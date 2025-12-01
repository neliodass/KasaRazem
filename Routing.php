<?php
class Routing
{
    public static $routes = [
//        "login" => ["controller" => "SecurityController", "action" => "login"],
//        "register" => ["controller" => "SecurityController", "action" => "register"],
    ];
    public static function run(string $path)
    {
        $path = trim($path, '/');
        $segments = explode('/', $path);
        $routeName = $segments[0] ?? '';
        if (!isset(self::$routes[$routeName])) {
            include("public/views/404.html");
            return;
        }
        $route = self::$routes[$routeName];
        $controllerName = $route['controller'];
        $actionName = $route['action'];
        if (!class_exists($controllerName) || !method_exists($controllerName, $actionName)) {
            include("public/views/404.html");
            return;
        }
        if(!method_exists($controllerName, "getInstance")) {
            throw new Exception("Controller does not implement getInstance method");
        }

        $controllerObj = $controllerName::getInstance();
        $param = $segments[1] ?? null;
        $controllerObj->$actionName($param);


    }
}
