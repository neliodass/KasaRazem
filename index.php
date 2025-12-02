<?php
require_once('core/Router.php');
require_once ('src/controllers/GroupController.php');
require_once ('src/controllers/SecurityController.php');

$router = new Router();

$router->add('GET', 'login',  ['controller' => 'SecurityController', 'action' => 'login']);
$router->add('POST', 'login', ['controller' => 'SecurityController', 'action' => 'login']);
$router->add('GET', 'register', ['controller' => 'SecurityController', 'action' => 'register']);
$router->add('POST', 'register', ['controller' => 'SecurityController', 'action' => 'register']);
$router->add('GET', 'logout', ['controller' => 'SecurityController', 'action' => 'logout']);
$router->add('GET', 'groups', ['controller' => 'GroupController', 'action' => 'groups']);
$router->add('GET', 'groups/add', ['controller' => 'GroupController', 'action' => 'addGroup']);
$router->add('GET', 'groups/join', ['controller' => 'GroupController', 'action' => 'joinGroup']);
$router->add('GET', 'groups/join/{code}', ['controller' => 'GroupController', 'action' => 'joinGroup']);
$router->add('POST', 'groups/join', ['controller' => 'GroupController', 'action' => 'joinGroup']);
$router->add('GET','groups/create',['controller' => 'GroupController', 'action' => 'createGroup']);
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$router->run($path,$_SERVER['REQUEST_METHOD']);