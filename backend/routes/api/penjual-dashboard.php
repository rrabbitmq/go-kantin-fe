<?php
use App\Models\Penjual;

require_once __DIR__ . '/../../config/response.php';
require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../config/auth.php';

handleCors();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(405, ['message' => 'Method tidak diizinkan']);
}

$auth = requireRole('penjual');

try {
    $profilId = Penjual::profilIdForUser((int) $auth['sub']);
    if (!$profilId) {
        jsonResponse(403, ['message' => 'Profil penjual belum tersedia.']);
    }

    jsonResponse(200, ['success' => true, 'data' => Penjual::dashboard($profilId)]);
} catch (PDOException $e) {
    error_log($e->getMessage());
    jsonResponse(500, ['message' => 'Terjadi kesalahan pada server']);
}