<?php
/**
 * SimpleEdu - Session Management
 */
class Session {
    public static function start() {
        if (session_status() === PHP_SESSION_NONE) {
            session_name('simpleedu_session');
            session_start();
        }
    }

    public static function set($key, $value) {
        $_SESSION[$key] = $value;
    }

    public static function get($key, $default = null) {
        return $_SESSION[$key] ?? $default;
    }

    public static function has($key) {
        return isset($_SESSION[$key]);
    }

    public static function remove($key) {
        unset($_SESSION[$key]);
    }

    public static function destroy() {
        session_destroy();
        $_SESSION = [];
    }

    public static function flash($key, $value = null) {
        if ($value !== null) {
            $_SESSION['_flash'][$key] = $value;
        } else {
            $val = $_SESSION['_flash'][$key] ?? null;
            unset($_SESSION['_flash'][$key]);
            return $val;
        }
    }

    public static function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    public static function userId() {
        return $_SESSION['user_id'] ?? null;
    }

    public static function userRole() {
        return $_SESSION['user_role'] ?? null;
    }

    public static function user() {
        if (!self::isLoggedIn()) return null;
        $db = Database::getInstance();
        $user = $db->fetch(
            "SELECT * FROM {$db->getPrefix()}users WHERE id = ?",
            [self::userId()]
        );
        // If user not found (e.g., after reinstall), destroy session
        if (!$user) {
            self::destroy();
            session_start();
            return null;
        }
        return $user;
    }
}
