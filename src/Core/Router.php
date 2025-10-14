<?php

use FastRoute\RouteCollector;
use function FastRoute\simpleDispatcher;

require_once __DIR__ . '/../../vendor/autoload.php';

$dispatcher = simpleDispatcher(function (RouteCollector $r) {
    $r->addRoute('POST', '/api/register', ['Src\Controllers\AuthController', 'register']);
    $r->addRoute('POST', '/api/login', ['Src\Controllers\AuthController', 'login']);
    $r->addRoute('POST', '/api/bookings', ['Src\Controllers\BookingController', 'create']);
    $r->addRoute('GET', '/api/download_invoice', ['Src\Controllers\InvoiceController', 'download']);

});

// minimal dispatch
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// remove trailing slash normalize
if ($uri !== '/' && str_ends_with($uri, '/')) {
    $uri = rtrim($uri, '/');
}

$routeInfo = $dispatcher->dispatch($httpMethod, $uri);
switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        http_response_code(404);
        echo json_encode(['error' => 'Not Found']);
        break;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        http_response_code(405);
        echo json_encode(['error' => 'Method Not Allowed']);
        break;
    case FastRoute\Dispatcher::FOUND:
        [$class, $method] = $routeInfo[1];
        $vars = $routeInfo[2];
        $controller = new $class();
        $controller->$method();
        break;
}
