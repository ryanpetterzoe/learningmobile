<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - <?= e(APP_NAME) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            background: #f0f4ff;
        }

        .auth-brand {
            flex: 0.8;
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
            width: 70px;
            height: 70px;
            background: rgba(255,255,255,0.15);
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
        }

        .brand-logo i { font-size: 32px; color: #fff; }
        .brand-content h1 { font-size: 32px; font-weight: 800; margin-bottom: 8px; }
        .brand-content h1 span { font-weight: 400; }
        .brand-content p { font-size: 15px; opacity: 0.85; max-width: 280px; line-height: 1.6; }

        .auth-form-panel {
            flex: 1.2;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
            overflow-y: auto;
        }

        .auth-form-container {
            width: 100%;
            max-width: 480px;
        }

        .close-btn {
            position: absolute;
            top: 25px;
            right: 25px;
            width: 36px;
            height: 36px;
            background: #f1f5f9;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #64748b;
            text-decoration: none;
            transition: all 0.3s;
        }

        .auth-form-header { margin-bottom: 30px; }
        .auth-form-header h2 { font-size: 26px; font-weight: 700; color: #1e293b; margin-bottom: 6px; }
        .auth-form-header p { color: #64748b; font-size: 14px; }

        .form-group {
            margin-bottom: 18px;
            position: relative;
        }

        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 6px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i.icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 15px;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 13px 14px 13px 42px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 14px;
            font-family: inherit;
            transition: all 0.3s;
            outline: none;
            background: #fff;
        }

        .form-group select {
            padding-left: 42px;
            appearance: none;
            cursor: pointer;
        }

        .form-group input:focus,
        .form-group select:focus {
            border-color: #3B49DF;
            box-shadow: 0 0 0 4px rgba(59, 73, 223, 0.1);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .terms-text {
            font-size: 12px;
            color: #64748b;
            margin: 15px 0 20px;
            line-height: 1.5;
        }

        .terms-text a {
            color: #3B49DF;
            text-decoration: none;
        }

        .btn-register {
            width: 100%;
            padding: 14px;
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

        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(59, 73, 223, 0.4);
        }

        .divider {
            display: flex;
            align-items: center;
            margin: 20px 0;
            gap: 12px;
        }

        .divider::before, .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #e2e8f0;
        }

        .divider span { font-size: 12px; color: #94a3b8; }

        .btn-google {
            width: 100%;
            padding: 12px;
            background: #fff;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 500;
            color: #374151;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all 0.3s;
        }

        .btn-google:hover { border-color: #3B49DF; background: #f8fafc; }

        .auth-footer {
            text-align: center;
            margin-top: 25px;
            font-size: 14px;
            color: #64748b;
        }

        .auth-footer a { color: #3B49DF; font-weight: 600; text-decoration: none; }

        .alert-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #dc2626;
            padding: 12px 16px;
            border-radius: 12px;
            font-size: 13px;
            margin-bottom: 18px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #16a34a;
            padding: 12px 16px;
            border-radius: 12px;
            font-size: 13px;
            margin-bottom: 18px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        @media (max-width: 768px) {
            body { flex-direction: column; overflow: auto; }
            .auth-brand { min-height: 180px; padding: 35px 25px; flex: none; }
            .auth-form-panel { padding: 30px 20px; }
            .form-row { grid-template-columns: 1fr; }
            .brand-content h1 { font-size: 26px; }
        }
    </style>
</head>
<body>
    <!-- Left Brand Panel -->
    <div class="auth-brand">
        <div class="brand-content">
            <div class="brand-logo">
                <i class="fas fa-book-open"></i>
            </div>
            <h1>Sign <span>Up</span></h1>
            <p>Register to Start Your Exciting Learning Process</p>
        </div>
    </div>

    <!-- Right Form Panel -->
    <div class="auth-form-panel">
        <div class="auth-form-container">
            <div class="auth-form-header">
                <h2>Buat Akun Baru</h2>
                <p>Daftar untuk memulai perjalanan belajar Anda</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert-error"><i class="fas fa-exclamation-circle"></i> <?= e($error) ?></div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="alert-success"><i class="fas fa-check-circle"></i> <?= e($success) ?></div>
            <?php endif; ?>

            <form method="POST" action="<?= url('register') ?>">
                <?= csrf_field() ?>

                <div class="form-group">
                    <label>Full Name</label>
                    <div class="input-wrapper">
                        <i class="fas fa-user icon"></i>
                        <input type="text" name="full_name" placeholder="Nama lengkap Anda" value="<?= e($_POST['full_name'] ?? '') ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Email address</label>
                    <div class="input-wrapper">
                        <i class="fas fa-envelope icon"></i>
                        <input type="email" name="email" placeholder="email@example.com" value="<?= e($_POST['email'] ?? '') ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>NIS/NIP (opsional)</label>
                        <div class="input-wrapper">
                            <i class="fas fa-id-card icon"></i>
                            <input type="text" name="nis" placeholder="Nomor Induk" value="<?= e($_POST['nis'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Mobile Number</label>
                        <div class="input-wrapper">
                            <i class="fas fa-phone icon"></i>
                            <input type="text" name="phone" placeholder="08xxxxxxxxxx" value="<?= e($_POST['phone'] ?? '') ?>">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Daftar sebagai</label>
                    <div class="input-wrapper">
                        <i class="fas fa-users icon"></i>
                        <select name="role">
                            <option value="siswa" <?= ($_POST['role'] ?? '') === 'siswa' ? 'selected' : '' ?>>Siswa</option>
                            <option value="guru" <?= ($_POST['role'] ?? '') === 'guru' ? 'selected' : '' ?>>Guru</option>
                            <option value="orang_tua" <?= ($_POST['role'] ?? '') === 'orang_tua' ? 'selected' : '' ?>>Orang Tua</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Create a Password</label>
                        <div class="input-wrapper">
                            <i class="fas fa-lock icon"></i>
                            <input type="password" name="password" placeholder="Min 6 karakter" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Confirm Password</label>
                        <div class="input-wrapper">
                            <i class="fas fa-lock icon"></i>
                            <input type="password" name="password_confirm" placeholder="Ulangi password" required>
                        </div>
                    </div>
                </div>

                <p class="terms-text">
                    By Signing up you must agree our <a href="#">Terms of Services</a> & 
                    <a href="#">Privacy Policies</a>
                </p>

                <button type="submit" class="btn-register">
                    Get Set to Explore <i class="fas fa-arrow-right"></i>
                </button>
            </form>

            <div class="divider"><span>atau</span></div>

            <button class="btn-google">
                <svg width="20" height="20" viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
                Sign in with Google
            </button>

            <div class="auth-footer">
                Already a Member? <a href="<?= url('login') ?>">Login Now</a>
            </div>
        </div>
    </div>
</body>
</html>
