<?php
// public/index.php - Main API Router

// Autoload utility classes (Response, etc.)
require_once dirname(__FILE__) . '/../app/utils/Response.php'; // Adjust path

// Include specific controllers for routing
use App\Controllers\UserController;


// Get the request method (GET, POST, PUT, DELETE, OPTIONS)
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Get the requested URI path
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$basePath = '/api'; 

if (strpos($requestUri, $basePath) === 0) {
    $requestUri = substr($requestUri, strlen($basePath));
}

// Trim slashes from the beginning and end for easier matching
$requestUri = trim($requestUri, '/');

// --- Simple Routing Logic ---

// User Routes
if ($requestUri === 'register' && $requestMethod === 'POST') {
    require_once dirname(__FILE__) . '/../app/controllers/UserController.php';
    $controller = new UserController();
    $controller->register();
} elseif ($requestUri === 'login' && $requestMethod === 'POST') {
    require_once dirname(__FILE__) . '/../app/controllers/UserController.php';
    $controller = new UserController();
    $controller->login();
} elseif ($requestUri === 'user/profile' && $requestMethod === 'GET') {
    // This route will eventually need authentication middleware.
    // For initial testing, we'll let it pass (but it's not secure).
    require_once dirname(__FILE__) . '/../app/controllers/UserController.php';
    $controller = new UserController();
    $controller->profile();
}

else {
    // Fallback for unhandled routes
    \App\Utils\Response::error("API Endpoint Not Found.", 404);
}

?>
