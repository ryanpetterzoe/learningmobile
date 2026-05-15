<?php
/**
 * SimpleEdu - Helper Functions
 */

function asset($path) {
    return BASE_URL . '/assets/' . ltrim($path, '/');
}

function upload_url($path) {
    if (empty($path)) return asset('img/default-avatar.png');
    return BASE_URL . '/' . ltrim($path, '/');
}

function url($path = '') {
    return BASE_URL . '/' . ltrim($path, '/');
}

function e($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

function csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field() {
    return '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
}

function verify_csrf() {
    $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    return hash_equals($_SESSION['csrf_token'] ?? '', $token);
}

function time_ago($datetime) {
    $now = new DateTime();
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    if ($diff->y > 0) return $diff->y . ' tahun lalu';
    if ($diff->m > 0) return $diff->m . ' bulan lalu';
    if ($diff->d > 7) return floor($diff->d / 7) . ' minggu lalu';
    if ($diff->d > 0) return $diff->d . ' hari lalu';
    if ($diff->h > 0) return $diff->h . ' jam lalu';
    if ($diff->i > 0) return $diff->i . ' menit lalu';
    return 'Baru saja';
}

function format_date($date, $format = 'd M Y') {
    if (empty($date)) return '-';
    return date($format, strtotime($date));
}

function format_datetime($datetime) {
    if (empty($datetime)) return '-';
    return date('d M Y H:i', strtotime($datetime));
}

function file_size_format($bytes) {
    if ($bytes >= 1073741824) return number_format($bytes / 1073741824, 2) . ' GB';
    if ($bytes >= 1048576) return number_format($bytes / 1048576, 2) . ' MB';
    if ($bytes >= 1024) return number_format($bytes / 1024, 2) . ' KB';
    return $bytes . ' bytes';
}

function truncate($string, $length = 100) {
    if (strlen($string) <= $length) return $string;
    return substr($string, 0, $length) . '...';
}

function slugify($text) {
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim($text, '-');
}

function get_level_name($level) {
    $levels = [
        1 => 'Pemula',
        2 => 'Pelajar',
        3 => 'Rajin',
        4 => 'Tekun',
        5 => 'Mahir',
        6 => 'Ahli',
        7 => 'Master',
        8 => 'Legend',
        9 => 'Guru Besar',
        10 => 'Sang Juara'
    ];
    return $levels[$level] ?? 'Level ' . $level;
}

function xp_for_level($level) {
    return $level * 100;
}

function calculate_level($xp) {
    $level = 1;
    while ($xp >= xp_for_level($level + 1)) {
        $level++;
    }
    return min($level, 10);
}

function day_name($dayNum) {
    $days = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'];
    return $days[$dayNum] ?? '';
}

function upload_file($file, $directory, $allowedTypes = null) {
    if ($file['error'] !== UPLOAD_ERR_OK) return false;

    $uploadDir = BASE_PATH . '/uploads/' . trim($directory, '/') . '/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if ($allowedTypes && !in_array($ext, $allowedTypes)) return false;
    $maxSize = defined('MAX_UPLOAD_SIZE') ? MAX_UPLOAD_SIZE : 50 * 1024 * 1024;
    if ($file['size'] > $maxSize) return false;

    $filename = uniqid() . '_' . time() . '.' . $ext;
    $filepath = $uploadDir . $filename;

    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return 'uploads/' . trim($directory, '/') . '/' . $filename;
    }
    return false;
}

function json_response($data, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function app_setting($key, $default = '') {
    $db = Database::getInstance();
    $value = $db->getSetting($key);
    return $value ?? $default;
}

function render($view, $data = []) {
    extract($data);
    $viewFile = BASE_PATH . '/views/' . $view . '.php';
    if (file_exists($viewFile)) {
        require $viewFile;
    }
}

function render_with_layout($view, $data = [], $layout = 'main') {
    $data['_content_view'] = $view;
    extract($data);
    require_once BASE_PATH . '/views/layouts/' . $layout . '.php';
}
