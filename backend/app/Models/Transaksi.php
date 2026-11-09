<?php
 
namespace App\Models;
 
use App\Core\Database;
 
final class Transaksi
{
    public static function forUser(int $userId): array
    {
        $stmt = Database::connection()->prepare(
            self::baseSelect() . ' WHERE t.id_pembeli = :id
             ORDER BY t.tgl_transaksi DESC, t.id_transaksi DESC
             LIMIT 50'
        );
        $stmt->execute([':id' => $userId]);
 
        return array_map([self::class, 'enrich'], $stmt->fetchAll());
    }
 
    /** Hanya mengembalikan transaksi milik $userId (mencegah melihat pesanan orang lain). */
    public static function findForUser(int $id, int $userId): ?array
    {
        $stmt = Database::connection()->prepare(
            self::baseSelect() . ' WHERE t.id_transaksi = :id AND t.id_pembeli = :u'
        );
        $stmt->execute([':id' => $id, ':u' => $userId]);
        $row = $stmt->fetch();
 
        return $row ? self::enrich($row) : null;
    }
 
    public static function items(int $orderId): array
    {
        $stmt = Database::connection()->prepare(
            "SELECT dt.id_detail_transaksi AS id,
                    dt.id_transaksi AS order_id,
                    v.id_produk AS product_id,
                    dt.id_varian_produk AS variant_id,
                    CASE WHEN lower(dt.nama_varian_snapshot) = 'reguler'
                         THEN dt.nama_produk_snapshot
                         ELSE CONCAT(dt.nama_produk_snapshot, ' - ', dt.nama_varian_snapshot)
                    END AS product_name,
                    COALESCE(p.gambar, '/img/menu/1.jpg') AS image_path,
                    dt.jumlah AS quantity,
                    dt.harga_satuan AS unit_price,
                    '' AS note
             FROM detail_transaksi dt
             JOIN varian_produk v ON v.id_varian_produk = dt.id_varian_produk
             JOIN produk p ON p.id_produk = v.id_produk
             WHERE dt.id_transaksi = :id
             ORDER BY dt.id_detail_transaksi"
        );
        $stmt->execute([':id' => $orderId]);
        $rows = $stmt->fetchAll();
 
        foreach ($rows as &$row) {
            $row['id']         = (int) $row['id'];
            $row['order_id']   = (int) $row['order_id'];
            $row['product_id'] = (int) $row['product_id'];
            $row['variant_id'] = (int) $row['variant_id'];
            $row['quantity']   = (int) $row['quantity'];
            $row['unit_price'] = (float) $row['unit_price'];
        }
        unset($row);
 
        return $rows;
    }
 
    private static function baseSelect(): string
    {
        return "SELECT t.id_transaksi AS id,
                       t.id_pembeli AS user_id,
                       t.kode_transaksi AS order_code,
                       t.status_transaksi AS status,
                       t.total_harga AS total_amount,
                       COALESCE(t.catatan_pembeli, '') AS note,
                       pp.nama_usaha AS store_name,
                       t.tgl_transaksi AS created_at
                FROM transaksi t
                JOIN profil_penjual pp ON pp.id_profil_penjual = t.id_profil_penjual";
    }
 
    private static function enrich(array $order): array
    {
        $order['id']           = (int) $order['id'];
        $order['user_id']      = (int) $order['user_id'];
        $order['total_amount'] = (float) $order['total_amount'];
 
        $items = self::items($order['id']);
        $order['menu_count'] = count($items);
        $order['item_count'] = 0;
        $thumbs = [];
        foreach ($items as $item) {
            $order['item_count'] += $item['quantity'];
            if ($item['image_path'] !== '') {
                $thumbs[] = $item['image_path'];
            }
        }
        $order['thumbs'] = array_values(array_unique($thumbs));
 
        return $order;
    }
}