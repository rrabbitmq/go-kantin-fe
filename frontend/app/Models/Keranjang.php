<?php
 
namespace App\Models;
 
use App\Core\ApiClient;
use App\Core\Auth;
 
final class Keranjang {
    
    public static function summary(): array {
        $empty = ['items' => [], 'jumlah' => 0, 'subtotal' => 0.0, 'biaya_layanan' => 0.0];
 
        $token = Auth::token();
        if (!$token) {
            return $empty;
        }
 
        $res = ApiClient::get('/keranjang', $token);
        if (!$res['ok'] ??  false|| !is_array($res['data'] ?? null)) {
            return $empty;
        }
 
        $data = $res['data'];
 
        $items = array_map(static function (array $row): array {
            $row['id']           = (int) ($row['id'] ?? $row['id_detail_keranjang'] ?? 0);
            $row['product_id']   = (int) ($row['product_id'] ?? $row['id_produk'] ?? 0);
            $row['variant_id']   = (int) ($row['variant_id'] ?? $row['id_varian_produk'] ?? 0);
            $row['quantity']     = (int) ($row['quantity'] ?? $row['jumlah'] ?? 0);
            $row['stock']        = (int) ($row['stock'] ?? $row['stok'] ?? 0);
            $row['price']        = (float) ($row['price'] ?? $row['harga_satuan'] ?? 0);
            $row['line_total']   = (float) ($row['line_total'] ?? ($row['quantity'] * $row['price']));
            return $row;
        }, $data['items'] ?? []);
 
        return [
            'items' => $items,
            'jumlah' => (int) ($data['jumlah'] ?? 0),
            'subtotal' => (float) ($data['subtotal'] ?? 0),
            'biaya_layanan' => (float) ($data['biaya_layanan'] ?? 0),
        ];
    }
 
    public static function itemsForUser(int $userId): array {
        return self::summary()['items'];
    }
 
    public static function countForUser(int $userId): int {
        $token = Auth::token();
        if (!$token) {
            return 0;
        }
 
        $res = ApiClient::get('/keranjang/jumlah', $token);
        return $res['ok'] ? (int) ($res['data']['jumlah'] ?? 0) : 0;
    }
 
    /** Tambah produk ke keranjang. Hasil: ['ok'=>bool, 'message'=>..., 'errors'=>[...], 'data'=>...] */
    public static function add(int $productId, int $quantity = 1, ?int $variantId = null): array {

        $body = ['jumlah'    => $quantity];
        if ($variantId) {
            $body['id_varian_produk'] = $variantId;
        }else {
            $body['id_produk'] = $productId;
        }
        return ApiClient::post('/keranjang', $body, Auth::token());
    }
 
    /** $detailId = kolom 'id' dari item keranjang */
    public static function updateQuantity(int $detailId, int $quantity): array {
        return ApiClient::request('PUT', '/keranjang', [
            'id_detail_keranjang' => $detailId,
            'jumlah' => $quantity,
        ], Auth::token());
    }
 
    public static function remove(int $detailId): array {
        return ApiClient::request('DELETE', '/keranjang?id=' . $detailId, [], Auth::token());
    }
}