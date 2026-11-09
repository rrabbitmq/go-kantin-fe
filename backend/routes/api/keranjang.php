<?php
use App\Core\HttpException;
use App\Models\Keranjang;
use App\Services\CheckoutService;
 
require_once __DIR__ . '/../../config/response.php';
require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../config/input.php';
require_once __DIR__ . '/../../config/auth.php';
 
handleCors();
 

$auth   = requireRole('pembeli');
$userId = (int) $auth['sub'];
$method = $_SERVER['REQUEST_METHOD'];
 
$ringkasan = static function () use ($userId): array {
    $items = Keranjang::itemsForUser($userId);
 
    return [
        'items'    => $items,
        'jumlah'   => (int) array_sum(array_column($items, 'quantity')),
        'subtotal' => (float) array_sum(array_column($items, 'line_total')),
        // biaya layanan dikenakan per pesanan (satu pesanan = satu penjual)
        'biaya_layanan' => (float) CheckoutService::BIAYA_LAYANAN * count(array_unique(array_column($items, 'seller_id'))),
    ];
};
 
$intOpt = ['options' => ['min_range' => 1]];
$qtyOpt = ['options' => ['min_range' => 1, 'max_range' => 99]];
 
try {
    if ($method === 'GET') {
        jsonResponse(200, ['success' => true, 'data' => $ringkasan()]);
    }
 
    if ($method === 'POST') {
        $in       = getInput();
        $varianId = filter_var($in['id_varian_produk'] ?? null, FILTER_VALIDATE_INT, $intOpt);
        $produkId = filter_var($in['id_produk'] ?? null, FILTER_VALIDATE_INT, $intOpt);
        $jumlah   = filter_var($in['jumlah'] ?? 1, FILTER_VALIDATE_INT, $qtyOpt);
 
        if (!$varianId && !$produkId) {
            jsonResponse(400, ['message' => 'Validasi gagal', 'errors' => ['id_produk' => 'Produk wajib dipilih.']]);
        }
        if ($jumlah === false) {
            jsonResponse(400, ['message' => 'Validasi gagal', 'errors' => ['jumlah' => 'Jumlah harus 1-99.']]);
        }
 
        Keranjang::addItem($userId, $varianId ?: null, $produkId ?: null, $jumlah);
        jsonResponse(201, ['success' => true, 'message' => 'Ditambahkan ke keranjang.', 'data' => $ringkasan()]);
    }
 
    if ($method === 'PUT') {
        $in       = getInput();
        $idDetail = filter_var($in['id_detail_keranjang'] ?? null, FILTER_VALIDATE_INT, $intOpt);
        $jumlah   = filter_var($in['jumlah'] ?? null, FILTER_VALIDATE_INT, $qtyOpt);
 
        if (!$idDetail || $jumlah === false || $jumlah === null) {
            jsonResponse(400, ['message' => 'Validasi gagal', 'errors' => ['jumlah' => 'Item dan jumlah (1-99) wajib diisi.']]);
        }
 
        Keranjang::setQuantity($userId, $idDetail, $jumlah);
        jsonResponse(200, ['success' => true, 'message' => 'Keranjang diperbarui.', 'data' => $ringkasan()]);
    }
 
    if ($method === 'DELETE') {
        $idDetail = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT, $intOpt);
        if (!$idDetail) {
            jsonResponse(400, ['message' => 'Validasi gagal', 'errors' => ['id' => 'ID item wajib diisi.']]);
        }
 
        Keranjang::removeItem($userId, $idDetail);
        jsonResponse(200, ['success' => true, 'message' => 'Item dihapus.', 'data' => $ringkasan()]);
    }
 
    jsonResponse(405, ['message' => 'Method tidak diizinkan']);
} catch (HttpException $e) {
    jsonResponse($e->status(), ['message' => $e->getMessage(), 'errors' => $e->errors()]);
} catch (PDOException $e) {
    error_log($e->getMessage());
    jsonResponse(500, ['message' => 'Terjadi kesalahan pada server']);
}