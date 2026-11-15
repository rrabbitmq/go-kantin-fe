<?php
use App\Models\Transaksi;
 
require_once __DIR__ . '/../../config/response.php';
require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../config/auth.php';
 
handleCors();
 
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(405, ['message' => 'Method tidak diizinkan']);
}
 
/*
 * GET /api/transaksi        riwayat pesanan milik pembeli yang login
 * GET /api/transaksi?id=ID  detail satu pesanan (hanya milik sendiri)
 */
$auth   = requireRole('pembeli');
$userId = (int) $auth['sub'];
$id     = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
 
try {
    if ($id) {
        $order = Transaksi::findForUser($id, $userId);
        if (!$order) {
            jsonResponse(404, ['message' => 'Pesanan tidak ditemukan']);
        }
        jsonResponse(200, ['success' => true, 'data' => ['order' => $order, 'items' => Transaksi::items($id)]]);
    }
 
    jsonResponse(200, ['success' => true, 'data' => Transaksi::forUser($userId)]);
} catch (PDOException $e) {
    error_log($e->getMessage());
    jsonResponse(500, ['message' => 'Terjadi kesalahan pada server']);
}