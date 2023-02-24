<?php
require "../boostrap.php";
use PruebaAPI\Controllers\ContactosController;
use PruebaAPI\Middleware\AuthController;

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode( '/', $uri );

// all of our endpoints start with /person
// everything else results in a 404 Not Found
if ($uri[1] !== 'contactos' && $uri[1] !== 'login') {
    header("HTTP/1.1 404 Not Found");
    exit();
}

// the user id is, of course, optional and must be a number:
$userId = null;
if (isset($uri[2])) {
    $userId = $uri[2];
}

if($uri[1] == 'login'){
    $requestMethod = $_SERVER["REQUEST_METHOD"];
    $controller = new AuthController($dbConnection, $requestMethod);
    $controller->processRequest();
    exit();
}

if($uri[1] == 'contactos') {
    $requestMethod = $_SERVER["REQUEST_METHOD"];
    $controller = new AuthController($dbConnection, $requestMethod);
    if($controller->validarToken()){
        $controller = new ContactosController($dbConnection, $requestMethod, $userId);
        $controller->processRequest();
    } else {
        header("HTTP/1.1 401 Unauthorized");
        echo "Acceso no autorizado";
        exit();
    }
}

// $requestMethod = $_SERVER["REQUEST_METHOD"];

// // pass the request method and user ID to the PersonController and process the HTTP request:
// $controller = new ContactosController($dbConnection, $requestMethod, $userId);
// $controller->processRequest();