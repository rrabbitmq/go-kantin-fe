<?php
use App\Services\KepadatanService;

require_once __DIR__ . '/../../config/response.php';
require_once __DIR__ . '/../../config/cors.php';

handleCors();

/*
 * GET /api/antrian/kepadatan
 * Kepadatan antrian slot 30 menit saat ini (pesanan aktif / kapasitas 20).
 */
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(405, ['message' => 'Method tidak diizinkan']);
}

try {
    jsonResponse(200, ['success' => true, 'data' => KepadatanService::saatIni()]);
} catch (PDOException | RuntimeException $e) {
    error_log($e->getMessage());
    jsonResponse(500, ['message' => 'Terjadi kesalahan pada server']);
}
