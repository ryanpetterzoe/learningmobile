<?php
/**
 * SimpleEdu - Login Controller
 */

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $error = 'Token keamanan tidak valid. Silakan coba lagi.';
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']);

        if (empty($email) || empty($password)) {
            $error = 'Email dan password harus diisi!';
        } else {
            $result = Auth::attempt($email, $password);
            if ($result === true) {
                Router::redirect('dashboard');
            } elseif ($result === 'pending') {
                $error = 'Akun Anda belum diaktivasi. Hubungi admin sekolah.';
            } elseif ($result === 'suspended') {
                $error = 'Akun Anda telah dinonaktifkan.';
            } else {
                $error = 'Email atau password salah!';
            }
        }
    }
}

render('auth/login', ['error' => $error]);
