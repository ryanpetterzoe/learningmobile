<?php
/**
 * SimpleEdu LMS - Installer
 * WordPress-style installation wizard
 */
session_start();

define('BASE_PATH', dirname(__DIR__));

// Auto-detect base URL
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptDir = rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])), '/\\');
define('BASE_URL', $protocol . '://' . $host . $scriptDir);
define('INSTALL_URL', $protocol . '://' . $host . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\'));

// Already installed?
if (file_exists(BASE_PATH . '/config/config.php')) {
    header('Location: ' . BASE_URL . '/');
    exit;
}

$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;
$error = '';
$success = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch ($step) {
        case 2: // Database config
            $dbHost = trim($_POST['db_host'] ?? 'localhost');
            $dbPort = trim($_POST['db_port'] ?? '3306');
            $dbName = trim($_POST['db_name'] ?? '');
            $dbUser = trim($_POST['db_user'] ?? '');
            $dbPass = $_POST['db_pass'] ?? '';
            $dbPrefix = trim($_POST['db_prefix'] ?? 'se_');

            // Test connection
            try {
                $pdo = new PDO(
                    "mysql:host={$dbHost};port={$dbPort}",
                    $dbUser,
                    $dbPass,
                    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                );
                
                // Create database if not exists
                $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                
                $_SESSION['install'] = [
                    'db_host' => $dbHost,
                    'db_port' => $dbPort,
                    'db_name' => $dbName,
                    'db_user' => $dbUser,
                    'db_pass' => $dbPass,
                    'db_prefix' => $dbPrefix
                ];
                
                header('Location: ' . INSTALL_URL . '/index.php?step=3');
                exit;
            } catch (PDOException $e) {
                $error = 'Koneksi database gagal: ' . $e->getMessage();
            }
            break;

        case 3: // App settings
            $appName = trim($_POST['app_name'] ?? 'SimpleEdu');
            $appDesc = trim($_POST['app_desc'] ?? '');
            $schoolName = trim($_POST['school_name'] ?? '');
            
            // Handle logo upload
            $logoPath = '';
            if (isset($_FILES['app_logo']) && $_FILES['app_logo']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = BASE_PATH . '/uploads/system/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $ext = strtolower(pathinfo($_FILES['app_logo']['name'], PATHINFO_EXTENSION));
                $allowed = ['png', 'jpg', 'jpeg', 'svg', 'webp'];
                if (in_array($ext, $allowed)) {
                    $logoFile = 'logo_' . time() . '.' . $ext;
                    move_uploaded_file($_FILES['app_logo']['tmp_name'], $uploadDir . $logoFile);
                    $logoPath = 'uploads/system/' . $logoFile;
                }
            }

            $_SESSION['install']['app_name'] = $appName;
            $_SESSION['install']['app_desc'] = $appDesc;
            $_SESSION['install']['school_name'] = $schoolName;
            $_SESSION['install']['app_logo'] = $logoPath;

            header('Location: ' . INSTALL_URL . '/index.php?step=4');
            exit;
            break;

        case 4: // Admin account
            $adminName = trim($_POST['admin_name'] ?? '');
            $adminEmail = trim($_POST['admin_email'] ?? '');
            $adminPass = $_POST['admin_pass'] ?? '';
            $adminPassConfirm = $_POST['admin_pass_confirm'] ?? '';

            if (empty($adminName) || empty($adminEmail) || empty($adminPass)) {
                $error = 'Semua field harus diisi!';
            } elseif ($adminPass !== $adminPassConfirm) {
                $error = 'Konfirmasi password tidak cocok!';
            } elseif (strlen($adminPass) < 6) {
                $error = 'Password minimal 6 karakter!';
            } else {
                $_SESSION['install']['admin_name'] = $adminName;
                $_SESSION['install']['admin_email'] = $adminEmail;
                $_SESSION['install']['admin_pass'] = $adminPass;

                // Run installation
                $result = runInstallation($_SESSION['install']);
                if ($result === true) {
                    header('Location: ' . INSTALL_URL . '/index.php?step=5');
                    exit;
                } else {
                    $error = $result;
                }
            }
            break;
    }
}

function runInstallation($config) {
    try {
        $pdo = new PDO(
            "mysql:host={$config['db_host']};port={$config['db_port']};dbname={$config['db_name']}",
            $config['db_user'],
            $config['db_pass'],
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        $prefix = $config['db_prefix'];

        // Read and execute SQL schema (statement by statement)
        $sql = file_get_contents(BASE_PATH . '/install/schema.sql');
        $sql = str_replace('{PREFIX}', $prefix, $sql);
        
        // Split into individual statements and execute one by one
        // This ensures compatibility with all MySQL/PDO configurations
        $statements = array_filter(array_map('trim', explode(';', $sql)));
        foreach ($statements as $statement) {
            if (!empty($statement) && $statement !== '--') {
                try {
                    $pdo->exec($statement);
                } catch (PDOException $e) {
                    // Skip duplicate key errors on INSERT (re-install scenario)
                    if (strpos($e->getMessage(), 'Duplicate entry') === false && 
                        strpos($e->getMessage(), '1062') === false) {
                        // Log but don't fail on non-critical errors
                        error_log("Install SQL warning: " . $e->getMessage());
                    }
                }
            }
        }

        // Also run migration files to ensure all tables/columns exist
        $migrationFiles = [
            BASE_PATH . '/install/migrate_v2.sql',
            BASE_PATH . '/install/migrate_v3_nested_replies.sql',
            BASE_PATH . '/install/migrate_v4_biodata.sql',
            BASE_PATH . '/install/migrate_v5_missing_tables.sql',
        ];
        foreach ($migrationFiles as $migFile) {
            if (file_exists($migFile)) {
                $migSql = file_get_contents($migFile);
                $migSql = str_replace('{PREFIX}', $prefix, $migSql);
                $migStatements = array_filter(array_map('trim', explode(';', $migSql)));
                foreach ($migStatements as $stmt) {
                    if (!empty($stmt) && $stmt !== '--') {
                        try { $pdo->exec($stmt); } catch (PDOException $e) { /* skip */ }
                    }
                }
            }
        }

        // Insert admin user
        $hashedPass = password_hash($config['admin_pass'], PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO {$prefix}users (full_name, email, password, role, status, created_at) VALUES (?, ?, ?, 'admin', 'active', NOW())");
        $stmt->execute([$config['admin_name'], $config['admin_email'], $hashedPass]);

        // Insert app settings
        $settings = [
            'app_name' => $config['app_name'],
            'app_desc' => $config['app_desc'],
            'school_name' => $config['school_name'],
            'app_logo' => $config['app_logo'],
            'theme' => 'light',
            'primary_color' => '#3B49DF',
            'installed_at' => date('Y-m-d H:i:s'),
            'version' => '1.0.0'
        ];

        $stmt = $pdo->prepare("INSERT INTO {$prefix}settings (setting_key, setting_value) VALUES (?, ?)");
        foreach ($settings as $key => $value) {
            $stmt->execute([$key, $value]);
        }

        // Generate config file
        $configContent = "<?php\n";
        $configContent .= "/**\n * SimpleEdu LMS - Configuration\n * Generated by installer on " . date('Y-m-d H:i:s') . "\n */\n\n";
        $configContent .= "// Database\n";
        $configContent .= "define('DB_HOST', " . var_export($config['db_host'], true) . ");\n";
        $configContent .= "define('DB_PORT', " . var_export($config['db_port'], true) . ");\n";
        $configContent .= "define('DB_NAME', " . var_export($config['db_name'], true) . ");\n";
        $configContent .= "define('DB_USER', " . var_export($config['db_user'], true) . ");\n";
        $configContent .= "define('DB_PASS', " . var_export($config['db_pass'], true) . ");\n";
        $configContent .= "define('DB_PREFIX', " . var_export($config['db_prefix'], true) . ");\n\n";
        $configContent .= "// App\n";
        $configContent .= "define('APP_NAME', " . var_export($config['app_name'], true) . ");\n";
        $configContent .= "define('APP_VERSION', '1.0.0');\n\n";
        $configContent .= "// Security\n";
        $configContent .= "define('SECRET_KEY', " . var_export(bin2hex(random_bytes(32)), true) . ");\n\n";
        $configContent .= "// Paths (auto-detected)\n";
        $configContent .= "if (!defined('BASE_PATH')) define('BASE_PATH', dirname(__DIR__));\n";
        $configContent .= "if (!defined('BASE_URL')) {\n";
        $configContent .= "    \$protocol = (!empty(\$_SERVER['HTTPS']) && \$_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';\n";
        $configContent .= "    \$host = \$_SERVER['HTTP_HOST'] ?? 'localhost';\n";
        $configContent .= "    \$scriptDir = rtrim(dirname(\$_SERVER['SCRIPT_NAME']), '/\\\\');\n";
        $configContent .= "    define('BASE_URL', \$protocol . '://' . \$host . \$scriptDir);\n";
        $configContent .= "}\n\n";
        $configContent .= "// Upload limits\n";
        $configContent .= "define('MAX_UPLOAD_SIZE', 50 * 1024 * 1024); // 50MB\n";
        $configContent .= "define('ALLOWED_FILE_TYPES', 'pdf,doc,docx,ppt,pptx,xls,xlsx,jpg,jpeg,png,gif,webp,mp4,mp3,zip,rar');\n";

        $configDir = BASE_PATH . '/config';
        if (!is_dir($configDir)) {
            mkdir($configDir, 0755, true);
        }
        file_put_contents($configDir . '/config.php', $configContent);

        // Create upload directories
        $dirs = ['uploads/avatars', 'uploads/assignments', 'uploads/materials', 'uploads/system', 'uploads/forum', 'uploads/portfolio'];
        foreach ($dirs as $dir) {
            $fullDir = BASE_PATH . '/' . $dir;
            if (!is_dir($fullDir)) {
                mkdir($fullDir, 0755, true);
            }
        }

        return true;
    } catch (Exception $e) {
        return 'Error instalasi: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SimpleEdu - Installer</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #1e3a5f 0%, #3B49DF 50%, #6366f1 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .installer-container {
            background: #fff;
            border-radius: 24px;
            box-shadow: 0 25px 60px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 600px;
            overflow: hidden;
        }

        .installer-header {
            background: linear-gradient(135deg, #3B49DF, #6366f1);
            padding: 40px 30px;
            text-align: center;
            color: #fff;
        }

        .installer-header h1 {
            font-size: 28px;
            font-weight: 800;
            margin-bottom: 8px;
        }

        .installer-header p {
            opacity: 0.9;
            font-size: 14px;
        }

        .installer-header .logo-icon {
            width: 60px;
            height: 60px;
            background: rgba(255,255,255,0.2);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 28px;
        }

        .steps-indicator {
            display: flex;
            justify-content: center;
            gap: 8px;
            padding: 20px;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
        }

        .step-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #e2e8f0;
            transition: all 0.3s;
        }

        .step-dot.active {
            background: #3B49DF;
            transform: scale(1.2);
        }

        .step-dot.done {
            background: #10b981;
        }

        .installer-body {
            padding: 40px 30px;
        }

        .step-title {
            font-size: 20px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 8px;
        }

        .step-desc {
            color: #64748b;
            font-size: 14px;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 6px;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 14px;
            font-family: inherit;
            transition: all 0.3s;
            outline: none;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            border-color: #3B49DF;
            box-shadow: 0 0 0 4px rgba(59, 73, 223, 0.1);
        }

        .form-group input[type="file"] {
            padding: 10px;
            background: #f8fafc;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .btn-primary {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #3B49DF, #6366f1);
            color: #fff;
            border: none;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 10px;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(59, 73, 223, 0.4);
        }

        .alert-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #dc2626;
            padding: 12px 16px;
            border-radius: 10px;
            font-size: 13px;
            margin-bottom: 20px;
        }

        .alert-success {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #16a34a;
            padding: 12px 16px;
            border-radius: 10px;
            font-size: 13px;
            margin-bottom: 20px;
        }

        .success-container {
            text-align: center;
            padding: 20px 0;
        }

        .success-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #10b981, #059669);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 36px;
            color: #fff;
        }

        .requirements-list {
            list-style: none;
            margin-bottom: 20px;
        }

        .requirements-list li {
            padding: 10px 15px;
            border-radius: 8px;
            margin-bottom: 6px;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .requirements-list li.pass {
            background: #f0fdf4;
            color: #16a34a;
        }

        .requirements-list li.fail {
            background: #fef2f2;
            color: #dc2626;
        }

        @media (max-width: 480px) {
            .form-row { grid-template-columns: 1fr; }
            .installer-body { padding: 25px 20px; }
            .installer-header { padding: 30px 20px; }
        }
    </style>
</head>
<body>
    <div class="installer-container">
        <div class="installer-header">
            <div class="logo-icon">📚</div>
            <h1>Simple<span style="font-weight:400">Edu</span></h1>
            <p>Instalasi Learning Management System</p>
        </div>

        <div class="steps-indicator">
            <?php for ($i = 1; $i <= 5; $i++): ?>
                <div class="step-dot <?= $i < $step ? 'done' : ($i === $step ? 'active' : '') ?>"></div>
            <?php endfor; ?>
        </div>

        <div class="installer-body">
            <?php if ($error): ?>
                <div class="alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if ($step === 1): ?>
                <!-- Step 1: Requirements Check -->
                <h2 class="step-title">Cek Persyaratan Sistem</h2>
                <p class="step-desc">Memastikan server Anda memenuhi persyaratan minimum.</p>

                <?php
                $checks = [
                    ['PHP Version >= 7.4', version_compare(PHP_VERSION, '7.4.0', '>=')],
                    ['PDO MySQL Extension', extension_loaded('pdo_mysql')],
                    ['GD Library', extension_loaded('gd')],
                    ['Mbstring Extension', extension_loaded('mbstring')],
                    ['JSON Extension', extension_loaded('json')],
                    ['Config writable', is_writable(BASE_PATH . '/config') || is_writable(BASE_PATH)],
                    ['Uploads writable', is_writable(BASE_PATH) || (is_dir(BASE_PATH . '/uploads') && is_writable(BASE_PATH . '/uploads'))],
                ];
                $allPass = true;
                foreach ($checks as $check) {
                    if (!$check[1]) $allPass = false;
                }
                ?>

                <ul class="requirements-list">
                    <?php foreach ($checks as $check): ?>
                        <li class="<?= $check[1] ? 'pass' : 'fail' ?>">
                            <span><?= $check[1] ? '✓' : '✗' ?></span>
                            <?= $check[0] ?>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <?php if ($allPass): ?>
                    <a href="?step=2"><button class="btn-primary">Lanjutkan →</button></a>
                <?php else: ?>
                    <div class="alert-error">Beberapa persyaratan belum terpenuhi. Silakan perbaiki terlebih dahulu.</div>
                    <a href="?step=2"><button class="btn-primary">Lanjutkan Anyway →</button></a>
                <?php endif; ?>

            <?php elseif ($step === 2): ?>
                <!-- Step 2: Database Configuration -->
                <h2 class="step-title">Konfigurasi Database</h2>
                <p class="step-desc">Masukkan informasi koneksi database MySQL Anda.</p>

                <form method="POST" action="?step=2">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Host Database</label>
                            <input type="text" name="db_host" value="localhost" required>
                        </div>
                        <div class="form-group">
                            <label>Port</label>
                            <input type="text" name="db_port" value="3306" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Nama Database</label>
                        <input type="text" name="db_name" placeholder="simpleedu_db" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Username</label>
                            <input type="text" name="db_user" value="root" required>
                        </div>
                        <div class="form-group">
                            <label>Password</label>
                            <input type="password" name="db_pass" placeholder="(kosongkan jika tidak ada)">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Table Prefix</label>
                        <input type="text" name="db_prefix" value="se_" required>
                    </div>
                    <button type="submit" class="btn-primary">Test Koneksi & Lanjutkan →</button>
                </form>

            <?php elseif ($step === 3): ?>
                <!-- Step 3: App Settings -->
                <h2 class="step-title">Pengaturan Aplikasi</h2>
                <p class="step-desc">Konfigurasi nama dan identitas LMS Anda.</p>

                <form method="POST" action="?step=3" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Nama Aplikasi</label>
                        <input type="text" name="app_name" value="SimpleEdu" required>
                    </div>
                    <div class="form-group">
                        <label>Nama Sekolah</label>
                        <input type="text" name="school_name" placeholder="SMK Negeri 1 ..." required>
                    </div>
                    <div class="form-group">
                        <label>Deskripsi Singkat</label>
                        <textarea name="app_desc" rows="3" placeholder="Platform pembelajaran digital untuk siswa SMK..."></textarea>
                    </div>
                    <div class="form-group">
                        <label>Logo Aplikasi (opsional)</label>
                        <input type="file" name="app_logo" accept="image/*">
                    </div>
                    <button type="submit" class="btn-primary">Lanjutkan →</button>
                </form>

            <?php elseif ($step === 4): ?>
                <!-- Step 4: Admin Account -->
                <h2 class="step-title">Akun Administrator</h2>
                <p class="step-desc">Buat akun admin utama untuk mengelola sistem.</p>

                <form method="POST" action="?step=4">
                    <div class="form-group">
                        <label>Nama Lengkap</label>
                        <input type="text" name="admin_name" placeholder="Administrator" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="admin_email" placeholder="admin@sekolah.sch.id" required>
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="admin_pass" placeholder="Minimal 6 karakter" required>
                    </div>
                    <div class="form-group">
                        <label>Konfirmasi Password</label>
                        <input type="password" name="admin_pass_confirm" required>
                    </div>
                    <button type="submit" class="btn-primary">Install Sekarang →</button>
                </form>

            <?php elseif ($step === 5): ?>
                <!-- Step 5: Success -->
                <div class="success-container">
                    <div class="success-icon">✓</div>
                    <h2 class="step-title">Instalasi Berhasil!</h2>
                    <p class="step-desc">SimpleEdu LMS telah berhasil diinstal. Anda dapat login dengan akun admin yang telah dibuat.</p>
                    <a href="<?= BASE_URL ?>/">
                        <button class="btn-primary">Masuk ke SimpleEdu →</button>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
