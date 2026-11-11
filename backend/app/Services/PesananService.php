<?php

namespace App\Services;

use App\Core\Database;
use App\Core\HttpException;

/**
 * Penjual memajukan status pesanan:
 *   menunggu_pembayaran --konfirmasi--> dibayar --proses--> diproses --siap--> siap_diambil --selesai--> selesai
 * Pesanan yang belum selesai bisa dibatalkan (stok dikembalikan dan dicatat di stok_log).
 */
final class PesananService
{
    private const TRANSISI = [
        'konfirmasi' => ['dari' => ['menunggu_pembayaran'], 'ke' => 'dibayar'],
        'proses'     => ['dari' => ['dibayar'], 'ke' => 'diproses'],
        'siap'       => ['dari' => ['diproses'], 'ke' => 'siap_diambil'],
        'selesai'    => ['dari' => ['siap_diambil'], 'ke' => 'selesai'],
        'batal'      => ['dari' => ['menunggu_pembayaran', 'dibayar', 'diproses'], 'ke' => 'dibatalkan'],
    ];

    private const PESAN = [
        'konfirmasi' => 'Pesanan dikonfirmasi.',
        'proses'     => 'Pesanan mulai disiapkan.',
        'siap'       => 'Pesanan siap diambil.',
        'selesai'    => 'Pesanan selesai.',
        'batal'      => 'Pesanan dibatalkan dan stok dikembalikan.',
    ];

    public static function ubahStatus(int $profilId, int $idTransaksi, string $aksi, int $userId): array
    {
        if (!isset(self::TRANSISI[$aksi])) {
            throw new HttpException(400, 'Aksi tidak dikenal.');
        }
        $aturan = self::TRANSISI[$aksi];

        $pdo = Database::connection();
        $pdo->beginTransaction();

        try {
            // Kunci baris pesanan, dan pastikan milik penjual yang login
            $stmt = $pdo->prepare(
                'SELECT kode_transaksi, status_transaksi
                 FROM transaksi
                 WHERE id_transaksi = :id AND id_profil_penjual = :p
                 FOR UPDATE'
            );
            $stmt->execute([':id' => $idTransaksi, ':p' => $profilId]);
            $row = $stmt->fetch();

            if (!$row) {
                throw new HttpException(404, 'Pesanan tidak ditemukan.');
            }
            if (!in_array($row['status_transaksi'], $aturan['dari'], true)) {
                throw new HttpException(409, 'Status pesanan sudah berubah. Muat ulang halaman.');
            }

            if ($aksi === 'batal') {
                self::kembalikanStok($pdo, $idTransaksi, $userId, $row['kode_transaksi']);
            }

            $sql = 'UPDATE transaksi SET status_transaksi = :s';
            if ($aturan['ke'] === 'selesai') {
                $sql .= ', waktu_selesai = CURRENT_TIMESTAMP';
            }
            $sql .= ' WHERE id_transaksi = :id';
            $pdo->prepare($sql)->execute([':s' => $aturan['ke'], ':id' => $idTransaksi]);

            $pdo->commit();

            return [
                'id'         => $idTransaksi,
                'order_code' => $row['kode_transaksi'],
                'status'     => $aturan['ke'],
                'pesan'      => self::PESAN[$aksi],
            ];
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    private static function kembalikanStok(\PDO $pdo, int $idTransaksi, int $userId, string $kode): void
    {
        $stmt = $pdo->prepare('SELECT id_varian_produk, jumlah FROM detail_transaksi WHERE id_transaksi = :id');
        $stmt->execute([':id' => $idTransaksi]);

        foreach ($stmt->fetchAll() as $it) {
            $n = (int) $it['jumlah'];
            $idVarian = (int) $it['id_varian_produk'];

            $upd = $pdo->prepare('UPDATE varian_produk SET stok = stok + :n WHERE id_varian_produk = :v RETURNING stok');
            $upd->execute([':n' => $n, ':v' => $idVarian]);
            $sesudah = (int) $upd->fetchColumn();

            $pdo->prepare(
                "INSERT INTO stok_log
                    (id_varian_produk, id_user, jenis_perubahan, jumlah_perubahan, stok_sebelum, stok_sesudah, keterangan)
                 VALUES (:v, :u, 'pembatalan', :perubahan, :sebelum, :sesudah, :ket)"
            )->execute([
                ':v'         => $idVarian,
                ':u'         => $userId,
                ':perubahan' => $n,
                ':sebelum'   => $sesudah - $n,
                ':sesudah'   => $sesudah,
                ':ket'       => 'Pembatalan ' . $kode,
            ]);
        }
    }
}