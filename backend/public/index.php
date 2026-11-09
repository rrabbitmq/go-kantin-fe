<?php
// Router sederhana: /api/xxx -> routes/api/xxx.php
require __DIR__ . '/../app/Core/Autoloader.php';
App\Core\Autoloader::register(__DIR__ . '/../app');
 
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
 
if (PHP_SAPI === 'cli-server' && $path !== '/' && is_file(__DIR__ . $path)) {
    return false; // biarkan php -S melayani file statis
}
 
$path = rtrim($path, '/') ?: '/';
 
$routes = [
    '/api/register'         => 'register.php',
    '/api/login'            => 'login.php',
    '/api/logout'           => 'logout.php',
    '/api/kategori'         => 'kategori.php',
    '/api/produk'           => 'produk.php',
    '/api/keranjang'        => 'keranjang.php',
    '/api/keranjang/jumlah' => 'keranjang-jumlah.php',
    '/api/checkout'         => 'checkout.php',
    '/api/transaksi'        => 'transaksi.php',
];
 
if (isset($routes[$path])) {
    require __DIR__ . '/../routes/api/' . $routes[$path];
    exit;
}
 
http_response_code(404);
header('Content-Type: application/json; charset=utf-8');
echo json_encode(['message' => 'Endpoint tidak ditemukan']);