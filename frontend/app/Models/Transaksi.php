<?php
 
namespace App\Models;
 
use App\Core\ApiClient;
use App\Core\Auth;
 
final class Transaksi {
    public static function forUser(int $userId): array {
        $res = ApiClient::get('/transaksi', Auth::token());
        if (!$res['ok'] || !is_array($res['data'])) {
            return [];
        }
 
        return array_map([self::class, 'enrich'], $res['data']);
    }

    /** Ringkasan profil: jumlah pesanan, total belanja, pesanan terakhir. */
    public static function summaryForUser(int $userId): array {
        $orders = self::forUser($userId);
        $jumlah = count($orders);
        $total = 0.0;
        $terakhir = null;

        foreach ($orders as $order) {
            if (($order['status'] ?? '') === 'done' || ($order['status'] ?? '') === 'selesai') {
                $total += (float) ($order['total_amount'] ?? 0);
            } else {
                $total += (float) ($order['total_amount'] ?? 0);
            }
            $waktu = $order['created_at'] ?? $order['tanggal'] ?? $order['tgl_transaksi'] ?? null;
            if ($waktu && ($terakhir === null || strcmp((string) $waktu, (string) $terakhir) > 0)) {
                $terakhir = $waktu;
            }
        }

        return [
            'jumlah_pesanan' => $jumlah,
            'total_belanja' => $total,
            'pesanan_terakhir' => $terakhir,
        ];
    }
 
    public static function find(int $id): ?array {
        $detail = self::detail($id);
 
        return $detail ? self::enrich($detail['order']) : null;
    }
 
    public static function items(int $orderId): array {
        $detail = self::detail($orderId);
        if (!$detail) {
            return [];
        }
 
        return array_map(static function (array $row): array {
            $row['id'] = (int) $row['id'];
            $row['quantity'] = (int) $row['quantity'];
            $row['unit_price'] = (float) $row['unit_price'];
            return $row;
        }, $detail['items'] ?? []);
    }
 
    /** Buat pesanan dari isi keranjang. Hasil: ['ok'=>bool, 'message'=>..., 'data'=>['orders'=>[...]]] */
    public static function checkout(string $note = '', string $metode = 'tunai'): array {
        return ApiClient::post('/checkout', ['catatan' => $note, 'metode_bayar' => $metode], Auth::token());
    }
 
    private static function detail(int $id): ?array {
        static $cache = [];
 
        if (!array_key_exists($id, $cache)) {
            $res = ApiClient::get('/transaksi?id=' . $id, Auth::token());
            $cache[$id] = ($res['ok'] && is_array($res['data'])) ? $res['data'] : null;
        }
 
        return $cache[$id];
    }
 
    private static function enrich(array $order): array {
        $order['id'] = (int) $order['id'];
        $order['total_amount'] = (float) $order['total_amount'];
        $order['eta_minutes'] = isset($order['eta_minutes']) ? (int) $order['eta_minutes'] : null;
        $order['status'] = gk_normalize_order_status((string) $order['status']);
        $order['menu_count'] = (int) ($order['menu_count'] ?? 0);
        $order['item_count'] = (int) ($order['item_count'] ?? 0);
        $order['thumbs'] = $order['thumbs'] ?? [];
        $order['pickup_label'] = $order['pickup_label'] ?? 'Ambil di Kantin';
        $order['pickup_location'] = $order['pickup_location'] ?? 'Kantin JTI • Lantai 8';
 
        return $order;
    }
}