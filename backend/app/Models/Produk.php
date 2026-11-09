<?php
 
namespace App\Models;
 
use App\Core\Database;
 
final class Produk
{
    public static function all(?int $categoryId = null, bool $terlaris = false, int $limit = 50): array
    {
        $where  = [];
        $params = [];
        if ($categoryId) {
            $where[] = 'p.id_kategori_produk = :kategori';
            $params[':kategori'] = $categoryId;
        }
 
        return self::query($where, $params, $terlaris, $limit);
    }
 
    public static function find(int $id): ?array
    {
        $rows = self::query(['p.id_produk = :id'], [':id' => $id], false, 1);
        if (!$rows) {
            return null;
        }
 
        $product = $rows[0];
        $product['variants'] = self::variants($id);
 
        return $product;
    }
 
    public static function variants(int $productId): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT id_varian_produk AS id, nama_varian AS name, harga_satuan AS price, stok AS stock
             FROM varian_produk
             WHERE id_produk = :id AND status_aktif = TRUE
             ORDER BY harga_satuan, id_varian_produk'
        );
        $stmt->execute([':id' => $productId]);
        $rows = $stmt->fetchAll();
 
        foreach ($rows as &$row) {
            $row['id']    = (int) $row['id'];
            $row['price'] = (float) $row['price'];
            $row['stock'] = (int) $row['stock'];
        }
        unset($row);
 
        return $rows;
    }
 
    private static function query(array $extraWhere, array $params, bool $terlaris, int $limit): array
    {
        $where = array_merge(["p.status_produk = 'aktif'", 'k.status_aktif = TRUE'], $extraWhere);
        $order = $terlaris ? 'COALESCE(s.terjual, 0) DESC, p.id_produk' : 'p.id_produk';
 
        $sql = "SELECT p.id_produk AS id,
                       p.id_kategori_produk AS category_id,
                       p.nama_produk AS name,
                       COALESCE(p.deskripsi, '') AS description,
                       COALESCE(p.gambar, '/img/menu/1.jpg') AS image_path,
                       COALESCE(MIN(v.harga_satuan), 0) AS price,
                       k.nama_kategori AS category_name,
                       lower(replace(k.nama_kategori, ' ', '-')) AS category_slug,
                       COALESCE(s.terjual, 0) AS terjual
                FROM produk p
                JOIN kategori_produk k ON k.id_kategori_produk = p.id_kategori_produk
                LEFT JOIN varian_produk v ON v.id_produk = p.id_produk AND v.status_aktif = TRUE
                LEFT JOIN (
                    SELECT vp.id_produk, SUM(dt.jumlah) AS terjual
                    FROM detail_transaksi dt
                    JOIN transaksi t ON t.id_transaksi = dt.id_transaksi
                         AND t.status_transaksi IN ('dibayar', 'diproses', 'siap_diambil', 'selesai')
                    JOIN varian_produk vp ON vp.id_varian_produk = dt.id_varian_produk
                    GROUP BY vp.id_produk
                ) s ON s.id_produk = p.id_produk
                WHERE " . implode(' AND ', $where) . "
                GROUP BY p.id_produk, k.nama_kategori, s.terjual
                ORDER BY $order
                LIMIT " . max(1, $limit);
 
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();
 
        foreach ($rows as &$row) {
            $row['id']          = (int) $row['id'];
            $row['category_id'] = (int) $row['category_id'];
            $row['price']       = (float) $row['price'];
            $row['terjual']     = (int) $row['terjual'];
        }
        unset($row);
 
        return $rows;
    }
}