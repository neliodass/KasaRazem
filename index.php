<?php
require_once('core/Router.php');
require_once ('src/controllers/GroupController.php');
require_once ('src/controllers/SecurityController.php');
require_once ('src/controllers/ExpenseController.php');
require_once ('src/controllers/BalanceController.php');
require_once ('src/controllers/ListController.php');
require_once ('src/controllers/ProfileController.php');

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
$router->add('POST','groups/{groupId}/delete',['controller' => 'GroupController', 'action' => 'deleteGroup']);
$router->add('GET','groups/{groupId}/edit',['controller' => 'GroupController', 'action' => 'editGroup']);
$router->add('POST','groups/{groupId}/edit',['controller' => 'GroupController', 'action' => 'editGroup']);
$router->add('POST','groups/{groupId}/deleteUser',['controller' => 'GroupController', 'action' => 'deleteUserFromGroup']);
$router->add('GET','groups/{groupId}/expense/{expenseId}',['controller' => 'ExpenseController', 'action' => 'getExpense']);
$router->add('POST','groups/{groupId}/expense/{expenseId}/delete',['controller' => 'ExpenseController', 'action' => 'deleteExpense']);
$router->add('POST','groups/create',['controller' => 'GroupController', 'action' => 'createGroup']);
$router->add("GET",'groups/{groupId}',['controller' => 'GroupController', 'action' => 'groupDetails']);
$router->add("GET",'groups/{groupId}/expenses',['controller' => 'ExpenseController', 'action' => 'expenses']);
$router->add('GET','groups/{groupId}/addExpense',['controller' => 'ExpenseController', 'action' => 'addExpense']);
$router->add('POST','groups/{groupId}/addExpense',['controller' => 'ExpenseController', 'action' => 'addExpense']);
$router->add('GET','groups/{groupId}/expense/{expenseId}/edit',['controller' => 'ExpenseController', 'action' => 'editExpense']);
$router->add('POST','groups/{groupId}/expense/{expenseId}/edit',['controller' => 'ExpenseController', 'action' => 'updateExpense']);
$router->add("GET",'groups/{groupId}/settlements',['controller' => 'BalanceController', 'action' => 'settleDetails']);
$router->add("POST",'groups/{groupId}/settleDebt',['controller' => 'BalanceController', 'action' => 'settleDebt']);
$router->add("GET",'groups/{groupId}/balance',['controller' => 'BalanceController', 'action' => 'balance']);
$router->add("GET",'groups/{groupId}/lists',['controller' => 'ListController', 'action' => 'index']);
$router->add('GET', 'groups/{groupId}/lists/{listId}/items', ['controller' => 'ListController', 'action' => 'getListItems']);
$router->add('POST', 'groups/{groupId}/lists/{listId}/delete', ['controller' => 'ListController', 'action' => 'deleteList']);
$router->add('POST', 'groups/{groupId}/items/{itemId}/toggle', ['controller' => 'ListController', 'action' => 'toggleItem']);
$router->add('POST', 'groups/{groupId}/items/{itemId}/delete', ['controller' => 'ListController', 'action' => 'deleteItem']);
$router->add('POST', 'groups/{groupId}/lists/add', ['controller' => 'ListController', 'action' => 'addList']);
$router->add('POST', 'groups/{groupId}/lists/{listId}/items/add', ['controller' => 'ListController', 'action' => 'addItem']);
$router->add('GET', 'profile', ['controller' => 'ProfileController', 'action' => 'getProfile']);

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$router->run($path,$_SERVER['REQUEST_METHOD']);