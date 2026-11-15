<?php

namespace App\Models;

use App\Core\Database;
use App\Core\Media;

/** Daftar menu dan riwayat stok milik satu penjual. */
final class Menu {
    public static function untukPenjual(int $profilId): array {
        $stmt = Database::connection()->prepare(
            "SELECT p.id_produk AS id,
                    p.nama_produk AS nama,
                    p.id_kategori_produk AS kategori_id,
                    k.nama_kategori AS kategori,
                    COALESCE(p.deskripsi, '') AS deskripsi,
                    p.gambar,
                    p.status_produk AS status,
                    COALESCE(vc.n, 0) AS varian,
                    pv.id_varian_produk AS id_varian,
                    COALESCE(pv.harga_satuan, 0) AS harga,
                    COALESCE(pv.stok, 0) AS stok
             FROM produk p
             JOIN kategori_produk k ON k.id_kategori_produk = p.id_kategori_produk
             LEFT JOIN LATERAL (
                 SELECT v.id_varian_produk, v.harga_satuan, v.stok
                 FROM varian_produk v
                 WHERE v.id_produk = p.id_produk AND v.status_aktif = TRUE
                 ORDER BY v.id_varian_produk
                 LIMIT 1
             ) pv ON TRUE
             LEFT JOIN LATERAL (
                 SELECT COUNT(*) AS n
                 FROM varian_produk v
                 WHERE v.id_produk = p.id_produk AND v.status_aktif = TRUE
             ) vc ON TRUE
             WHERE p.id_profil_penjual = :p
             ORDER BY p.id_produk"
        );
        $stmt->execute([':p' => $profilId]);
        $rows = $stmt->fetchAll();

        foreach ($rows as &$r) {
            $r['id']          = (int) $r['id'];
            $r['kategori_id'] = (int) $r['kategori_id'];
            $r['varian']      = (int) $r['varian'];
            $r['id_varian']   = (int) $r['id_varian'];
            $r['harga']       = (int) round((float) $r['harga']);
            $r['stok']        = (int) $r['stok'];
            $r['gambar']      = Media::url($r['gambar']);
        }
        unset($r);

        return $rows;
    }

    public static function riwayatStok(int $profilId, int $limit = 200): array {
        $stmt = Database::connection()->prepare(
            "SELECT to_char(l.created_at AT TIME ZONE 'Asia/Jakarta', 'YYYY-MM-DD HH24:MI') AS waktu,
                    p.nama_produk, v.nama_varian,
                    l.jenis_perubahan AS jenis,
                    l.jumlah_perubahan AS jumlah,
                    l.stok_sebelum AS sebelum,
                    l.stok_sesudah AS sesudah,
                    COALESCE(u.username, 'Sistem') AS petugas,
                    COALESCE(l.keterangan, '') AS keterangan
             FROM stok_log l
             JOIN varian_produk v ON v.id_varian_produk = l.id_varian_produk
             JOIN produk p ON p.id_produk = v.id_produk
             LEFT JOIN users u ON u.id_user = l.id_user
             WHERE p.id_profil_penjual = :p
             ORDER BY l.created_at DESC, l.id_stok_log DESC
             LIMIT " . max(1, min($limit, 500))
        );
        $stmt->execute([':p' => $profilId]);

        $out = [];
        foreach ($stmt->fetchAll() as $r) {
            $out[] = [
                'waktu'      => $r['waktu'],
                'produk'     => $r['nama_produk'] . ' / ' . $r['nama_varian'],
                'jenis'      => $r['jenis'],
                'jumlah'     => (int) $r['jumlah'],
                'sebelum'    => (int) $r['sebelum'],
                'sesudah'    => (int) $r['sesudah'],
                'petugas'    => $r['petugas'],
                'keterangan' => $r['keterangan'],
            ];
        }

        return $out;
    }
}