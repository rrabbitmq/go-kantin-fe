<?php
use App\Models\Keranjang;
 
require_once __DIR__ . '/../../config/response.php';
require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../config/auth.php';
 
handleCors();
 
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(405, ['message' => 'Method tidak diizinkan']);
}
 
$auth = verifyToken(); // wajib login
 
try {
    jsonResponse(200, [
        'success' => true,
        'data'    => ['jumlah' => Keranjang::countForUser((int) $auth['sub'])],
    ]);
} catch (PDOException $e) {
    error_log($e->getMessage());
    jsonResponse(500, ['message' => 'Terjadi kesalahan pada server']);
}