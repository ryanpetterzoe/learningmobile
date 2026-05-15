<?php
// Get app settings for branding
$db = Database::getInstance();
$appLogo = $db->getSetting('app_logo');
$appName = $db->getSetting('app_name') ?: 'SimpleEdu';
$appSlogan = $db->getSetting('app_slogan') ?: 'Experience a better learning environment';
$schoolName = $db->getSetting('school_name') ?: '';
$appCopyright = $db->getSetting('app_copyright') ?: '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?= e($appName) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        /* ===== SPLASH SCREEN ===== */
        .splash-screen {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: linear-gradient(160deg, #1a237e 0%, #3B49DF 40%, #6366f1 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            transition: opacity 0.6s ease, visibility 0.6s ease;
        }
        .splash-screen.hidden {
            opacity: 0;
            visibility: hidden;
        }
        .splash-logo {
            width: 100px;
            height: 100px;
            border-radius: 24px;
            background: rgba(255,255,255,0.15);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 24px;
            animation: splashPulse 1.5s infinite ease-in-out;
        }
        .splash-logo img {
            width: 70px;
            height: 70px;
            object-fit: contain;
            border-radius: 12px;
        }
        .splash-logo i {
            font-size: 44px;
            color: #fff;
        }
        .splash-title {
            font-family: 'Inter', sans-serif;
            font-size: 32px;
            font-weight: 800;
            color: #fff;
            margin-bottom: 8px;
        }
        .splash-subtitle {
            font-family: 'Inter', sans-serif;
            font-size: 14px;
            color: rgba(255,255,255,0.8);
            margin-bottom: 30px;
        }
        .splash-loader {
            display: flex;
            gap: 6px;
        }
        .splash-loader span {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: rgba(255,255,255,0.6);
            animation: splashBounce 1.2s infinite ease-in-out;
        }
        .splash-loader span:nth-child(2) { animation-delay: 0.2s; }
        .splash-loader span:nth-child(3) { animation-delay: 0.4s; }

        @keyframes splashPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        @keyframes splashBounce {
            0%, 80%, 100% { transform: translateY(0); opacity: 0.6; }
            40% { transform: translateY(-12px); opacity: 1; }
        }

        /* ===== MAIN LOGIN PAGE ===== */
        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            background: #f0f4ff;
            overflow: hidden;
        }

        /* Left Panel - Branding */
        .auth-brand {
            flex: 1;
            background: linear-gradient(160deg, #1a237e 0%, #3B49DF 40%, #6366f1 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 60px;
            position: relative;
            overflow: hidden;
        }

        .auth-brand::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(ellipse, rgba(255,255,255,0.08) 0%, transparent 70%);
            animation: float 15s infinite ease-in-out;
        }

        .auth-brand::after {
            content: '';
            position: absolute;
            bottom: -20%;
            right: -20%;
            width: 60%;
            height: 60%;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.4) 0%, transparent 70%);
            animation: float 10s infinite ease-in-out reverse;
        }

        @keyframes float {
            0%, 100% { transform: translate(0, 0); }
            50% { transform: translate(30px, -30px); }
        }

        .brand-content {
            position: relative;
            z-index: 2;
            text-align: center;
            color: #fff;
        }

        .brand-logo {
            width: 80px;
            height: 80px;
            background: rgba(255,255,255,0.15);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
        }

        .brand-logo i {
            font-size: 36px;
            color: #fff;
        }

        .brand-logo img {
            width: 56px;
            height: 56px;
            object-fit: contain;
            border-radius: 10px;
        }

        .brand-content h1 {
            font-size: 36px;
            font-weight: 800;
            margin-bottom: 10px;
        }

        .brand-content h1 span {
            font-weight: 400;
        }

        .brand-content p {
            font-size: 16px;
            opacity: 0.85;
            max-width: 300px;
            line-height: 1.6;
        }

        .brand-school-name {
            font-size: 13px;
            opacity: 0.7;
            margin-top: 12px;
        }

        .brand-illustration {
            margin-top: 40px;
            opacity: 0.3;
            font-size: 120px;
        }

        /* Right Panel - Form */
        .auth-form-panel {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
            position: relative;
        }

        .auth-form-container {
            width: 100%;
            max-width: 420px;
        }

        .auth-form-header {
            margin-bottom: 35px;
        }

        .auth-form-header h2 {
            font-size: 28px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 8px;
        }

        .auth-form-header p {
            color: #64748b;
            font-size: 15px;
        }

        .form-group {
            margin-bottom: 20px;
            position: relative;
        }

        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 16px;
            transition: color 0.3s;
        }

        .form-group input {
            width: 100%;
            padding: 14px 16px 14px 46px;
            border: 2px solid #e2e8f0;
            border-radius: 14px;
            font-size: 14px;
            font-family: inherit;
            transition: all 0.3s;
            outline: none;
            background: #fff;
        }

        .form-group input:focus {
            border-color: #3B49DF;
            box-shadow: 0 0 0 4px rgba(59, 73, 223, 0.1);
        }

        .form-group input:focus + i,
        .form-group input:focus ~ i {
            color: #3B49DF;
        }

        .input-wrapper .toggle-pass {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            left: auto;
            cursor: pointer;
            color: #94a3b8;
        }

        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            font-size: 13px;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #64748b;
            cursor: pointer;
        }

        .remember-me input {
            accent-color: #3B49DF;
            width: 16px;
            height: 16px;
        }

        .forgot-link {
            color: #3B49DF;
            text-decoration: none;
            font-weight: 500;
        }

        .forgot-link:hover {
            text-decoration: underline;
        }

        .btn-login {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #3B49DF, #6366f1);
            color: #fff;
            border: none;
            border-radius: 14px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(59, 73, 223, 0.4);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .divider {
            display: flex;
            align-items: center;
            margin: 25px 0;
            gap: 15px;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #e2e8f0;
        }

        .divider span {
            font-size: 12px;
            color: #94a3b8;
            font-weight: 500;
        }

        .auth-footer {
            text-align: center;
            margin-top: 30px;
            font-size: 14px;
            color: #64748b;
        }

        .auth-footer a {
            color: #3B49DF;
            font-weight: 600;
            text-decoration: none;
        }

        .auth-footer a:hover {
            text-decoration: underline;
        }

        .auth-copyright {
            text-align: center;
            margin-top: 20px;
            font-size: 11px;
            color: #94a3b8;
        }

        .alert-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #dc2626;
            padding: 12px 16px;
            border-radius: 12px;
            font-size: 13px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-error i {
            font-size: 16px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            body { flex-direction: column; overflow: auto; }
            
            .auth-brand {
                min-height: 200px;
                padding: 40px 30px;
                flex: none;
            }

            .brand-content h1 { font-size: 28px; }
            .brand-content p { font-size: 14px; }
            .brand-illustration { display: none; }

            .auth-form-panel {
                padding: 30px 20px;
                flex: 1;
            }

            .auth-form-header h2 { font-size: 22px; }
        }

        @media (max-width: 480px) {
            .auth-brand { min-height: 160px; padding: 30px 20px; }
            .brand-logo { width: 60px; height: 60px; }
            .brand-logo i { font-size: 28px; }
            .brand-content h1 { font-size: 24px; }
            .form-options { flex-direction: column; gap: 10px; align-items: flex-start; }
        }
    </style>
</head>
<body>
    <!-- Splash Screen -->
    <div class="splash-screen" id="splashScreen">
        <div class="splash-logo">
            <?php if ($appLogo): ?>
                <img src="<?= upload_url($appLogo) ?>" alt="Logo">
            <?php else: ?>
                <i class="fas fa-book-open"></i>
            <?php endif; ?>
        </div>
        <div class="splash-title"><?= e($appName) ?></div>
        <?php if ($appSlogan): ?>
            <div class="splash-subtitle"><?= e($appSlogan) ?></div>
        <?php endif; ?>
        <div class="splash-loader">
            <span></span><span></span><span></span>
        </div>
    </div>

    <!-- Left Brand Panel -->
    <div class="auth-brand">
        <div class="brand-content">
            <div class="brand-logo">
                <?php if ($appLogo): ?>
                    <img src="<?= upload_url($appLogo) ?>" alt="Logo">
                <?php else: ?>
                    <i class="fas fa-book-open"></i>
                <?php endif; ?>
            </div>
            <h1><?= e($appName) ?></h1>
            <p><?= e($appSlogan) ?></p>
            <?php if ($schoolName): ?>
                <div class="brand-school-name"><?= e($schoolName) ?></div>
            <?php endif; ?>
            <div class="brand-illustration">
                <i class="fas fa-graduation-cap"></i>
            </div>
        </div>
    </div>

    <!-- Right Form Panel -->
    <div class="auth-form-panel">
        <div class="auth-form-container">
            <div class="auth-form-header">
                <h2>Log In</h2>
                <p>Masuk ke akun Anda untuk melanjutkan belajar</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= e($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?= url('login') ?>">
                <?= csrf_field() ?>

                <div class="form-group">
                    <label>Email address</label>
                    <div class="input-wrapper">
                        <input type="email" name="email" placeholder="name@example.com" value="<?= e($_POST['email'] ?? '') ?>" required>
                        <i class="fas fa-envelope"></i>
                    </div>
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <div class="input-wrapper">
                        <input type="password" name="password" id="password" placeholder="********" required>
                        <i class="fas fa-lock"></i>
                        <i class="fas fa-eye toggle-pass" onclick="togglePassword()"></i>
                    </div>
                </div>

                <div class="form-options">
                    <label class="remember-me">
                        <input type="checkbox" name="remember">
                        Remember me
                    </label>
                    <a href="<?= url('forgot-password') ?>" class="forgot-link">Forgot Password?</a>
                </div>

                <button type="submit" class="btn-login">
                    Login Now <i class="fas fa-arrow-right"></i>
                </button>
            </form>

            <div class="auth-footer">
                New Here? <a href="<?= url('register') ?>">Sign Up</a>
            </div>

            <?php if ($appCopyright): ?>
                <div class="auth-copyright"><?= e($appCopyright) ?></div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            const icon = document.querySelector('.toggle-pass');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }

        // Splash screen - hide after 2 seconds
        window.addEventListener('load', function() {
            setTimeout(function() {
                document.getElementById('splashScreen').classList.add('hidden');
            }, 2000);
        });
    </script>
</body>
</html>
