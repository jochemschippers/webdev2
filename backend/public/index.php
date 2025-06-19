<?php
// public/index.php - Main API Router

// Autoload utility classes (Response, etc.)
require_once dirname(__FILE__) . '/../app/utils/Response.php';

// Include all Controller classes
// It's crucial that these paths are correct relative to index.php
require_once dirname(__FILE__) . '/../app/controllers/Controller.php'; // Base Controller
require_once dirname(__FILE__) . '/../app/controllers/UserController.php';
require_once dirname(__FILE__) . '/../app/controllers/ManufacturerController.php';
require_once dirname(__FILE__) . '/../app/controllers/BrandController.php';
require_once dirname(__FILE__) . '/../app/controllers/GraphicCardController.php';
require_once dirname(__FILE__) . '/../app/controllers/OrderController.php';

// Use the namespaces for cleaner code
use App\Controllers\Controller; // Use the base Controller here
use App\Controllers\UserController;
use App\Controllers\ManufacturerController;
use App\Controllers\BrandController;
use App\Controllers\GraphicCardController;
use App\Controllers\OrderController;
use App\Utils\Response; // Use the Response utility class

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

// --- Centralized Routing Logic ---

switch ($requestUri) {
    // --- User Routes ---
    case 'register':
        if ($requestMethod === 'POST') {
            $controller = new UserController();
            $controller->register();
        }
        break;
    case 'login':
        if ($requestMethod === 'POST') {
            $controller = new UserController();
            $controller->login();
        }
        break;
    case 'user/profile': // Example protected route (authentication logic to be added later if needed)
        if ($requestMethod === 'GET') {
            $controller = new UserController();
            $controller->profile();
        }
        break;

    // --- Manufacturers Routes ---
    case 'manufacturers':
        $controller = new ManufacturerController();
        if ($requestMethod === 'GET') {
            $controller->index();
        } elseif ($requestMethod === 'POST') {
            $controller->store();
        }
        break;
    default:
        // Handle routes with IDs (e.g., /api/manufacturers/123)
        if (preg_match('/^manufacturers\/(\d+)$/', $requestUri, $matches)) {
            $id = (int)$matches[1];
            $controller = new ManufacturerController();
            if ($requestMethod === 'GET') {
                $controller->show($id);
            } elseif ($requestMethod === 'PUT') {
                $controller->update($id);
            } elseif ($requestMethod === 'DELETE') {
                $controller->destroy($id);
            }
        }
        // --- Brands Routes ---
        elseif ($requestUri === 'brands') {
            $controller = new BrandController();
            if ($requestMethod === 'GET') {
                $controller->index();
            } elseif ($requestMethod === 'POST') {
                $controller->store();
            }
        } elseif (preg_match('/^brands\/(\d+)$/', $requestUri, $matches)) {
            $id = (int)$matches[1];
            $controller = new BrandController();
            if ($requestMethod === 'GET') {
                $controller->show($id);
            } elseif ($requestMethod === 'PUT') {
                $controller->update($id);
            } elseif ($requestMethod === 'DELETE') {
                $controller->destroy($id);
            }
        }
        // --- Graphic Cards Routes ---
        elseif ($requestUri === 'graphic-cards') {
            $controller = new GraphicCardController();
            if ($requestMethod === 'GET') {
                $controller->index();
            } elseif ($requestMethod === 'POST') {
                $controller->store();
            }
        } elseif (preg_match('/^graphic-cards\/(\d+)$/', $requestUri, $matches)) {
            $id = (int)$matches[1];
            $controller = new GraphicCardController();
            if ($requestMethod === 'GET') {
                $controller->show($id);
            } elseif ($requestMethod === 'PUT') {
                $controller->update($id);
            } elseif ($requestMethod === 'DELETE') {
                $controller->destroy($id);
            }
        }
        // --- Orders Routes ---
        elseif ($requestUri === 'orders') {
            $controller = new OrderController();
            if ($requestMethod === 'GET') {
                $controller->index();
            } elseif ($requestMethod === 'POST') {
                $controller->store();
            }
        } elseif (preg_match('/^orders\/(\d+)$/', $requestUri, $matches)) {
            $id = (int)$matches[1];
            $controller = new OrderController();
            if ($requestMethod === 'GET') {
                $controller->show($id);
            } elseif ($requestMethod === 'PUT') {
                $controller->update($id);
            } elseif ($requestMethod === 'DELETE') {
                $controller->destroy($id);
            }
        }
        // If no route matched
        else {
            Response::error("API Endpoint Not Found.", 404);
        }
        break; // Break from the switch after handling default or ID-based routes
}

// If none of the above routes matched and no response was sent, send a 405 error for unsupported method.
// This handles cases where a correct URI is hit but with an invalid HTTP method.
// This check is now less critical for CORS preflights, as the base controller ensures headers.
if (!headers_sent()) {
    Response::error("Method Not Allowed or Invalid Request.", 405); // 405 Method Not Allowed
}
?>
