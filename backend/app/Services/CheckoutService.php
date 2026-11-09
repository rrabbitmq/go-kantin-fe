<?php
 
namespace App\Services;
 
use App\Core\Database;
use App\Core\HttpException;
use App\Models\Keranjang;
 
/**
 * Membuat pesanan dari isi keranjang.
 * - Satu transaksi hanya untuk satu penjual, jadi keranjang dipecah per penjual.
 * - Stok dikurangi saat checkout (dan dicatat di stok_log).
 * - Status awal: menunggu_pembayaran.
 * Semua langkah berjalan dalam satu transaksi database (gagal satu = batal semua).
 */
final class CheckoutService
{
    public const BIAYA_LAYANAN = 0;
 
    public static function checkout(int $userId, string $catatan = ''): array
    {
        $pdo = Database::connection();
        $pdo->beginTransaction();
 
        try {
            $idKeranjang = Keranjang::idForUser($pdo, $userId);
 
            $stmt = $pdo->prepare(
                "SELECT dk.id_varian_produk, dk.jumlah,
                        v.nama_varian, v.harga_satuan,
                        p.nama_produk, p.id_profil_penjual,
                        CASE WHEN v.status_aktif AND p.status_produk = 'aktif' THEN 1 ELSE 0 END AS tersedia
                 FROM detail_keranjang dk
                 JOIN varian_produk v ON v.id_varian_produk = dk.id_varian_produk
                 JOIN produk p ON p.id_produk = v.id_produk
                 WHERE dk.id_keranjang = :k
                 ORDER BY p.id_profil_penjual, dk.id_detail_keranjang"
            );
            $stmt->execute([':k' => $idKeranjang]);
            $items = $stmt->fetchAll();
 
            if (!$items) {
                throw new HttpException(400, 'Keranjang masih kosong.');
            }
 
            $perPenjual = [];
            foreach ($items as $item) {
                if ((int) $item['tersedia'] !== 1) {
                    throw new HttpException(409, $item['nama_produk'] . ' sedang tidak tersedia. Hapus dari keranjang lalu coba lagi.');
                }
                $perPenjual[(int) $item['id_profil_penjual']][] = $item;
            }
 
            $hasil = [];
 
            foreach ($perPenjual as $idPenjual => $rows) {
                $totalProduk = 0;
                $subtotal = 0.0;
                foreach ($rows as $r) {
                    $totalProduk += (int) $r['jumlah'];
                    $subtotal += (float) $r['harga_satuan'] * (int) $r['jumlah'];
                }
                $biaya = (float) self::BIAYA_LAYANAN;
                $total = $subtotal + $biaya;
                $kode  = 'GK-' . date('ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
 
                $stmt = $pdo->prepare(
                    'INSERT INTO transaksi
                        (kode_transaksi, id_pembeli, id_profil_penjual, total_produk,
                         subtotal, biaya_layanan, total_harga, catatan_pembeli)
                     VALUES (:kode, :pembeli, :penjual, :total_produk,
                             :subtotal, :biaya, :total, :catatan)
                     RETURNING id_transaksi'
                );
                $stmt->execute([
                    ':kode'         => $kode,
                    ':pembeli'      => $userId,
                    ':penjual'      => $idPenjual,
                    ':total_produk' => $totalProduk,
                    ':subtotal'     => $subtotal,
                    ':biaya'        => $biaya,
                    ':total'        => $total,
                    ':catatan'      => $catatan !== '' ? $catatan : null,
                ]);
                $idTransaksi = (int) $stmt->fetchColumn();
 
                foreach ($rows as $r) {
                    $n = (int) $r['jumlah'];
                    $idVarian = (int) $r['id_varian_produk'];
 
                    // Kurangi stok secara atomik; gagal kalau stok tidak cukup
                    $upd = $pdo->prepare(
                        'UPDATE varian_produk SET stok = stok - :n
                         WHERE id_varian_produk = :v AND stok >= :min
                         RETURNING stok'
                    );
                    $upd->execute([':n' => $n, ':v' => $idVarian, ':min' => $n]);
                    $sesudah = $upd->fetchColumn();
                    if ($sesudah === false) {
                        throw new HttpException(409, 'Stok ' . $r['nama_produk'] . ' tidak mencukupi.');
                    }
                    $sesudah = (int) $sesudah;
 
                    $pdo->prepare(
                        "INSERT INTO stok_log
                            (id_varian_produk, id_user, jenis_perubahan, jumlah_perubahan,
                             stok_sebelum, stok_sesudah, keterangan)
                         VALUES (:v, :u, 'keluar', :perubahan, :sebelum, :sesudah, :ket)"
                    )->execute([
                        ':v'         => $idVarian,
                        ':u'         => $userId,
                        ':perubahan' => -$n,
                        ':sebelum'   => $sesudah + $n,
                        ':sesudah'   => $sesudah,
                        ':ket'       => 'Pesanan ' . $kode,
                    ]);
 
                    $pdo->prepare(
                        'INSERT INTO detail_transaksi
                            (id_transaksi, id_varian_produk, nama_produk_snapshot,
                             nama_varian_snapshot, jumlah, harga_satuan, sub_total)
                         VALUES (:t, :v, :np, :nv, :j, :h, :s)'
                    )->execute([
                        ':t'  => $idTransaksi,
                        ':v'  => $idVarian,
                        ':np' => $r['nama_produk'],
                        ':nv' => $r['nama_varian'],
                        ':j'  => $n,
                        ':h'  => (float) $r['harga_satuan'],
                        ':s'  => (float) $r['harga_satuan'] * $n,
                    ]);
                }
 
                $hasil[] = [
                    'id'           => $idTransaksi,
                    'order_code'   => $kode,
                    'status'       => 'menunggu_pembayaran',
                    'item_count'   => $totalProduk,
                    'total_amount' => $total,
                ];
            }
 
            $pdo->prepare('DELETE FROM detail_keranjang WHERE id_keranjang = :k')
                ->execute([':k' => $idKeranjang]);
 
            $pdo->commit();
 
            return $hasil;
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }
}