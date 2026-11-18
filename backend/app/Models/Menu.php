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
                    COALESCE(vc.id_varian, 0) AS id_varian,
                    COALESCE(vc.harga_min, 0) AS harga,
                    COALESCE(vc.stok_total, 0) AS stok,
                    COALESCE(vc.items, '[]'::json) AS variants_json
             FROM produk p
             JOIN kategori_produk k ON k.id_kategori_produk = p.id_kategori_produk
             LEFT JOIN LATERAL (
                 SELECT COUNT(*)::int AS n,
                        MIN(v.harga_satuan) AS harga_min,
                        SUM(v.stok)::int AS stok_total,
                        (ARRAY_AGG(v.id_varian_produk ORDER BY v.id_varian_produk))[1] AS id_varian,
                        json_agg(
                            json_build_object(
                                'id', v.id_varian_produk,
                                'nama', v.nama_varian,
                                'harga', v.harga_satuan,
                                'stok', v.stok
                            )
                            ORDER BY v.id_varian_produk
                        ) AS items
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

            $variants = json_decode((string) ($r['variants_json'] ?? '[]'), true);
            unset($r['variants_json']);
            $r['variants'] = [];
            if (is_array($variants)) {
                foreach ($variants as $v) {
                    if (!is_array($v)) {
                        continue;
                    }
                    $r['variants'][] = [
                        'id' => (int) ($v['id'] ?? 0),
                        'nama' => (string) ($v['nama'] ?? ''),
                        'harga' => (int) round((float) ($v['harga'] ?? 0)),
                        'stok' => (int) ($v['stok'] ?? 0),
                    ];
                }
            }
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
