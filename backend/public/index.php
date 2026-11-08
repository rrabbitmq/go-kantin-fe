<?php

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
 
if (PHP_SAPI === 'cli-server' && $path !== '/' && is_file(__DIR__ . $path)) {
    return false;
}
 
$path = rtrim($path, '/') ?: '/';
 
$routes = [
    '/api/register' => 'register.php',
    '/api/login'    => 'login.php',
    '/api/logout'   => 'logout.php',
];
 
if (isset($routes[$path])) {
    require __DIR__ . '/../routes/api/' . $routes[$path];
    exit;
}
 
http_response_code(404);
header('Content-Type: application/json; charset=utf-8');
echo json_encode(['message' => 'Endpoint tidak ditemukan']);

