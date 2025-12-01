<?php
require_once('Routing.php');

//$path = trim($_SERVER['REQUEST_URI'], '/');
//$path = parse_url($path, PHP_URL_PATH);
//Routing::run($path);
echo file_get_contents('public/views/login.html');