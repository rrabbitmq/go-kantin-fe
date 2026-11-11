<?php

namespace App\Models;

use App\Core\Database;

final class Transaksi
{
    public static function forUser(int $userId): array
    {
        $pdo = Database::connection();
        if ($pdo) {
            try {
                $stmt = $pdo->prepare(
                    'SELECT t.id_transaksi AS id,
                            t.id_pembeli AS user_id,
                            t.kode_transaksi AS order_code,
                            t.status_transaksi AS status,
                            t.total_harga AS total_amount,
                            NULL::integer AS eta_minutes,
                            \'Ambil di Kantin\' AS pickup_label,
                            \'Kantin JTI • Lantai 8\' AS pickup_location,
                            t.tgl_transaksi AS created_at
                     FROM transaksi t
                     WHERE t.id_pembeli = ?
                     ORDER BY t.tgl_transaksi DESC, t.id_transaksi DESC'
                );
                $stmt->execute([$userId]);
                return array_map([self::class, 'enrich'], $stmt->fetchAll());
            } catch (\Throwable $e) {
                // fallback
            }
        }

        return array_map([self::class, 'enrich'], self::fallbackOrders($userId));
    }

    public static function summaryForUser(int $userId): array
    {
        $summary = [
            'jumlah_pesanan' => 0,
            'total_belanja' => 0.0,
            'pesanan_terakhir' => null,
        ];

        $pdo = Database::connection();
        if (!$pdo) {
            return $summary;
        }

        try {
            $stmt = $pdo->prepare(
                'SELECT COUNT(*) AS jumlah_pesanan,
                        COALESCE(SUM(total_harga) FILTER (WHERE status_transaksi <> \'dibatalkan\'), 0) AS total_belanja,
                        MAX(tgl_transaksi) AS pesanan_terakhir
                 FROM transaksi
                 WHERE id_pembeli = ?'
            );
            $stmt->execute([$userId]);
            $row = $stmt->fetch();
            if ($row) {
                $summary['jumlah_pesanan'] = (int) $row['jumlah_pesanan'];
                $summary['total_belanja'] = (float) $row['total_belanja'];
                $summary['pesanan_terakhir'] = $row['pesanan_terakhir'];
            }
        } catch (\Throwable $e) {
            // keep zero summary
        }

        return $summary;
    }

    public static function find(int $id): ?array
    {
        $pdo = Database::connection();
        if ($pdo) {
            try {
                $stmt = $pdo->prepare(
                    'SELECT t.id_transaksi AS id,
                            t.id_pembeli AS user_id,
                            t.kode_transaksi AS order_code,
                            t.status_transaksi AS status,
                            t.total_harga AS total_amount,
                            NULL::integer AS eta_minutes,
                            \'Ambil di Kantin\' AS pickup_label,
                            \'Kantin JTI • Lantai 8\' AS pickup_location,
                            t.tgl_transaksi AS created_at
                     FROM transaksi t
                     WHERE t.id_transaksi = ?'
                );
                $stmt->execute([$id]);
                $row = $stmt->fetch();
                if ($row) {
                    return self::enrich($row);
                }
            } catch (\Throwable $e) {
                // fallback
            }
        }

        foreach (self::fallbackOrders(1) as $order) {
            if ((int) $order['id'] === $id) {
                return self::enrich($order);
            }
        }

        return null;
    }

    public static function items(int $orderId): array
    {
        $pdo = Database::connection();
        if ($pdo) {
            try {
                $stmt = $pdo->prepare(
                    'SELECT dt.id_detail_transaksi AS id,
                            dt.id_transaksi AS order_id,
                            dt.id_varian_produk AS product_id,
                            CONCAT(dt.nama_produk_snapshot, \' - \', dt.nama_varian_snapshot) AS product_name,
                            COALESCE(p.gambar, \'/img/menus/oreo-soft-cake.jpg\') AS image_path,
                            dt.jumlah AS quantity,
                            dt.harga_satuan AS unit_price,
                            \'\' AS note
                     FROM detail_transaksi dt
                     LEFT JOIN varian_produk v ON v.id_varian_produk = dt.id_varian_produk
                     LEFT JOIN produk p ON p.id_produk = v.id_produk
                     WHERE dt.id_transaksi = ?
                     ORDER BY dt.id_detail_transaksi'
                );
                $stmt->execute([$orderId]);
                $rows = $stmt->fetchAll();
                if ($rows) {
                    foreach ($rows as &$row) {
                        $row['id'] = (int) $row['id'];
                        $row['quantity'] = (int) $row['quantity'];
                        $row['unit_price'] = (float) $row['unit_price'];
                    }
                    unset($row);
                    return $rows;
                }
            } catch (\Throwable $e) {
                // fallback
            }
        }

        return self::fallbackItems($orderId);
    }

    private static function enrich(array $order): array
    {
        $order['id'] = (int) $order['id'];
        $order['total_amount'] = (float) $order['total_amount'];
        $order['eta_minutes'] = isset($order['eta_minutes']) ? (int) $order['eta_minutes'] : null;
        $order['status'] = gk_normalize_order_status((string) $order['status']);

        if (!isset($order['menu_count']) || !isset($order['item_count']) || !isset($order['thumbs'])) {
            $items = self::items($order['id']);
            $order['menu_count'] = count($items);
            $order['item_count'] = 0;
            $thumbs = [];
            foreach ($items as $item) {
                $order['item_count'] += (int) $item['quantity'];
                if (!empty($item['image_path'])) {
                    $thumbs[] = $item['image_path'];
                }
            }
            $order['thumbs'] = array_values(array_unique($thumbs));
        }

        return $order;
    }

    private static function fallbackOrders(int $userId): array
    {
        if ($userId !== 1 && $userId > 0) {
            // still show demo history for any authenticated demo buyer during UI migration
        }

        return [
            [
                'id' => 1,
                'user_id' => $userId,
                'order_code' => 'ABC-123-XYZ',
                'status' => 'processing',
                'total_amount' => 11000,
                'eta_minutes' => 15,
                'pickup_label' => 'Ambil di Kantin',
                'pickup_location' => 'Kantin JTI • Lantai 8',
                'created_at' => '2026-10-20 11:42:00',
                'menu_count' => 2,
                'item_count' => 3,
                'thumbs' => ['/img/menus/oreo-soft-cake.jpg', '/img/menus/kapal-api.jpg'],
            ],
            [
                'id' => 2,
                'user_id' => $userId,
                'order_code' => 'GK-884-21A',
                'status' => 'ready',
                'total_amount' => 15000,
                'eta_minutes' => 5,
                'pickup_label' => 'Ambil di Kantin',
                'pickup_location' => 'Kantin JTI • Lantai 8',
                'created_at' => '2026-10-20 10:15:00',
                'menu_count' => 1,
                'item_count' => 1,
                'thumbs' => ['/img/menus/nasi-ayam-geprek.jpg'],
            ],
            [
                'id' => 3,
                'user_id' => $userId,
                'order_code' => 'GK-771-09C',
                'status' => 'done',
                'total_amount' => 30500,
                'eta_minutes' => null,
                'pickup_label' => 'Ambil di Kantin',
                'pickup_location' => 'Kantin JTI • Lantai 8',
                'created_at' => '2026-10-19 12:08:00',
                'menu_count' => 3,
                'item_count' => 4,
                'thumbs' => ['/img/menus/ayam-krispi.jpg', '/img/menus/risol-mayo.jpg', '/img/menus/air-mineral-club.jpg'],
            ],
            [
                'id' => 4,
                'user_id' => $userId,
                'order_code' => 'GK-650-44B',
                'status' => 'done',
                'total_amount' => 8000,
                'eta_minutes' => null,
                'pickup_label' => 'Ambil di Kantin',
                'pickup_location' => 'Kantin JTI • Lantai 8',
                'created_at' => '2026-10-18 13:30:00',
                'menu_count' => 1,
                'item_count' => 2,
                'thumbs' => ['/img/menus/teh-pucuk.png'],
            ],
        ];
    }

    private static function fallbackItems(int $orderId): array
    {
        $map = [
            1 => [
                ['id' => 1, 'order_id' => 1, 'product_id' => 1, 'product_name' => 'Oreo Soft Cake', 'image_path' => '/img/menus/oreo-soft-cake.jpg', 'quantity' => 1, 'unit_price' => 3000, 'note' => ''],
                ['id' => 2, 'order_id' => 1, 'product_id' => 4, 'product_name' => 'Kapal Api', 'image_path' => '/img/menus/kapal-api.jpg', 'quantity' => 2, 'unit_price' => 4000, 'note' => 'satu panas, satu dingin'],
            ],
            2 => [
                ['id' => 3, 'order_id' => 2, 'product_id' => 9, 'product_name' => 'Nasi Ayam Geprek', 'image_path' => '/img/menus/nasi-ayam-geprek.jpg', 'quantity' => 1, 'unit_price' => 15000, 'note' => ''],
            ],
            3 => [
                ['id' => 4, 'order_id' => 3, 'product_id' => 2, 'product_name' => 'Ayam Krispi', 'image_path' => '/img/menus/ayam-krispi.jpg', 'quantity' => 2, 'unit_price' => 12000, 'note' => ''],
                ['id' => 5, 'order_id' => 3, 'product_id' => 12, 'product_name' => 'Risol Mayo', 'image_path' => '/img/menus/risol-mayo.jpg', 'quantity' => 1, 'unit_price' => 3500, 'note' => ''],
                ['id' => 6, 'order_id' => 3, 'product_id' => 3, 'product_name' => 'Air Mineral Club', 'image_path' => '/img/menus/air-mineral-club.jpg', 'quantity' => 1, 'unit_price' => 3000, 'note' => ''],
            ],
            4 => [
                ['id' => 7, 'order_id' => 4, 'product_id' => 6, 'product_name' => 'Teh Pucuk', 'image_path' => '/img/menus/teh-pucuk.png', 'quantity' => 2, 'unit_price' => 4000, 'note' => ''],
            ],
        ];

        return $map[$orderId] ?? [];
    }
}
