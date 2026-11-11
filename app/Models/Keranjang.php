<?php

namespace App\Models;

use App\Core\Database;

final class Keranjang
{
    public static function itemsForUser(int $userId): array
    {
        $pdo = Database::connection();
        if ($pdo) {
            try {
                $stmt = $pdo->prepare(
                    'SELECT dk.id_detail_keranjang AS id,
                            k.id_user AS user_id,
                            dk.id_varian_produk AS product_id,
                            dk.jumlah AS quantity,
                            \'\' AS note,
                            p.nama_produk AS name,
                            v.harga_satuan AS price,
                            COALESCE(p.gambar, \'/img/menus/oreo-soft-cake.jpg\') AS image_path,
                            COALESCE(p.deskripsi, \'\') AS description,
                            (v.harga_satuan * dk.jumlah) AS line_total
                     FROM keranjang k
                     JOIN detail_keranjang dk ON dk.id_keranjang = k.id_keranjang
                     JOIN varian_produk v ON v.id_varian_produk = dk.id_varian_produk
                     JOIN produk p ON p.id_produk = v.id_produk
                     WHERE k.id_user = ?
                     ORDER BY dk.id_detail_keranjang'
                );
                $stmt->execute([$userId]);
                $rows = $stmt->fetchAll();
                foreach ($rows as &$row) {
                    $row['id'] = (int) $row['id'];
                    $row['product_id'] = (int) $row['product_id'];
                    $row['quantity'] = (int) $row['quantity'];
                    $row['price'] = (float) $row['price'];
                    $row['line_total'] = (float) $row['line_total'];
                }
                unset($row);
                return $rows;
            } catch (\Throwable $e) {
                // fallback
            }
        }

        return self::fallback($userId);
    }

    public static function countForUser(int $userId): int
    {
        $count = 0;
        foreach (self::itemsForUser($userId) as $item) {
            $count += (int) $item['quantity'];
        }

        return $count;
    }

    private static function fallback(int $userId): array
    {
        $products = [];
        foreach (Produk::all() as $p) {
            $products[$p['id']] = $p;
        }

        $items = [
            ['id' => 1, 'product_id' => 1, 'quantity' => 1, 'note' => ''],
            ['id' => 2, 'product_id' => 2, 'quantity' => 1, 'note' => ''],
            ['id' => 3, 'product_id' => 4, 'quantity' => 1, 'note' => ''],
        ];

        $out = [];
        foreach ($items as $item) {
            if (!isset($products[$item['product_id']])) {
                continue;
            }
            $p = $products[$item['product_id']];
            $out[] = [
                'id' => $item['id'],
                'user_id' => $userId,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'note' => $item['note'],
                'name' => $p['name'],
                'price' => $p['price'],
                'image_path' => $p['image_path'],
                'description' => $p['description'],
                'line_total' => $p['price'] * $item['quantity'],
            ];
        }

        return $out;
    }
}
