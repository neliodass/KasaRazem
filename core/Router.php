<?php

require_once 'src/controllers/SecurityController.php';
require_once 'src/controllers/GroupController.php';
require_once 'src/controllers/ExpenseController.php';
class Router
{
    private $routes = [];

    public function add(string $method, string $path, array $controller): void
    {
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $path);
        $pattern = "#^" . $pattern . "$#";

        $this->routes[] = [
            'method' => $method,
            'pattern' => $pattern,
            'controller' => $controller['controller'],
            'action' => $controller['action']
        ];
    }
    public function run(string $path, string $requestMethod): void
    {
        $path = trim($path, '/');
        foreach ($this->routes as $route) {
            if ($route['method'] !== $requestMethod) {
                continue;
            }
            if (preg_match($route['pattern'], $path, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                $this->dispatch($route['controller'], $route['action'], $params);
                return;
            }
        }
        include("public/views/404.html");
        exit();
    }

    private function dispatch($controllerName, $actionName, $params): void
    {
        if (!class_exists($controllerName) || !method_exists($controllerName, $actionName)) {
            throw new Exception("Controller or Action not found");
        }
        if (!method_exists($controllerName, 'getInstance')) {
            throw new Exception("Controller must implement getInstance");
        }

        $controllerObj = $controllerName::getInstance();
        $controllerObj->$actionName(...array_values($params));
    }
}
