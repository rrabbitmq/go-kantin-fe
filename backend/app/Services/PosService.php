<?php

namespace App\Services;

use App\Core\Database;
use App\Core\HttpException;

/** Transaksi walk-in kasir POS dan validasi tiket QR. */
final class PosService
{
    public static function jual(int $profilId, int $penjualUserId, array $items, string $metode): array
    {
        $metode = strtolower(trim($metode));
        if (!in_array($metode, ['tunai', 'qris'], true)) {
            throw new HttpException(400, 'Metode pembayaran harus tunai atau qris.');
        }
        if ($items === []) {
            throw new HttpException(400, 'Nota masih kosong.');
        }

        $pdo = Database::connection();
        $pdo->beginTransaction();

        try {
            $stmt = $pdo->prepare(
                'SELECT id_metode_pembayaran FROM metode_pembayaran WHERE kode_metode = :k AND status_aktif = TRUE'
            );
            $stmt->execute([':k' => $metode]);
            $idMetode = $stmt->fetchColumn();
            if (!$idMetode) {
                throw new HttpException(400, 'Metode pembayaran tidak tersedia.');
            }

            $baris = [];
            $totalProduk = 0;
            $subtotal = 0.0;

            foreach ($items as $item) {
                $idVarian = (int) ($item['id_varian'] ?? $item['id'] ?? 0);
                $jumlah = (int) ($item['qty'] ?? $item['jumlah'] ?? 0);
                if ($idVarian < 1 || $jumlah < 1) {
                    throw new HttpException(400, 'Item nota tidak valid.');
                }

                $q = $pdo->prepare(
                    "SELECT v.id_varian_produk, v.nama_varian, v.harga_satuan, v.stok,
                            p.nama_produk
                     FROM varian_produk v
                     JOIN produk p ON p.id_produk = v.id_produk
                     WHERE v.id_varian_produk = :v
                       AND p.id_profil_penjual = :p
                       AND v.status_aktif = TRUE
                       AND p.status_produk = 'aktif'
                     FOR UPDATE OF v"
                );
                $q->execute([':v' => $idVarian, ':p' => $profilId]);
                $row = $q->fetch();
                if (!$row) {
                    throw new HttpException(404, 'Produk tidak ditemukan di stand ini.');
                }
                if ((int) $row['stok'] < $jumlah) {
                    throw new HttpException(409, 'Stok ' . $row['nama_produk'] . ' tidak mencukupi.');
                }

                $baris[] = [
                    'id_varian' => $idVarian,
                    'nama_produk' => $row['nama_produk'],
                    'nama_varian' => $row['nama_varian'],
                    'harga' => (float) $row['harga_satuan'],
                    'jumlah' => $jumlah,
                    'stok' => (int) $row['stok'],
                ];
                $totalProduk += $jumlah;
                $subtotal += (float) $row['harga_satuan'] * $jumlah;
            }

            $kode = 'POS-' . date('ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
            $ins = $pdo->prepare(
                'INSERT INTO transaksi
                    (kode_transaksi, id_pembeli, id_profil_penjual, total_produk,
                     subtotal, biaya_layanan, total_harga, status_transaksi, catatan_pembeli, waktu_selesai)
                 VALUES (:kode, :pembeli, :penjual, :total_produk,
                         :subtotal, 0, :total, \'selesai\', :catatan, CURRENT_TIMESTAMP)
                 RETURNING id_transaksi'
            );
            $ins->execute([
                ':kode' => $kode,
                ':pembeli' => $penjualUserId,
                ':penjual' => $profilId,
                ':total_produk' => $totalProduk,
                ':subtotal' => $subtotal,
                ':total' => $subtotal,
                ':catatan' => 'POS walk-in',
            ]);
            $idTransaksi = (int) $ins->fetchColumn();

            foreach ($baris as $r) {
                $n = $r['jumlah'];
                $upd = $pdo->prepare(
                    'UPDATE varian_produk SET stok = stok - :n
                     WHERE id_varian_produk = :v AND stok >= :min
                     RETURNING stok'
                );
                $upd->execute([':n' => $n, ':v' => $r['id_varian'], ':min' => $n]);
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
                    ':v' => $r['id_varian'],
                    ':u' => $penjualUserId,
                    ':perubahan' => -$n,
                    ':sebelum' => $sesudah + $n,
                    ':sesudah' => $sesudah,
                    ':ket' => 'POS ' . $kode,
                ]);

                $pdo->prepare(
                    'INSERT INTO detail_transaksi
                        (id_transaksi, id_varian_produk, nama_produk_snapshot,
                         nama_varian_snapshot, jumlah, harga_satuan, sub_total)
                     VALUES (:t, :v, :np, :nv, :j, :h, :s)'
                )->execute([
                    ':t' => $idTransaksi,
                    ':v' => $r['id_varian'],
                    ':np' => $r['nama_produk'],
                    ':nv' => $r['nama_varian'],
                    ':j' => $n,
                    ':h' => $r['harga'],
                    ':s' => $r['harga'] * $n,
                ]);
            }

            $pdo->prepare(
                "INSERT INTO pembayaran
                    (id_transaksi, id_metode_pembayaran, provider, referensi_provider, jumlah_bayar, status_pembayaran)
                 VALUES (:t, :m, 'pos', :ref, :jumlah, 'dibayar')"
            )->execute([
                ':t' => $idTransaksi,
                ':m' => (int) $idMetode,
                ':ref' => $kode,
                ':jumlah' => $subtotal,
            ]);

            $pdo->commit();

            return [
                'id' => $idTransaksi,
                'kode' => $kode,
                'total' => (int) round($subtotal),
                'metode' => $metode,
                'item_count' => $totalProduk,
            ];
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    public static function cariTiket(int $profilId, string $kode): array
    {
        $kode = trim($kode);
        if ($kode === '') {
            throw new HttpException(400, 'Kode tiket wajib diisi.');
        }

        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            "SELECT t.id_transaksi, t.kode_transaksi, t.status_transaksi, t.total_harga,
                    u.username AS pembeli,
                    COALESCE(bayar.kode_metode, 'tunai') AS metode
             FROM transaksi t
             JOIN users u ON u.id_user = t.id_pembeli
             LEFT JOIN LATERAL (
                 SELECT mp.kode_metode
                 FROM pembayaran pb
                 JOIN metode_pembayaran mp ON mp.id_metode_pembayaran = pb.id_metode_pembayaran
                 WHERE pb.id_transaksi = t.id_transaksi
                 ORDER BY pb.id_pembayaran DESC
                 LIMIT 1
             ) bayar ON TRUE
             WHERE t.id_profil_penjual = :p
               AND (t.kode_transaksi = :k OR t.kode_transaksi ILIKE :like)
             LIMIT 1"
        );
        $stmt->execute([':p' => $profilId, ':k' => $kode, ':like' => '%' . $kode . '%']);
        $t = $stmt->fetch();
        if (!$t) {
            throw new HttpException(404, 'Tiket / kode transaksi tidak ditemukan.');
        }

        $items = $pdo->prepare(
            'SELECT jumlah, nama_produk_snapshot, nama_varian_snapshot, harga_satuan, sub_total
             FROM detail_transaksi WHERE id_transaksi = :id ORDER BY id_detail_transaksi'
        );
        $items->execute([':id' => $t['id_transaksi']]);
        $daftar = [];
        foreach ($items->fetchAll() as $r) {
            $nama = strtolower($r['nama_varian_snapshot']) === 'reguler'
                ? $r['nama_produk_snapshot']
                : $r['nama_produk_snapshot'] . ' - ' . $r['nama_varian_snapshot'];
            $daftar[] = [
                'nama' => $nama,
                'jumlah' => (int) $r['jumlah'],
                'sub_total' => (int) round((float) $r['sub_total']),
            ];
        }

        return [
            'id' => (int) $t['id_transaksi'],
            'kode' => $t['kode_transaksi'],
            'status' => $t['status_transaksi'],
            'pembeli' => $t['pembeli'],
            'metode' => $t['metode'],
            'total' => (int) round((float) $t['total_harga']),
            'items' => $daftar,
            'bisa_selesai' => $t['status_transaksi'] === 'siap_diambil',
        ];
    }
}
