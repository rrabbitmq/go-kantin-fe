<?php
use App\Core\HttpException;
use App\Models\Penjual;
use App\Services\PesananServices;

require_once __DIR__ . '/../../config/response.php';
require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../config/input.php';
require_once __DIR__ . '/../../config/auth.php';

handleCors();

/*
 * GET /api/penjual/pesanan?status=aktif|semua&limit=N   daftar pesanan milik penjual
 * PUT /api/penjual/pesanan   {id, aksi}                 aksi: konfirmasi|proses|siap|selesai|batal
 */
$auth   = requireRole('penjual');
$method = $_SERVER['REQUEST_METHOD'];

try {
    $profilId = Penjual::profilIdForUser((int) $auth['sub']);
    if (!$profilId) {
        jsonResponse(403, ['message' => 'Profil penjual belum tersedia.']);
    }

    if ($method === 'GET') {
        $mode  = ($_GET['status'] ?? 'aktif') === 'semua' ? 'semua' : 'aktif';
        $limit = filter_var($_GET['limit'] ?? 100, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 500]]) ?: 100;

        jsonResponse(200, ['success' => true, 'data' => Penjual::pesanan($profilId, $mode, $limit)]);
    }

    if ($method === 'PUT' || $method === 'POST') {
        $in   = getInput();
        $id   = filter_var($in['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $aksi = is_string($in['aksi'] ?? null) ? $in['aksi'] : '';

        if (!$id || $aksi === '') {
            jsonResponse(400, ['message' => 'Validasi gagal', 'errors' => ['id' => 'ID pesanan dan aksi wajib diisi.']]);
        }

        $hasil = PesananService::ubahStatus($profilId, $id, $aksi, (int) $auth['sub']);
        jsonResponse(200, ['success' => true, 'message' => $hasil['pesan'], 'data' => $hasil]);
    }

    jsonResponse(405, ['message' => 'Method tidak diizinkan']);
} catch (HttpException $e) {
    jsonResponse($e->status(), ['message' => $e->getMessage(), 'errors' => $e->errors()]);
} catch (PDOException $e) {
    error_log($e->getMessage());
    jsonResponse(500, ['message' => 'Terjadi kesalahan pada server']);
}