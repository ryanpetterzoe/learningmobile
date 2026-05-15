<?php
/**
 * SimpleEdu - Forgot Password Controller
 * Allows users to reset their password via email or admin-assisted reset
 */

$db = Database::getInstance();
$prefix = $db->getPrefix();
$action = $GLOBALS['action'] ?? 'index';
$param = $GLOBALS['param'] ?? null;
$error = '';
$success = '';
$step = 'request'; // request, verify, reset, done

switch ($action) {
    case 'reset':
        // Step 3: Reset password with valid token
        $token = $param ?? ($_GET['token'] ?? '');
        if (empty($token)) {
            Router::redirect('forgot-password');
        }

        // Validate token
        $resetRecord = $db->fetch(
            "SELECT * FROM {$prefix}password_resets WHERE token = ? AND expires_at > NOW() AND used = 0",
            [$token]
        );

        if (!$resetRecord) {
            $error = 'Link reset tidak valid atau sudah kedaluwarsa.';
            $step = 'request';
        } else {
            $step = 'reset';

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $newPassword = $_POST['new_password'] ?? '';
                $confirmPassword = $_POST['confirm_password'] ?? '';

                if (empty($newPassword) || strlen($newPassword) < 6) {
                    $error = 'Password baru minimal 6 karakter!';
                } elseif ($newPassword !== $confirmPassword) {
                    $error = 'Konfirmasi password tidak cocok!';
                } else {
                    // Update password
                    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                    $db->update('users', ['password' => $hashedPassword], 'id = ?', [$resetRecord['user_id']]);

                    // Mark token as used
                    $db->update('password_resets', ['used' => 1], 'id = ?', [$resetRecord['id']]);

                    $step = 'done';
                    $success = 'Password berhasil direset! Silakan login dengan password baru.';
                }
            }
        }
        break;

    default:
        // Step 1: Request reset
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verify_csrf()) {
                $error = 'Token keamanan tidak valid.';
            } else {
                $email = trim($_POST['email'] ?? '');

                if (empty($email)) {
                    $error = 'Masukkan email Anda!';
                } else {
                    $user = $db->fetch("SELECT id, full_name, email FROM {$prefix}users WHERE email = ?", [$email]);

                    if ($user) {
                        // Generate reset token
                        $token = bin2hex(random_bytes(32));
                        $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

                        // Create password_resets table if not exists
                        $db->query("CREATE TABLE IF NOT EXISTS {$prefix}password_resets (
                            id INT AUTO_INCREMENT PRIMARY KEY,
                            user_id INT NOT NULL,
                            token VARCHAR(64) NOT NULL,
                            expires_at DATETIME NOT NULL,
                            used TINYINT(1) DEFAULT 0,
                            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

                        // Delete old tokens for this user
                        $db->delete('password_resets', 'user_id = ?', [$user['id']]);

                        // Insert new token
                        $db->insert('password_resets', [
                            'user_id' => $user['id'],
                            'token' => $token,
                            'expires_at' => $expiresAt,
                            'created_at' => date('Y-m-d H:i:s')
                        ]);

                        // Build reset link
                        $resetLink = BASE_URL . '/forgot-password/reset/' . $token;

                        // Try to send email (if mail function is available)
                        $emailSent = false;
                        if (function_exists('mail')) {
                            $subject = 'Reset Password - ' . APP_NAME;
                            $message = "Halo {$user['full_name']},\n\n";
                            $message .= "Anda menerima email ini karena ada permintaan reset password untuk akun Anda.\n\n";
                            $message .= "Klik link berikut untuk mereset password:\n";
                            $message .= $resetLink . "\n\n";
                            $message .= "Link ini berlaku selama 1 jam.\n\n";
                            $message .= "Jika Anda tidak meminta reset password, abaikan email ini.\n\n";
                            $message .= "Terima kasih,\n" . APP_NAME;

                            $headers = "From: noreply@" . ($_SERVER['HTTP_HOST'] ?? 'localhost') . "\r\n";
                            $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

                            $emailSent = @mail($user['email'], $subject, $message, $headers);
                        }

                        if ($emailSent) {
                            $success = 'Link reset password telah dikirim ke email Anda. Cek inbox atau folder spam.';
                        } else {
                            // If email fails, show the link directly (for local/dev environments)
                            $success = 'Link reset password telah dibuat. Jika tidak menerima email, hubungi admin atau gunakan link berikut:';
                            $step = 'link_generated';
                        }
                    } else {
                        // Don't reveal if email exists or not (security)
                        $success = 'Jika email terdaftar, link reset password akan dikirim. Periksa inbox Anda.';
                    }
                }
            }
        }
        break;
}

// Render the view
?>
<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Reset Password - <?= e(APP_NAME) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: linear-gradient(135deg, #1e3a5f 0%, #3B49DF 50%, #6366f1 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .reset-container {
            background: #fff;
            border-radius: 24px;
            box-shadow: 0 25px 60px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 420px;
            padding: 40px 30px;
            text-align: center;
        }
        .reset-icon {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, #3B49DF, #6366f1);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 28px;
            color: #fff;
        }
        .reset-container h2 {
            font-size: 22px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 8px;
        }
        .reset-container p.desc {
            font-size: 14px;
            color: #64748b;
            margin-bottom: 24px;
            line-height: 1.5;
        }
        .form-group {
            margin-bottom: 16px;
            text-align: left;
        }
        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 6px;
        }
        .form-group input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 14px;
            font-family: inherit;
            outline: none;
            transition: all 0.3s;
        }
        .form-group input:focus {
            border-color: #3B49DF;
            box-shadow: 0 0 0 4px rgba(59, 73, 223, 0.1);
        }
        .btn-submit {
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
            margin-top: 8px;
            font-family: inherit;
        }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(59, 73, 223, 0.4);
        }
        .alert {
            padding: 12px 16px;
            border-radius: 10px;
            font-size: 13px;
            margin-bottom: 16px;
            text-align: left;
        }
        .alert-error { background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; }
        .alert-success { background: #f0fdf4; border: 1px solid #bbf7d0; color: #16a34a; }
        .back-link {
            display: inline-block;
            margin-top: 20px;
            font-size: 13px;
            color: #3B49DF;
            text-decoration: none;
            font-weight: 500;
        }
        .back-link:hover { text-decoration: underline; }
        .reset-link-box {
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 12px;
            margin-top: 12px;
            word-break: break-all;
            font-size: 12px;
            color: #3B49DF;
            text-align: left;
        }
        .success-icon {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, #10b981, #059669);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 28px;
            color: #fff;
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <?php if ($step === 'done'): ?>
            <div class="success-icon"><i class="fas fa-check"></i></div>
            <h2>Password Berhasil Direset!</h2>
            <p class="desc">Password Anda telah diperbarui. Silakan login dengan password baru.</p>
            <a href="<?= url('login') ?>" class="btn-submit" style="display:block;text-align:center;text-decoration:none;line-height:1;">Login Sekarang</a>

        <?php elseif ($step === 'reset'): ?>
            <div class="reset-icon"><i class="fas fa-key"></i></div>
            <h2>Buat Password Baru</h2>
            <p class="desc">Masukkan password baru untuk akun Anda.</p>

            <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>

            <form method="POST">
                <?= csrf_field() ?>
                <div class="form-group">
                    <label>Password Baru</label>
                    <input type="password" name="new_password" placeholder="Minimal 6 karakter" required>
                </div>
                <div class="form-group">
                    <label>Konfirmasi Password</label>
                    <input type="password" name="confirm_password" placeholder="Ulangi password baru" required>
                </div>
                <button type="submit" class="btn-submit">Reset Password</button>
            </form>

        <?php else: ?>
            <div class="reset-icon"><i class="fas fa-lock"></i></div>
            <h2>Lupa Password?</h2>
            <p class="desc">Masukkan email yang terdaftar dan kami akan mengirimkan link untuk mereset password Anda.</p>

            <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
            <?php if ($success): ?><div class="alert alert-success"><?= e($success) ?></div><?php endif; ?>

            <?php if ($step === 'link_generated' && isset($resetLink)): ?>
                <div class="reset-link-box">
                    <a href="<?= e($resetLink) ?>"><?= e($resetLink) ?></a>
                </div>
            <?php endif; ?>

            <?php if (!$success): ?>
            <form method="POST">
                <?= csrf_field() ?>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" placeholder="contoh@email.com" required>
                </div>
                <button type="submit" class="btn-submit">Kirim Link Reset</button>
            </form>
            <?php endif; ?>

            <a href="<?= url('login') ?>" class="back-link"><i class="fas fa-arrow-left"></i> Kembali ke Login</a>
        <?php endif; ?>
    </div>
</body>
</html>
