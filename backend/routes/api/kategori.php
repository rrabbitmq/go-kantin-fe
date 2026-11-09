<?php
use App\Models\KategoriProduk;
 
require_once __DIR__ . '/../../config/response.php';
require_once __DIR__ . '/../../config/cors.php';
 
handleCors();
 
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(405, ['message' => 'Method tidak diizinkan']);
}
 
try {
    jsonResponse(200, ['success' => true, 'data' => KategoriProduk::all()]);
} catch (PDOException $e) {
    error_log($e->getMessage());
    jsonResponse(500, ['message' => 'Terjadi kesalahan pada server']);
}