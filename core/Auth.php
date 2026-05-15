<?php
/**
 * SimpleEdu - Authentication
 */
class Auth {
    public static function attempt($email, $password) {
        $db = Database::getInstance();
        $user = $db->fetch(
            "SELECT * FROM {$db->getPrefix()}users WHERE email = ?",
            [$email]
        );

        if (!$user) return false;
        if (!password_verify($password, $user['password'])) return false;
        if ($user['status'] === 'suspended') return 'suspended';
        if ($user['status'] === 'pending') return 'pending';

        // Login success
        self::loginUser($user);
        return true;
    }

    public static function loginUser($user) {
        Session::set('user_id', $user['id']);
        Session::set('user_role', $user['role']);
        Session::set('user_name', $user['full_name']);
        Session::set('user_avatar', $user['avatar']);

        // Update last login
        $db = Database::getInstance();
        $db->update('users', ['last_login' => date('Y-m-d H:i:s')], 'id = ?', [$user['id']]);

        // Award XP for login
        Gamification::awardXP($user['id'], 5, 'Login harian');
    }

    public static function logout() {
        Session::destroy();
    }

    public static function register($data) {
        $db = Database::getInstance();
        
        // Check if email exists
        $existing = $db->fetch(
            "SELECT id FROM {$db->getPrefix()}users WHERE email = ?",
            [$data['email']]
        );
        if ($existing) return 'Email sudah terdaftar!';

        // Hash password
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        $data['status'] = 'pending'; // Need admin approval
        $data['created_at'] = date('Y-m-d H:i:s');

        $userId = $db->insert('users', $data);
        return $userId > 0 ? true : 'Gagal mendaftar, coba lagi.';
    }

    public static function requireRole($roles) {
        if (!is_array($roles)) $roles = [$roles];
        if (!in_array(Session::userRole(), $roles)) {
            http_response_code(403);
            require_once BASE_PATH . '/views/errors/403.php';
            exit;
        }
    }

    public static function isAdmin() {
        return Session::userRole() === 'admin';
    }

    public static function isGuru() {
        return in_array(Session::userRole(), ['guru', 'wali_kelas']);
    }

    public static function isSiswa() {
        return Session::userRole() === 'siswa';
    }
}
