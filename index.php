<?php
/**
 * SimpleEdu LMS - Main Entry Point
 * Detects installation status and routes accordingly
 */

// Define base path
define('BASE_PATH', __DIR__);

// Auto-detect base URL
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
define('BASE_URL', $protocol . '://' . $host . $scriptDir);

// Check if installed
if (!file_exists(BASE_PATH . '/config/config.php')) {
    // Redirect to installer
    header('Location: ' . BASE_URL . '/install/index.php');
    exit;
}

// Load configuration
require_once BASE_PATH . '/config/config.php';
require_once BASE_PATH . '/core/Database.php';
require_once BASE_PATH . '/core/Session.php';
require_once BASE_PATH . '/core/Router.php';
require_once BASE_PATH . '/core/Helper.php';
require_once BASE_PATH . '/core/Gamification.php';
require_once BASE_PATH . '/core/Auth.php';

// Start session
Session::start();

// Route the request
$router = new Router();
$router->dispatch();
