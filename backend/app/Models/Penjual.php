<?php

namespace App\Models;

use App\Core\Database;

/**
 * Data dashboard dan antrean untuk penjual.
 * dibatasi ke SATU profil penjual (id_profil_penjual) milik penjual yang login.
 */

final class Penjual {
    private const PEMASUKAN = "'dibayar','diproses','siap_diambil','selesai'";
    private const AKTIF = ['menunggu_pembayaran', 'dibayar', 'diproses', 'siap_diambil'];
    private const SEMUA = ['menunggu_pembayaran', 'dibayar', 'diproses', 'siap_diambil', 'selesai', 'dibatalkan'];

    private const HARI = "(t.tgl_transaksi AT TIME ZONE 'Asia/Jakarta')::date";
    private const HARI_INI = "(CURRENT_TIMESTAMP AT TIME ZONE 'Asia/Jakarta')::date";

    public static function profilIdForUser(int $userId): ?int {
        $stmt = Database::connection()->prepare(
            'SELECT id_profil_penjual FROM profil_penjual WHERE id_user = :id'
        );
        $stmt->execute([':id' => $userId]);
        $id = $stmt->fetchColumn();

        return $id ? (int) $id : null;
    }

    /** Profil usaha untuk pengaturan stand dan meta sidebar. */
    public static function profil(int $profilId): ?array {
        $stmt = Database::connection()->prepare(
            "SELECT id_profil_penjual, id_user, nama_usaha, no_hp,
                    COALESCE(alamat, '') AS alamat,
                    to_char(COALESCE(jam_buka, TIME '08:00'), 'HH24:MI') AS jam_buka,
                    to_char(COALESCE(jam_tutup, TIME '15:00'), 'HH24:MI') AS jam_tutup,
                    COALESCE(status_buka, TRUE) AS status_buka
             FROM profil_penjual
             WHERE id_profil_penjual = :p"
        );
        $stmt->execute([':p' => $profilId]);
        $row = $stmt->fetch();
        if (!$row) {
            return null;
        }

        return [
            'id' => (int) $row['id_profil_penjual'],
            'id_user' => (int) $row['id_user'],
            'nama_usaha' => $row['nama_usaha'],
            'lokasi' => $row['alamat'],
            'no_hp' => (string) ($row['no_hp'] ?? ''),
            'jam_buka' => $row['jam_buka'],
            'jam_tutup' => $row['jam_tutup'],
            'status_buka' => (bool) $row['status_buka'],
            'jam' => str_replace(':', '.', $row['jam_buka']) . ' - ' . str_replace(':', '.', $row['jam_tutup']) . ' WIB',
        ];
    }

    public static function updateProfil(int $profilId, array $data): array {
        $stmt = Database::connection()->prepare(
            'UPDATE profil_penjual
             SET nama_usaha = :nama,
                 alamat = :alamat,
                 no_hp = :hp,
                 jam_buka = CAST(:buka AS TIME),
                 jam_tutup = CAST(:tutup AS TIME),
                 status_buka = :buka_status
             WHERE id_profil_penjual = :p
             RETURNING id_profil_penjual'
        );
        $stmt->execute([
            ':nama' => $data['nama_usaha'],
            ':alamat' => $data['lokasi'] !== '' ? $data['lokasi'] : null,
            ':hp' => $data['no_hp'] !== '' ? $data['no_hp'] : null,
            ':buka' => $data['jam_buka'],
            ':tutup' => $data['jam_tutup'],
            ':buka_status' => $data['status_buka'] ? 'true' : 'false',
            ':p' => $profilId,
        ]);

        if (!$stmt->fetchColumn()) {
            throw new \RuntimeException('Profil penjual tidak ditemukan.');
        }

        return self::profil($profilId) ?? [];
    }

    public static function dashboard(int $profilId): array {
        $pdo = Database::connection();
        $in = self::PEMASUKAN;
        $hari = self::HARI;
        $hariIni = self::HARI_INI;

        // 1. Pendapatan hari ini / per-hari
        $stmt = $pdo->prepare(
            "SELECT COALESCE(SUM(t.subtotal) FILTER (WHERE t.status_transaksi IN ($in) AND $hari = $hariIni), 0) AS pendapatan,
                    COUNT(*) FILTER (WHERE t.status_transaksi = 'selesai') AS transaksi
             FROM transaksi t
             WHERE t.id_profil_penjual = :p"
        );
        $stmt->execute([':p' => $profilId]);
        $ringkas = $stmt->fetch();

        //2. Jumlah menu aktif per-kategori
        $stmt = $pdo->prepare(
            "SELECT lower(k.nama_kategori) AS kategori, COUNT(*) AS jumlah
             FROM produk p
             JOIN kategori_produk k ON k.id_kategori_produk = p.id_kategori_produk
             WHERE p.id_profil_penjual = :p AND p.status_produk = 'aktif'
             GROUP BY lower(k.nama_kategori)"
        );
        $stmt->execute([':p' => $profilId]);
        $perKategori = [];
        foreach ($stmt->fetchAll() as $r) {
            $perKategori[$r['kategori']] = (int) $r['jumlah'];
        }

        //3. peringatan stok menipis(di-set 5)
        $stmt = $pdo->prepare(
            "SELECT p.nama_produk, v.nama_varian, v.stok
             FROM varian_produk v
             JOIN produk p ON p.id_produk = v.id_produk
             WHERE p.id_profil_penjual = :p AND p.status_produk = 'aktif' AND v.status_aktif = TRUE
               AND v.stok <= CASE WHEN v.batas_stok_minimum > 0 THEN v.batas_stok_minimum ELSE 5 END
             ORDER BY v.stok, p.nama_produk"
        );
        $stmt->execute([':p' => $profilId]);
        $menipis = $stmt->fetchAll();
        $lowStock = [];
        foreach (array_slice($menipis, 0, 5) as $r) {
            $lowStock[] = ['nama' => self::namaItem($r['nama_produk'], $r['nama_varian']), 'stok' => (int) $r['stok']];
        }

        // 4. Ringkasan per kategori: sisa stok (semua) dan penjualan hari ini
        $stmt = $pdo->prepare(
            "SELECT k.nama_kategori AS nama, COALESCE(SUM(v.stok), 0) AS sisa
             FROM kategori_produk k
             JOIN produk p ON p.id_kategori_produk = k.id_kategori_produk
                  AND p.id_profil_penjual = :p AND p.status_produk = 'aktif'
             JOIN varian_produk v ON v.id_produk = p.id_produk AND v.status_aktif = TRUE
             WHERE k.status_aktif = TRUE
             GROUP BY k.id_kategori_produk, k.nama_kategori
             ORDER BY k.id_kategori_produk"
        );
        $stmt->execute([':p' => $profilId]);
        $sisaRows = $stmt->fetchAll();

        $stmt = $pdo->prepare(
            "SELECT k.nama_kategori AS nama, SUM(dt.jumlah) AS terjual, SUM(dt.sub_total) AS omzet
             FROM transaksi t
             JOIN detail_transaksi dt ON dt.id_transaksi = t.id_transaksi
             JOIN varian_produk v ON v.id_varian_produk = dt.id_varian_produk
             JOIN produk p ON p.id_produk = v.id_produk
             JOIN kategori_produk k ON k.id_kategori_produk = p.id_kategori_produk
             WHERE t.id_profil_penjual = :p
               AND t.status_transaksi IN ($in)
               AND $hari = $hariIni
             GROUP BY k.nama_kategori"
        );
        $stmt->execute([':p' => $profilId]);
        $jual = [];
        foreach ($stmt->fetchAll() as $r) {
            $jual[$r['nama']] = $r;
        }

        $categories = [];
        foreach ($sisaRows as $r) {
            $categories[] = [
                'nama'    => $r['nama'],
                'terjual' => (int) ($jual[$r['nama']]['terjual'] ?? 0),
                'sisa'    => (int) $r['sisa'],
                'omzet'   => (float) ($jual[$r['nama']]['omzet'] ?? 0),
            ];
        }

        // 5. Pendapatan 7 hari terakhir (hari ini di paling kanan)
        $stmt = $pdo->prepare(
            "SELECT $hari AS hari, SUM(t.subtotal) AS total
             FROM transaksi t
             WHERE t.id_profil_penjual = :p
               AND t.status_transaksi IN ($in)
               AND $hari >= $hariIni - 6
             GROUP BY 1"
        );
        $stmt->execute([':p' => $profilId]);
        $perHari = [];
        foreach ($stmt->fetchAll() as $r) {
            $perHari[$r['hari']] = (float) $r['total'];
        }

        $namaHari = [1 => 'Sen', 2 => 'Sel', 3 => 'Rab', 4 => 'Kam', 5 => 'Jum', 6 => 'Sab', 7 => 'Min'];
        $sekarang = new \DateTimeImmutable('now', new \DateTimeZone('Asia/Jakarta'));
        $weeklyRevenue = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = $sekarang->modify("-$i day");
            $weeklyRevenue[] = [
                'label' => $namaHari[(int) $d->format('N')],
                'value' => $perHari[$d->format('Y-m-d')] ?? 0.0,
            ];
        }

        // 6. Menu terlaris (hanya yang sudah pernah terjual)
        $stmt = $pdo->prepare(
            "SELECT p.nama_produk AS nama,
                    COALESCE(MIN(v.harga_satuan), 0) AS harga,
                    COALESCE(SUM(v.stok), 0) AS stok,
                    COALESCE(s.terjual, 0) AS terjual
             FROM produk p
             JOIN varian_produk v ON v.id_produk = p.id_produk AND v.status_aktif = TRUE
             LEFT JOIN (
                 SELECT vp.id_produk, SUM(dt.jumlah) AS terjual
                 FROM detail_transaksi dt
                 JOIN transaksi t ON t.id_transaksi = dt.id_transaksi
                      AND t.id_profil_penjual = :p2 AND t.status_transaksi IN ($in)
                 JOIN varian_produk vp ON vp.id_varian_produk = dt.id_varian_produk
                 GROUP BY vp.id_produk
             ) s ON s.id_produk = p.id_produk
             WHERE p.id_profil_penjual = :p AND p.status_produk = 'aktif'
             GROUP BY p.id_produk, s.terjual
             HAVING COALESCE(s.terjual, 0) > 0
             ORDER BY COALESCE(s.terjual, 0) DESC, p.id_produk
             LIMIT 5"
        );
        $stmt->execute([':p' => $profilId, ':p2' => $profilId]);
        $bestsellers = [];
        $rank = 1;
        foreach ($stmt->fetchAll() as $r) {
            $bestsellers[] = [
                'rank' => $rank++,
                'nama' => $r['nama'],
                'harga' => (float) $r['harga'],
                'stok' => (int) $r['stok'],
                'terjual' => (int) $r['terjual'],
            ];
        }

        // 7. Jumlah pesanan yang masih berjalan (untuk badge menu Antrean)
        $stmt = $pdo->prepare(
            'SELECT COUNT(*) FROM transaksi
             WHERE id_profil_penjual = :p AND status_transaksi IN (\'' . implode("','", self::AKTIF) . '\')'
        );
        $stmt->execute([':p' => $profilId]);
        $aktif = (int) $stmt->fetchColumn();

        $selesai = (int) ($ringkas['transaksi'] ?? 0);

        // 8. Transaksi selesai per metode
        $stmt = $pdo->prepare(
            "SELECT COALESCE(pb.kode_metode, 'tunai') AS metode, COUNT(*) AS jumlah
            FROM transaksi t
            LEFT JOIN LATERAL (
                SELECT m.kode_metode
                FROM pembayaran p
                JOIN metode_pembayaran m ON m.id_metode_pembayaran = p.id_metode_pembayaran
                WHERE p.id_transaksi = t.id_transaksi
                ORDER BY p.id_pembayaran DESC
                LIMIT 1
            ) pb ON TRUE
            WHERE t.id_profil_penjual = :p AND t.status_transaksi = 'selesai'
            GROUP BY 1"
        );
        $stmt->execute([':p' => $profilId]);
        $perMetode = [];
        foreach ($stmt->fetchAll() as $r) {
            $perMetode[$r['metode']] = (int) $r['jumlah'];
        }

        return [
            'stats' => [
                'pendapatan'   => (float) ($ringkas['pendapatan'] ?? 0),
                'transaksi'    => $selesai,
                'qris'         => $perMetode['qris'] ?? 0,
                'tunai'        => $perMetode['tunai'] ?? 0,
                'menu_aktif'   => array_sum($perKategori),
                'makanan'      => $perKategori['makanan'] ?? 0,
                'minuman'      => $perKategori['minuman'] ?? 0,
                'snack'        => $perKategori['snack'] ?? 0,
                'stok_menipis' => count($menipis),
            ],
            'lowStock'      => $lowStock,
            'categories'    => $categories,
            'weeklyRevenue' => $weeklyRevenue,
            'bestsellers'   => $bestsellers,
            'antrian_aktif' => $aktif,
        ];
    }

    /**
     * @param string $mode 'aktif' (antrean berjalan, terlama dulu) atau 'semua' (riwayat, terbaru dulu)
     */
    public static function pesanan(int $profilId, string $mode = 'aktif', int $limit = 100): array
    {
        $pdo = Database::connection();
        $statuses = $mode === 'semua' ? self::SEMUA : self::AKTIF;
        $urut = $mode === 'semua' ? 'DESC' : 'ASC';

        $params = [':p' => $profilId];
        $holders = [];
        foreach ($statuses as $i => $status) {
            $holders[] = ':s' . $i;
            $params[':s' . $i] = $status;
        }

        $sql = "WITH harian AS (
                    SELECT t.*, ROW_NUMBER() OVER (PARTITION BY " . self::HARI . " ORDER BY t.id_transaksi) AS no_antri
                    FROM transaksi t
                    WHERE t.id_profil_penjual = :p
                )
                SELECT t.id_transaksi AS id,
                       t.kode_transaksi,
                       t.status_transaksi AS status,
                       t.total_harga,
                       COALESCE(t.catatan_pembeli, '') AS catatan_raw,
                       t.no_antri,
                       to_char(t.tgl_transaksi AT TIME ZONE 'Asia/Jakarta', 'HH24:MI') AS waktu,
                       to_char(t.tgl_transaksi AT TIME ZONE 'Asia/Jakarta', 'YYYY-MM-DD HH24:MI') AS tanggal,
                       u.username AS pembeli,
                       COALESCE(u.no_hp, u.email, '-') AS kontak,
                       COALESCE(pb.kode_metode, 'tunai') AS metode,
                       COALESCE(pb.status_pembayaran, 'pending') AS status_bayar
                FROM harian t
                JOIN users u ON u.id_user = t.id_pembeli
                LEFT JOIN LATERAL (
                    SELECT m.kode_metode, p.status_pembayaran
                    FROM pembayaran p
                    JOIN metode_pembayaran m ON m.id_metode_pembayaran = p.id_metode_pembayaran
                    WHERE p.id_transaksi = t.id_transaksi
                    ORDER BY p.id_pembayaran DESC
                    LIMIT 1
                ) pb ON TRUE
                WHERE t.status_transaksi IN (" . implode(',', $holders) . ")
                ORDER BY t.tgl_transaksi $urut, t.id_transaksi $urut
                LIMIT " . max(1, min($limit, 500));

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();
        if (!$rows) {
            return [];
        }

        // Item semua pesanan sekaligus (satu query)
        $params = [];
        $holders = [];
        foreach ($rows as $i => $r) {
            $holders[] = ':i' . $i;
            $params[':i' . $i] = (int) $r['id'];
        }
        $stmt = $pdo->prepare(
            'SELECT id_transaksi, jumlah, harga_satuan, nama_produk_snapshot, nama_varian_snapshot
             FROM detail_transaksi
             WHERE id_transaksi IN (' . implode(',', $holders) . ')
             ORDER BY id_detail_transaksi'
        );
        $stmt->execute($params);
        $items = [];
        foreach ($stmt->fetchAll() as $it) {
            $items[(int) $it['id_transaksi']][] = [
                'qty'   => (int) $it['jumlah'],
                'nama'  => self::namaItem($it['nama_produk_snapshot'], $it['nama_varian_snapshot']),
                'harga' => (float) $it['harga_satuan'],
            ];
        }

        $out = [];
        foreach ($rows as $r) {
            // Jadwal pengambilan dicatat checkout di awal catatan: "[Ambil 12.00–12.30] catatan..."
            $slot = 'Segera';
            $catatan = (string) $r['catatan_raw'];
            if (preg_match('/^\[Ambil ([^\]]+)\]\s*/u', $catatan, $m)) {
                $slot = $m[1];
                $catatan = trim(substr($catatan, strlen($m[0])));
            }

            $out[] = [
                'id'       => (int) $r['id'],
                'queue'    => 'A-' . str_pad((string) $r['no_antri'], 3, '0', STR_PAD_LEFT),
                'kode'     => '#' . $r['kode_transaksi'],
                'kode_raw' => $r['kode_transaksi'],
                'status'   => $r['status'],
                'slot'     => $slot,
                'pembayar' => self::labelBayar($r['metode'], $r['status_bayar']),
                'metode'   => $r['metode'],
                'status_bayar' => $r['status_bayar'],
                'pembeli'  => $r['pembeli'],
                'nim'      => $r['kontak'],
                'waktu'    => $r['waktu'],
                'tanggal'  => $r['tanggal'],
                'total'    => (float) $r['total_harga'],
                'catatan'  => $catatan,
                'items'    => $items[(int) $r['id']] ?? [],
            ];
        }

        return $out;
    }

    private static function labelBayar(string $metode, string $status): string {
        if ($metode === 'qris') {
            return 'QRIS - ' . match ($status) {
                'dibayar' => 'Terverifikasi',
                'dikembalikan' => 'Perlu dikembalikan',
                'dibatalkan' => 'Dibatalkan',
                default => 'Menunggu verifikasi',
            };
        }

        return 'Tunai - ' . match ($status) {
            'dibayar' => 'Lunas',
            'dibatalkan', 'dikembalikan' => 'Dibatalkan',
            default => 'Bayar saat ambil',
        };
    }

    private static function namaItem(string $produk, string $varian): string {
        return strtolower($varian) === 'reguler' ? $produk : $produk . ' - ' . $varian;
    }
}