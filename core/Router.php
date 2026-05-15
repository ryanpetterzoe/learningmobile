<?php
/**
 * SimpleEdu - Simple Router
 */
class Router {
    private $route;

    public function __construct() {
        $this->route = trim($_GET['route'] ?? '', '/');
        if (empty($this->route)) {
            $this->route = 'dashboard';
        }
    }

    public function dispatch() {
        // Check auth for protected routes
        $publicRoutes = ['login', 'register', 'forgot-password', 'api/login'];
        
        $routeParts = explode('/', $this->route);
        $page = $routeParts[0];
        $action = $routeParts[1] ?? 'index';
        $param = $routeParts[2] ?? null;

        // If not logged in and trying to access protected route
        if (!Session::isLoggedIn() && !in_array($page, $publicRoutes)) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        // If logged in and trying to access login/register
        if (Session::isLoggedIn() && in_array($page, ['login', 'register'])) {
            header('Location: ' . BASE_URL . '/dashboard');
            exit;
        }

        // Map route to controller file
        $controllerFile = BASE_PATH . '/controllers/' . $page . '.php';
        
        if (file_exists($controllerFile)) {
            // Pass action and param to controller
            $GLOBALS['action'] = $action;
            $GLOBALS['param'] = $param;
            $GLOBALS['routeParts'] = $routeParts;
            require_once $controllerFile;
        } else {
            // 404
            http_response_code(404);
            require_once BASE_PATH . '/views/errors/404.php';
        }
    }

    public static function redirect($path) {
        header('Location: ' . BASE_URL . '/' . ltrim($path, '/'));
        exit;
    }

    public static function back() {
        $referer = $_SERVER['HTTP_REFERER'] ?? BASE_URL . '/dashboard';
        header('Location: ' . $referer);
        exit;
    }

    public static function currentUrl() {
        return BASE_URL . '/' . trim($_GET['route'] ?? '', '/');
    }
}
