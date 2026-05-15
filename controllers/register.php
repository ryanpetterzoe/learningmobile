<?php
/**
 * SimpleEdu - Register Controller
 */

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $error = 'Token keamanan tidak valid.';
    } else {
        $data = [
            'full_name' => trim($_POST['full_name'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'password' => $_POST['password'] ?? '',
            'phone' => trim($_POST['phone'] ?? ''),
            'nis' => trim($_POST['nis'] ?? ''),
            'role' => $_POST['role'] ?? 'siswa',
        ];

        $confirmPass = $_POST['password_confirm'] ?? '';

        // Validate
        if (empty($data['full_name']) || empty($data['email']) || empty($data['password'])) {
            $error = 'Nama, email, dan password wajib diisi!';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $error = 'Format email tidak valid!';
        } elseif (strlen($data['password']) < 6) {
            $error = 'Password minimal 6 karakter!';
        } elseif ($data['password'] !== $confirmPass) {
            $error = 'Konfirmasi password tidak cocok!';
        } elseif (!in_array($data['role'], ['siswa', 'guru', 'orang_tua'])) {
            $error = 'Role tidak valid!';
        } else {
            $result = Auth::register($data);
            if ($result === true) {
                $success = 'Pendaftaran berhasil! Silakan tunggu persetujuan admin.';
            } else {
                $error = $result;
            }
        }
    }
}

render('auth/register', ['error' => $error, 'success' => $success]);
