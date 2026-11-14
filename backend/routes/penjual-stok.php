<?php
use App\Core\HttpException;
use App\Models\Menu;
use App\Models\Penjual;
use App\Services\MenuService;

require_once __DIR__ . '/../../config/response.php';
require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../config/input.php';
require_once __DIR__ . '/../../config/auth.php';

handleCors();

/*
 * GET  /api/penjual/stok   riwayat perubahan stok (Mutasi Stok)
 * POST /api/penjual/stok   {id_varian, jenis: masuk|keluar|penyesuaian, jumlah, keterangan}
 */
$auth   = requireRole('penjual');
$userId = (int) $auth['sub'];
$method = $_SERVER['REQUEST_METHOD'];

try {
    $profilId = Penjual::profilIdForUser($userId);
    if (!$profilId) {
        jsonResponse(403, ['message' => 'Profil penjual belum tersedia.']);
    }

    if ($method === 'GET') {
        jsonResponse(200, ['success' => true, 'data' => Menu::riwayatStok($profilId)]);
    }

    if ($method === 'POST') {
        $in = getInput();
        $idVarian = filter_var($in['id_varian'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $jumlah   = filter_var($in['jumlah'] ?? null, FILTER_VALIDATE_INT);
        $jenis    = is_string($in['jenis'] ?? null) ? $in['jenis'] : '';
        $ket      = is_string($in['keterangan'] ?? null) ? trim($in['keterangan']) : '';

        if (!$idVarian || $jumlah === false || $jumlah === null) {
            jsonResponse(400, ['message' => 'Validasi gagal', 'errors' => ['jumlah' => 'Produk dan jumlah wajib diisi dengan benar.']]);
        }

        $hasil = MenuService::ubahStok($profilId, $userId, $idVarian, $jenis, $jumlah, $ket);
        jsonResponse(200, ['success' => true, 'message' => $hasil['pesan'], 'data' => $hasil]);
    }

    jsonResponse(405, ['message' => 'Method tidak diizinkan']);
} catch (HttpException $e) {
    jsonResponse($e->status(), ['message' => $e->getMessage(), 'errors' => $e->errors()]);
} catch (PDOException $e) {
    error_log($e->getMessage());
    jsonResponse(500, ['message' => 'Terjadi kesalahan pada server']);
}