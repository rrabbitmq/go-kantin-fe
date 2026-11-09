<?php
 
namespace App\Models;
 
use App\Core\Database;
use App\Core\HttpException;
use PDO;
 
final class Keranjang
{
    /** Ambil id keranjang milik user; dibuat otomatis kalau belum ada. */
    public static function idForUser(PDO $pdo, int $userId): int
    {
        $stmt = $pdo->prepare(
            'INSERT INTO keranjang (id_user) VALUES (:id)
             ON CONFLICT (id_user) DO UPDATE SET updated_at = CURRENT_TIMESTAMP
             RETURNING id_keranjang'
        );
        $stmt->execute([':id' => $userId]);
 
        return (int) $stmt->fetchColumn();
    }
 
    public static function itemsForUser(int $userId): array
    {
        $stmt = Database::connection()->prepare(
            "SELECT dk.id_detail_keranjang AS id,
                    k.id_user AS user_id,
                    p.id_produk AS product_id,
                    dk.id_varian_produk AS variant_id,
                    p.id_profil_penjual AS seller_id,
                    dk.jumlah AS quantity,
                    '' AS note,
                    p.nama_produk AS name,
                    v.nama_varian AS variant_name,
                    v.harga_satuan AS price,
                    v.stok AS stock,
                    COALESCE(p.gambar, '/img/menu/1.jpg') AS image_path,
                    COALESCE(p.deskripsi, '') AS description,
                    (v.harga_satuan * dk.jumlah) AS line_total
             FROM keranjang k
             JOIN detail_keranjang dk ON dk.id_keranjang = k.id_keranjang
             JOIN varian_produk v ON v.id_varian_produk = dk.id_varian_produk
             JOIN produk p ON p.id_produk = v.id_produk
             WHERE k.id_user = :id
             ORDER BY dk.id_detail_keranjang"
        );
        $stmt->execute([':id' => $userId]);
        $rows = $stmt->fetchAll();
 
        foreach ($rows as &$row) {
            $row['id']         = (int) $row['id'];
            $row['user_id']    = (int) $row['user_id'];
            $row['product_id'] = (int) $row['product_id'];
            $row['variant_id'] = (int) $row['variant_id'];
            $row['seller_id']  = (int) $row['seller_id'];
            $row['quantity']   = (int) $row['quantity'];
            $row['price']      = (float) $row['price'];
            $row['stock']      = (int) $row['stock'];
            $row['line_total'] = (float) $row['line_total'];
        }
        unset($row);
 
        return $rows;
    }
 
    public static function countForUser(int $userId): int
    {
        $stmt = Database::connection()->prepare(
            'SELECT COALESCE(SUM(dk.jumlah), 0)
             FROM keranjang k
             JOIN detail_keranjang dk ON dk.id_keranjang = k.id_keranjang
             WHERE k.id_user = :id'
        );
        $stmt->execute([':id' => $userId]);
 
        return (int) $stmt->fetchColumn();
    }
 
    /**
     * Tambah ke keranjang. Kirim $variantId, atau $productId (varian aktif
     * termurah yang stoknya ada akan dipilih otomatis).
     */
    public static function addItem(int $userId, ?int $variantId, ?int $productId, int $quantity): void
    {
        $pdo = Database::connection();
 
        if ($variantId === null) {
            $stmt = $pdo->prepare(
                "SELECT v.id_varian_produk
                 FROM varian_produk v
                 JOIN produk p ON p.id_produk = v.id_produk
                 WHERE p.id_produk = :id
                   AND p.status_produk = 'aktif'
                   AND v.status_aktif = TRUE
                   AND v.stok > 0
                 ORDER BY v.harga_satuan, v.id_varian_produk
                 LIMIT 1"
            );
            $stmt->execute([':id' => $productId]);
            $found = $stmt->fetchColumn();
            if (!$found) {
                throw new HttpException(404, 'Produk tidak tersedia atau stok habis.');
            }
            $variantId = (int) $found;
        }
 
        $stmt = $pdo->prepare(
            "SELECT v.stok, p.nama_produk
             FROM varian_produk v
             JOIN produk p ON p.id_produk = v.id_produk
             WHERE v.id_varian_produk = :id
               AND v.status_aktif = TRUE
               AND p.status_produk = 'aktif'"
        );
        $stmt->execute([':id' => $variantId]);
        $varian = $stmt->fetch();
        if (!$varian) {
            throw new HttpException(404, 'Produk tidak tersedia.');
        }
 
        $idKeranjang = self::idForUser($pdo, $userId);
 
        $stmt = $pdo->prepare(
            'SELECT jumlah FROM detail_keranjang
             WHERE id_keranjang = :k AND id_varian_produk = :v'
        );
        $stmt->execute([':k' => $idKeranjang, ':v' => $variantId]);
        $baru = (int) $stmt->fetchColumn() + $quantity;
 
        if ($baru > (int) $varian['stok']) {
            throw new HttpException(409, 'Stok ' . $varian['nama_produk'] . ' tidak mencukupi (tersisa ' . (int) $varian['stok'] . ').');
        }
 
        $pdo->prepare(
            'INSERT INTO detail_keranjang (id_keranjang, id_varian_produk, jumlah)
             VALUES (:k, :v, :j)
             ON CONFLICT (id_keranjang, id_varian_produk) DO UPDATE SET jumlah = EXCLUDED.jumlah'
        )->execute([':k' => $idKeranjang, ':v' => $variantId, ':j' => $baru]);
    }
 
    public static function setQuantity(int $userId, int $detailId, int $quantity): void
    {
        $pdo = Database::connection();
 
        $stmt = $pdo->prepare(
            'SELECT v.stok, p.nama_produk
             FROM detail_keranjang dk
             JOIN keranjang k ON k.id_keranjang = dk.id_keranjang
             JOIN varian_produk v ON v.id_varian_produk = dk.id_varian_produk
             JOIN produk p ON p.id_produk = v.id_produk
             WHERE dk.id_detail_keranjang = :d AND k.id_user = :u'
        );
        $stmt->execute([':d' => $detailId, ':u' => $userId]);
        $row = $stmt->fetch();
        if (!$row) {
            throw new HttpException(404, 'Item keranjang tidak ditemukan.');
        }
        if ($quantity > (int) $row['stok']) {
            throw new HttpException(409, 'Stok ' . $row['nama_produk'] . ' tidak mencukupi (tersisa ' . (int) $row['stok'] . ').');
        }
 
        $pdo->prepare('UPDATE detail_keranjang SET jumlah = :j WHERE id_detail_keranjang = :d')
            ->execute([':j' => $quantity, ':d' => $detailId]);
    }
 
    public static function removeItem(int $userId, int $detailId): void
    {
        $stmt = Database::connection()->prepare(
            'DELETE FROM detail_keranjang dk
             USING keranjang k
             WHERE dk.id_keranjang = k.id_keranjang
               AND k.id_user = :u
               AND dk.id_detail_keranjang = :d'
        );
        $stmt->execute([':u' => $userId, ':d' => $detailId]);
 
        if ($stmt->rowCount() === 0) {
            throw new HttpException(404, 'Item keranjang tidak ditemukan.');
        }
    }
}