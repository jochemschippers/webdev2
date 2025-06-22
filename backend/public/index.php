<?php
// public/index.php - Main API Router

// Load Composer's autoloader at the very beginning
require_once dirname(__DIR__) . '/vendor/autoload.php';

// Include all Controller classes
// It's crucial that these paths are correct relative to index.php
require_once dirname(__FILE__) . '/../app/controllers/Controller.php'; // Base Controller
require_once dirname(__FILE__) . '/../app/controllers/UserController.php';
require_once dirname(__FILE__) . '/../app/controllers/ManufacturerController.php';
require_once dirname(__FILE__) . '/../app/controllers/BrandController.php';
require_once dirname(__FILE__) . '/../app/controllers/GraphicCardController.php';
require_once dirname(__FILE__) . '/../app/controllers/OrderController.php';

// Include Middleware classes
require_once dirname(__FILE__) . '/../app/middlewares/AuthMiddleware.php'; // NEW: AuthMiddleware

// Use the namespaces for cleaner code
use App\Controllers\Controller;
use App\Controllers\UserController;
use App\Controllers\ManufacturerController;
use App\Controllers\BrandController;
use App\Controllers\GraphicCardController;
use App\Controllers\OrderController;
use App\Middlewares\AuthMiddleware; // NEW: Use AuthMiddleware
use App\Utils\Response;

// Instantiate the base Controller *before* any specific routing logic.
// This ensures that CORS headers are always set for every request, including OPTIONS preflight.
$baseController = new Controller(); // This will execute its constructor and set CORS headers.

// Get the request method (GET, POST, PUT, DELETE, OPTIONS)
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Get the requested URI path
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Remove the base path /api/ if present
$basePath = '/api';
if (strpos($requestUri, $basePath) === 0) {
    $requestUri = substr($requestUri, strlen($basePath));
}

// Trim slashes from the beginning and end for easier matching
$requestUri = trim($requestUri, '/');

// Variable to hold authenticated user data if authentication succeeds
$authenticatedUser = null;

// --- Centralized Routing Logic ---

switch ($requestUri) {
    // --- Public User Routes (Login & Register) ---
    case 'register':
        if ($requestMethod === 'POST') {
            $controller = new UserController();
            $controller->register();
        } else {
            Response::error("Method Not Allowed.", 405);
        }
        break;
    case 'login':
        if ($requestMethod === 'POST') {
            $controller = new UserController();
            $controller->login();
        } else {
            Response::error("Method Not Allowed.", 405);
        }
        break;

    // --- Protected User Profile Route ---
    case 'user/profile':
        if ($requestMethod === 'GET') {
            $authenticatedUser = AuthMiddleware::authenticate(); // Authenticate user
            if ($authenticatedUser) {
                $controller = new UserController();
                // Pass the user_id from the decoded token to the profile method
                $controller->profile($authenticatedUser->user_id);
            }
        } else {
            Response::error("Method Not Allowed.", 405);
        }
        break;

    // --- Manufacturers Routes ---
    case 'manufacturers':
        $controller = new ManufacturerController();
        if ($requestMethod === 'GET') {
            $controller->index(); // Publicly accessible to view manufacturers
        } elseif ($requestMethod === 'POST') {
            $authenticatedUser = AuthMiddleware::authenticate(['admin']); // Only admins can create
            if ($authenticatedUser) {
                $controller->store();
            }
        } else {
            Response::error("Method Not Allowed.", 405);
        }
        break;
    default:
        // Handle routes with IDs (e.g., /api/manufacturers/123)
        if (preg_match('/^manufacturers\/(\d+)$/', $requestUri, $matches)) {
            $id = (int)$matches[1];
            $controller = new ManufacturerController();
            if ($requestMethod === 'GET') {
                $controller->show($id); // Publicly accessible to view a single manufacturer
            } elseif ($requestMethod === 'PUT') {
                $authenticatedUser = AuthMiddleware::authenticate(['admin']); // Only admins can update
                if ($authenticatedUser) {
                    $controller->update($id);
                }
            } elseif ($requestMethod === 'DELETE') {
                $authenticatedUser = AuthMiddleware::authenticate(['admin']); // Only admins can delete
                if ($authenticatedUser) {
                    $controller->destroy($id);
                }
            } else {
                Response::error("Method Not Allowed.", 405);
            }
        }
        // --- Brands Routes ---
        elseif ($requestUri === 'brands') {
            $controller = new BrandController();
            if ($requestMethod === 'GET') {
                $controller->index(); // Publicly accessible
            } elseif ($requestMethod === 'POST') {
                $authenticatedUser = AuthMiddleware::authenticate(['admin']); // Only admins can create
                if ($authenticatedUser) {
                    $controller->store();
                }
            } else {
                Response::error("Method Not Allowed.", 405);
            }
        } elseif (preg_match('/^brands\/(\d+)$/', $requestUri, $matches)) {
            $id = (int)$matches[1];
            $controller = new BrandController();
            if ($requestMethod === 'GET') {
                $controller->show($id); // Publicly accessible
            } elseif ($requestMethod === 'PUT') {
                $authenticatedUser = AuthMiddleware::authenticate(['admin']); // Only admins can update
                if ($authenticatedUser) {
                    $controller->update($id);
                }
            } elseif ($requestMethod === 'DELETE') {
                $authenticatedUser = AuthMiddleware::authenticate(['admin']); // Only admins can delete
                if ($authenticatedUser) {
                    $controller->destroy($id);
                }
            } else {
                Response::error("Method Not Allowed.", 405);
            }
        }
        // --- Graphic Cards Routes ---
        elseif ($requestUri === 'graphic-cards') {
            $controller = new GraphicCardController();
            if ($requestMethod === 'GET') {
                $controller->index(); // Publicly accessible
            } elseif ($requestMethod === 'POST') {
                $authenticatedUser = AuthMiddleware::authenticate(['admin']); // Only admins can create
                if ($authenticatedUser) {
                    $controller->store();
                }
            } else {
                Response::error("Method Not Allowed.", 405);
            }
        } elseif (preg_match('/^graphic-cards\/(\d+)$/', $requestUri, $matches)) {
            $id = (int)$matches[1];
            $controller = new GraphicCardController();
            if ($requestMethod === 'GET') {
                $controller->show($id); // Publicly accessible
            } elseif ($requestMethod === 'PUT') {
                $authenticatedUser = AuthMiddleware::authenticate(['admin']); // Only admins can update
                if ($authenticatedUser) {
                    $controller->update($id);
                }
            } elseif ($requestMethod === 'DELETE') {
                $authenticatedUser = AuthMiddleware::authenticate(['admin']); // Only admins can delete
                if ($authenticatedUser) {
                    $controller->destroy($id);
                }
            } else {
                Response::error("Method Not Allowed.", 405);
            }
        }
        // --- Orders Routes ---
        elseif ($requestUri === 'orders') {
            $controller = new OrderController();
            if ($requestMethod === 'GET') {
                $authenticatedUser = AuthMiddleware::authenticate(); // Authenticate to view orders
                if ($authenticatedUser) {
                    // Pass user_id and role to index method for filtering (user's own orders vs. all for admin)
                    $controller->index($authenticatedUser->user_id, $authenticatedUser->role);
                } else {
                    Response::error("Authentication required to view orders.", 401);
                }
            } elseif ($requestMethod === 'POST') {
                $authenticatedUser = AuthMiddleware::authenticate(); // Authenticate to create an order
                if ($authenticatedUser) {
                    // Pass the authenticated user's ID to the store method
                    $controller->store($authenticatedUser->user_id);
                }
            } else {
                Response::error("Method Not Allowed.", 405);
            }
        }
        // Route for getting, updating, or deleting a specific order by ID
        elseif (preg_match('/^orders\/(\d+)$/', $requestUri, $matches)) {
            $id = (int)$matches[1];
            $controller = new OrderController();
            if ($requestMethod === 'GET') {
                $authenticatedUser = AuthMiddleware::authenticate(); // Authenticate to view a specific order
                if ($authenticatedUser) {
                    // Pass user_id and role to show method for authorization (can view own or any if admin)
                    $controller->show($id, $authenticatedUser->user_id, $authenticatedUser->role);
                }
            } elseif ($requestMethod === 'PUT') {
                $authenticatedUser = AuthMiddleware::authenticate(['admin']); // Only admins can update order status
                if ($authenticatedUser) {
                    $controller->update($id);
                }
            } elseif ($requestMethod === 'DELETE') {
                $authenticatedUser = AuthMiddleware::authenticate(['admin']); // Only admins can delete orders
                if ($authenticatedUser) {
                    $controller->destroy($id);
                }
            } else {
                Response::error("Method Not Allowed.", 405);
            }
        }
        // If no route matched
        else {
            Response::error("API Endpoint Not Found.", 404);
        }
        break;
}

// If none of the above routes matched and no response was sent, it means an unhandled case.
// This block should ideally not be reached if all routes are covered or Response::error exits.
if (!headers_sent()) {
    Response::error("Unhandled Request or Invalid Endpoint.", 404);
}
