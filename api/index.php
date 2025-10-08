<?php
// API REST para el Sistema del Colegio Dora Schmidt - A
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Manejar preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../config/database.php';
require_once '../includes/session.php';

// Iniciar sesión para mantener compatibilidad
session_start();

// Router simple para la API
class ApiRouter {
    private $routes = [];
    
    public function addRoute($method, $path, $callback) {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'callback' => $callback
        ];
    }
    
    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $path = str_replace('/api', '', $path);
        
        foreach ($this->routes as $route) {
            if ($route['method'] === $method && $this->matchPath($route['path'], $path)) {
                $params = $this->extractParams($route['path'], $path);
                call_user_func($route['callback'], $params);
                return;
            }
        }
        
        $this->sendResponse(['error' => 'Endpoint not found'], 404);
    }
    
    private function matchPath($routePath, $requestPath) {
        $routePath = preg_replace('/\{[^}]+\}/', '([^/]+)', $routePath);
        return preg_match('#^' . $routePath . '$#', $requestPath);
    }
    
    private function extractParams($routePath, $requestPath) {
        $routePath = preg_replace('/\{([^}]+)\}/', '(?P<$1>[^/]+)', $routePath);
        preg_match('#^' . $routePath . '$#', $requestPath, $matches);
        return array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
    }
    
    public function sendResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit();
    }
}

// Crear instancia del router
$router = new ApiRouter();

// Incluir controladores de la API
require_once 'controllers/PadresController.php';

// Definir rutas
$router->addRoute('GET', '/padres', function($params) {
    $controller = new PadresController();
    $controller->getAll();
});

$router->addRoute('POST', '/padres', function($params) {
    $controller = new PadresController();
    $controller->create();
});

$router->addRoute('GET', '/padres/{id}', function($params) {
    $controller = new PadresController();
    $controller->getById($params['id']);
});

$router->addRoute('PUT', '/padres/{id}', function($params) {
    $controller = new PadresController();
    $controller->update($params['id']);
});

$router->addRoute('DELETE', '/padres/{id}', function($params) {
    $controller = new PadresController();
    $controller->delete($params['id']);
});

$router->addRoute('POST', '/padres/{id}/asociar', function($params) {
    $controller = new PadresController();
    $controller->asociarEstudiante($params['id']);
});

$router->addRoute('DELETE', '/padres/{id}/desasociar', function($params) {
    $controller = new PadresController();
    $controller->desasociarEstudiante($params['id']);
});

// Manejar la petición
$router->handleRequest();
?>
