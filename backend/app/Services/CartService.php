<?php
namespace App\Services;

use App\Core\Database;
use Exception;
use PDO;

final class CartService
{
    private PDO $db;

    public function __construct()
    {
        // Memanggil method connection() sesuai milik Anda
        $this->db = Database::connection();
    }

    // Mengambil daftar isi keranjang pengguna
    public function list(int $userId): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    dk.id_detail_keranjang AS id,
                    dk.id_varian_produk,
                    dk.jumlah,
                    vp.nama_varian,
                    vp.harga_satuan,
                    vp.stok AS stok_tersedia,
                    p.id_produk,
                    p.nama_produk,
                    p.gambar,
                    (dk.jumlah * vp.harga_satuan) AS line_total
                FROM detail_keranjang dk
                JOIN keranjang k ON k.id_keranjang = dk.id_keranjang
                JOIN varian_produk vp ON vp.id_varian_produk = dk.id_varian_produk
                JOIN produk p ON p.id_produk = vp.id_produk
                WHERE k.id_user = ?
                ORDER BY dk.id_detail_keranjang DESC
            ");
            $stmt->execute([$userId]);
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $subtotal = array_sum(array_column($items, 'line_total'));

            return [
                'status' => 200,
                'ok'     => true,
                'data'   => [
                    'items'    => $items,
                    'subtotal' => (float) $subtotal
                ]
            ];
        } catch (Exception $e) {
            return ['status' => 500, 'ok' => false, 'message' => 'Gagal mengambil data keranjang: ' . $e->getMessage()];
        }
    }

    // Menambah item ke keranjang dan menguraikan stok varian_produk
    public function add(int $userId, int $idVarianProduk, int $jumlah): array
    {
        if ($idVarianProduk <= 0 || $jumlah <= 0) {
            return ['status' => 400, 'ok' => false, 'message' => 'Varian produk atau jumlah tidak valid.'];
        }

        try {
            $this->db->beginTransaction();

            // 1. Ambil atau buat keranjang user
            $stmtK = $this->db->prepare("SELECT id_keranjang FROM keranjang WHERE id_user = ?");
            $stmtK->execute([$userId]);
            $idKeranjang = $stmtK->fetchColumn();

            if (!$idKeranjang) {
                $stmtNew = $this->db->prepare("INSERT INTO keranjang (id_user) VALUES (?) RETURNING id_keranjang");
                $stmtNew->execute([$userId]);
                $idKeranjang = $stmtNew->fetchColumn();
            }

            // 2. Lock stok pada tabel varian_produk (FOR UPDATE)
            $stmtVarian = $this->db->prepare("SELECT stok FROM varian_produk WHERE id_varian_produk = ? FOR UPDATE");
            $stmtVarian->execute([$idVarianProduk]);
            $varian = $stmtVarian->fetch(PDO::FETCH_ASSOC);

            if (!$varian) {
                $this->db->rollBack();
                return ['status' => 404, 'ok' => false, 'message' => 'Varian produk tidak ditemukan.'];
            }

            if ((int)$varian['stok'] < $jumlah) {
                $this->db->rollBack();
                return ['status' => 400, 'ok' => false, 'message' => 'Stok tidak mencukupi. Sisa stok: ' . $varian['stok']];
            }

            // 3. Kurangi stok varian secara otomatis
            $stmtUpdateStok = $this->db->prepare("UPDATE varian_produk SET stok = stok - ? WHERE id_varian_produk = ?");
            $stmtUpdateStok->execute([$jumlah, $idVarianProduk]);

            // 4. Tambah/update jumlah di detail_keranjang
            $stmtDetail = $this->db->prepare("
                INSERT INTO detail_keranjang (id_keranjang, id_varian_produk, jumlah)
                VALUES (?, ?, ?)
                ON CONFLICT (id_keranjang, id_varian_produk)
                DO UPDATE SET jumlah = detail_keranjang.jumlah + EXCLUDED.jumlah
            ");
            $stmtDetail->execute([$idKeranjang, $idVarianProduk, $jumlah]);

            $this->db->commit();
            return ['status' => 200, 'ok' => true, 'message' => 'Item berhasil ditambahkan ke keranjang.'];

        } catch (Exception $e) {
            $this->db->rollBack();
            return ['status' => 500, 'ok' => false, 'message' => 'Gagal memproses item: ' . $e->getMessage()];
        }
    }

    // Mengubah jumlah kuantitas item dan memperbarui selisih stok
    public function update(int $userId, int $idDetailKeranjang, int $jumlahBaru): array
    {
        if ($jumlahBaru <= 0) {
            return $this->remove($userId, $idDetailKeranjang);
        }

        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("
                SELECT dk.id_detail_keranjang, dk.id_varian_produk, dk.jumlah, vp.stok 
                FROM detail_keranjang dk
                JOIN keranjang k ON k.id_keranjang = dk.id_keranjang
                JOIN varian_produk vp ON vp.id_varian_produk = dk.id_varian_produk
                WHERE dk.id_detail_keranjang = ? AND k.id_user = ?
                FOR UPDATE
            ");
            $stmt->execute([$idDetailKeranjang, $userId]);
            $item = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$item) {
                $this->db->rollBack();
                return ['status' => 404, 'ok' => false, 'message' => 'Item keranjang tidak ditemukan.'];
            }

            $selisih = $jumlahBaru - (int)$item['jumlah'];

            if ($selisih > 0) {
                if ((int)$item['stok'] < $selisih) {
                    $this->db->rollBack();
                    return ['status' => 400, 'ok' => false, 'message' => 'Stok tidak mencukupi untuk penambahan ini.'];
                }
                $stK = $this->db->prepare("UPDATE varian_produk SET stok = stok - ? WHERE id_varian_produk = ?");
                $stK->execute([$selisih, $item['id_varian_produk']]);
            } elseif ($selisih < 0) {
                $stT = $this->db->prepare("UPDATE varian_produk SET stok = stok + ? WHERE id_varian_produk = ?");
                $stT->execute([abs($selisih), $item['id_varian_produk']]);
            }

            $stUp = $this->db->prepare("UPDATE detail_keranjang SET jumlah = ? WHERE id_detail_keranjang = ?");
            $stUp->execute([$jumlahBaru, $idDetailKeranjang]);

            $this->db->commit();
            return ['status' => 200, 'ok' => true, 'message' => 'Jumlah keranjang berhasil diperbarui.'];

        } catch (Exception $e) {
            $this->db->rollBack();
            return ['status' => 500, 'ok' => false, 'message' => 'Gagal memperbarui item: ' . $e->getMessage()];
        }
    }

    // Menghapus item dan mengembalikan stok penuh ke varian_produk
    public function remove(int $userId, int $idDetailKeranjang): array
    {
        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("
                SELECT dk.id_detail_keranjang, dk.id_varian_produk, dk.jumlah 
                FROM detail_keranjang dk
                JOIN keranjang k ON k.id_keranjang = dk.id_keranjang
                WHERE dk.id_detail_keranjang = ? AND k.id_user = ?
                FOR UPDATE
            ");
            $stmt->execute([$idDetailKeranjang, $userId]);
            $item = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$item) {
                $this->db->rollBack();
                return ['status' => 404, 'ok' => false, 'message' => 'Item keranjang tidak ditemukan.'];
            }

            $stmtRestore = $this->db->prepare("UPDATE varian_produk SET stok = stok + ? WHERE id_varian_produk = ?");
            $stmtRestore->execute([$item['jumlah'], $item['id_varian_produk']]);

            $stmtDelete = $this->db->prepare("DELETE FROM detail_keranjang WHERE id_detail_keranjang = ?");
            $stmtDelete->execute([$idDetailKeranjang]);

            $this->db->commit();
            return ['status' => 200, 'ok' => true, 'message' => 'Item dihapus dan stok dikembalikan.'];

        } catch (Exception $e) {
            $this->db->rollBack();
            return ['status' => 500, 'ok' => false, 'message' => 'Gagal menghapus item: ' . $e->getMessage()];
        }
    }
}