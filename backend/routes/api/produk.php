<?php
use App\Models\Produk;
 
require_once __DIR__ . '/../../config/response.php';
require_once __DIR__ . '/../../config/cors.php';
 
handleCors();
 
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(405, ['message' => 'Method tidak diizinkan']);
}

$intOpt   = ['options' => ['min_range' => 1]];
$kategori = filter_var($_GET['kategori'] ?? null, FILTER_VALIDATE_INT, $intOpt);
$id       = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT, $intOpt);
$terlaris = ($_GET['terlaris'] ?? '') === '1';
$limit    = filter_var($_GET['limit'] ?? 50, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 100]]) ?: 50;
 
try {
    if ($id) {
        $produk = Produk::find($id);
        if (!$produk) {
            jsonResponse(404, ['message' => 'Produk tidak ditemukan']);
        }
        jsonResponse(200, ['success' => true, 'data' => [$produk]]);
    }
 
    jsonResponse(200, ['success' => true, 'data' => Produk::all($kategori ?: null, $terlaris, $limit)]);
} catch (PDOException $e) {
    error_log($e->getMessage());
    jsonResponse(500, ['message' => 'Terjadi kesalahan pada server']);
}