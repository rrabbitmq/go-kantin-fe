<?php
use App\Core\HttpException;
use App\Services\CheckoutService;
 
require_once __DIR__ . '/../../config/response.php';
require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../config/input.php';
require_once __DIR__ . '/../../config/auth.php';
 
handleCors();
 
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(405, ['message' => 'Method tidak diizinkan']);
}
 
$auth    = requireRole('pembeli');
$input   = getInput();
$catatan = is_string($input['catatan'] ?? null) ? trim($input['catatan']) : '';
 
if (strlen($catatan) > 500) {
    jsonResponse(400, ['message' => 'Validasi gagal', 'errors' => ['catatan' => 'Catatan maksimal 500 karakter.']]);
}
 
try {
    $orders = CheckoutService::checkout((int) $auth['sub'], $catatan);
 
    jsonResponse(201, [
        'success' => true,
        'message' => 'Pesanan berhasil dibuat. Silakan lanjutkan pembayaran.',
        'data'    => ['orders' => $orders],
    ]);
} catch (HttpException $e) {
    jsonResponse($e->status(), ['message' => $e->getMessage(), 'errors' => $e->errors()]);
} catch (PDOException $e) {
    error_log($e->getMessage());
    jsonResponse(500, ['message' => 'Terjadi kesalahan pada server']);
}