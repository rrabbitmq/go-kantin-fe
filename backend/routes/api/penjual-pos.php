<?php
use App\Core\HttpException;
use App\Models\Menu;
use App\Models\Penjual;
use App\Services\PesananService;
use App\Services\PosService;

require_once __DIR__ . '/../../config/response.php';
require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../config/input.php';
require_once __DIR__ . '/../../config/auth.php';

handleCors();

/*
 * GET  /api/penjual/pos                 katalog aktif untuk POS
 * POST /api/penjual/pos                 aksi=jual|tiket|selesai
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
        $menu = array_values(array_filter(
            Menu::untukPenjual($profilId),
            static fn(array $p): bool => ($p['status'] ?? '') === 'aktif' && (int) ($p['id_varian'] ?? 0) > 0
        ));
        jsonResponse(200, [
            'success' => true,
            'data' => [
                'products' => $menu,
                'receipt_prefix' => 'POS-' . date('ymd'),
            ],
        ]);
    }

    if ($method === 'POST') {
        $in = getInput();
        $aksi = (string) ($in['aksi'] ?? 'jual');

        if ($aksi === 'jual') {
            $items = $in['items'] ?? [];
            if (!is_array($items)) {
                jsonResponse(400, ['message' => 'Daftar item tidak valid.']);
            }
            $hasil = PosService::jual($profilId, $userId, $items, (string) ($in['metode'] ?? 'tunai'));
            jsonResponse(201, ['success' => true, 'message' => 'Transaksi POS disimpan.', 'data' => $hasil]);
        }

        if ($aksi === 'tiket') {
            $tiket = PosService::cariTiket($profilId, (string) ($in['kode'] ?? ''));
            jsonResponse(200, ['success' => true, 'data' => $tiket]);
        }

        if ($aksi === 'selesai') {
            $id = (int) ($in['id'] ?? 0);
            if ($id < 1) {
                jsonResponse(400, ['message' => 'ID transaksi wajib diisi.']);
            }
            $hasil = PesananService::ubahStatus($profilId, $id, 'selesai', $userId);
            jsonResponse(200, ['success' => true, 'message' => $hasil['pesan'] ?? 'Pesanan selesai.', 'data' => $hasil]);
        }

        jsonResponse(400, ['message' => 'Aksi tidak dikenal.']);
    }

    jsonResponse(405, ['message' => 'Method tidak diizinkan']);
} catch (HttpException $e) {
    jsonResponse($e->status(), ['message' => $e->getMessage(), 'errors' => $e->errors()]);
} catch (PDOException | RuntimeException $e) {
    error_log($e->getMessage());
    jsonResponse(500, ['message' => 'Terjadi kesalahan pada server']);
}
