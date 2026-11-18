<?php

namespace App\Services;

use App\Core\Database;
use App\Core\HttpException;
use App\Core\Media;

/**
 * Tambah, ubah, nonaktifkan menu, dan sesuaikan stok.
 * Harga & stok dikelola per varian (minimal 1 varian aktif).
 * Semua perubahan stok dicatat di stok_log (tampil di halaman Mutasi Stok).
 */
final class MenuService {
    private const STATUS = ['aktif', 'habis', 'nonaktif'];

    /** Tambah menu (tanpa id) atau ubah menu (id diisi). $file = $_FILES['gambar'] atau null. */
    public static function simpan(int $profilId, int $userId, array $in, ?array $file): array {
        $id = (int) ($in['id'] ?? 0);
        $data = self::validasi($profilId, $in, $id);

        $gambarBaru = $file !== null ? Media::simpan($file) : null;

        $pdo = Database::connection();
        $pdo->beginTransaction();
        try {
            $hasil = $id > 0
                ? self::ubah($pdo, $profilId, $userId, $id, $data, $gambarBaru)
                : self::tambah($pdo, $profilId, $userId, $data, $gambarBaru);
            $pdo->commit();
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            Media::hapus($gambarBaru);
            throw $e;
        }

        Media::hapus($hasil['gambar_lama'] ?? null);
        unset($hasil['gambar_lama']);

        return $hasil;
    }

    /** "Hapus" menu = nonaktifkan, karena menu bisa sudah tercatat di transaksi lama. */
    public static function nonaktifkan(int $profilId, int $idProduk): void {
        $stmt = Database::connection()->prepare(
            "UPDATE produk SET status_produk = 'nonaktif'
             WHERE id_produk = :id AND id_profil_penjual = :p"
        );
        $stmt->execute([':id' => $idProduk, ':p' => $profilId]);

        if ($stmt->rowCount() === 0) {
            throw new HttpException(404, 'Menu tidak ditemukan.');
        }
    }

    /**
     * masuk        : stok bertambah sebanyak jumlah
     * keluar       : stok berkurang sebanyak jumlah
     * penyesuaian  : stok DIATUR menjadi jumlah
     */
    public static function ubahStok(int $profilId, int $userId, int $idVarian, string $jenis, int $jumlah, string $keterangan): array {
        if (!in_array($jenis, ['masuk', 'keluar', 'penyesuaian'], true)) {
            throw new HttpException(400, 'Validasi gagal', ['jenis' => 'Jenis harus masuk, keluar, atau penyesuaian.']);
        }
        $min = $jenis === 'penyesuaian' ? 0 : 1;
        if ($jumlah < $min || $jumlah > 100000) {
            throw new HttpException(400, 'Validasi gagal', ['jumlah' => "Jumlah harus $min - 100000."]);
        }

        $pdo = Database::connection();
        $pdo->beginTransaction();

        try {
            $stmt = $pdo->prepare(
                'SELECT v.stok, p.id_produk, p.status_produk
                 FROM varian_produk v
                 JOIN produk p ON p.id_produk = v.id_produk
                 WHERE v.id_varian_produk = :v AND p.id_profil_penjual = :p
                 FOR UPDATE OF v'
            );
            $stmt->execute([':v' => $idVarian, ':p' => $profilId]);
            $row = $stmt->fetch();
            if (!$row) {
                throw new HttpException(404, 'Varian menu tidak ditemukan.');
            }

            $lama = (int) $row['stok'];
            $baru = match ($jenis) {
                'masuk' => $lama + $jumlah,
                'keluar' => $lama - $jumlah,
                default => $jumlah,
            };
            if ($baru < 0) {
                throw new HttpException(409, "Stok tidak cukup untuk dikurangi (stok sekarang $lama).");
            }
            if ($baru === $lama) {
                throw new HttpException(400, 'Stok tidak berubah.');
            }

            $pdo->prepare('UPDATE varian_produk SET stok = :s WHERE id_varian_produk = :v')
                ->execute([':s' => $baru, ':v' => $idVarian]);

            self::catatStok($pdo, $idVarian, $userId, $jenis, $baru - $lama, $lama, $baru,
                $keterangan !== '' ? $keterangan : 'Penyesuaian manual');

            self::sinkronStatusDariStok($pdo, (int) $row['id_produk'], (string) $row['status_produk']);

            $pdo->commit();

            return ['stok' => $baru, 'pesan' => "Stok diperbarui: $lama menjadi $baru."];
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    private static function validasi(int $profilId, array $in, int $id): array {
        $err = [];

        $nama = trim((string) ($in['nama'] ?? ''));
        if (mb_strlen($nama) < 2 || mb_strlen($nama) > 150) {
            $err['nama'] = 'Nama menu 2-150 karakter.';
        }

        $deskripsi = trim((string) ($in['deskripsi'] ?? ''));
        if (mb_strlen($deskripsi) > 1000) {
            $err['deskripsi'] = 'Deskripsi maksimal 1000 karakter.';
        }

        $kategori = filter_var($in['id_kategori'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if (!$kategori) {
            $err['id_kategori'] = 'Kategori wajib dipilih.';
        }

        $status = (string) ($in['status'] ?? 'aktif');
        if (!in_array($status, self::STATUS, true)) {
            $err['status'] = 'Status tidak valid.';
        }

        $variants = self::parseVariants($in);
        if ($variants === []) {
            // Backward compatible: harga+stok tunggal → 1 varian Reguler
            $harga = filter_var($in['harga'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 10000000]]);
            $stok = filter_var($in['stok'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 100000]]);
            if ($harga === false || $harga === null || $stok === false || $stok === null) {
                $err['varian'] = 'Minimal satu varian dengan nama, harga, dan stok.';
            } else {
                $variants = [[
                    'id' => 0,
                    'nama' => 'Reguler',
                    'harga' => (int) $harga,
                    'stok' => (int) $stok,
                ]];
            }
        }

        $names = [];
        foreach ($variants as $i => $v) {
            $key = 'varian.' . ($i + 1);
            $vNama = trim((string) ($v['nama'] ?? ''));
            if ($vNama === '' || mb_strlen($vNama) > 100) {
                $err[$key . '.nama'] = 'Nama varian wajib (maks. 100 karakter).';
            }
            $lower = mb_strtolower($vNama);
            if ($vNama !== '' && isset($names[$lower])) {
                $err[$key . '.nama'] = 'Nama varian duplikat: ' . $vNama;
            }
            $names[$lower] = true;

            $vHarga = filter_var($v['harga'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 10000000]]);
            if ($vHarga === false || $vHarga === null) {
                $err[$key . '.harga'] = 'Harga varian harus 0 - 10.000.000.';
            }

            $vStok = filter_var($v['stok'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 100000]]);
            if ($vStok === false || $vStok === null) {
                $err[$key . '.stok'] = 'Stok varian harus 0 - 100000.';
            }

            $variants[$i] = [
                'id' => max(0, (int) ($v['id'] ?? 0)),
                'nama' => $vNama,
                'harga' => (int) ($vHarga ?? 0),
                'stok' => (int) ($vStok ?? 0),
            ];
        }

        if (!$err) {
            $pdo = Database::connection();

            $stmt = $pdo->prepare('SELECT 1 FROM kategori_produk WHERE id_kategori_produk = :k AND status_aktif = TRUE');
            $stmt->execute([':k' => $kategori]);
            if (!$stmt->fetchColumn()) {
                $err['id_kategori'] = 'Kategori tidak ditemukan.';
            }

            $stmt = $pdo->prepare(
                'SELECT 1 FROM produk
                 WHERE id_profil_penjual = :p AND lower(nama_produk) = lower(:n) AND id_produk <> :id'
            );
            $stmt->execute([':p' => $profilId, ':n' => $nama, ':id' => $id]);
            if ($stmt->fetchColumn()) {
                $err['nama'] = 'Nama menu sudah dipakai.';
            }
        }

        if ($err) {
            throw new HttpException(400, 'Validasi gagal', $err);
        }

        $stokTotal = array_sum(array_column($variants, 'stok'));

        return [
            'nama' => $nama,
            'deskripsi' => $deskripsi,
            'kategori' => (int) $kategori,
            'status' => $status,
            'variants' => $variants,
            'stok_total' => $stokTotal,
        ];
    }

    /** @return list<array{id?:int,nama?:string,harga?:mixed,stok?:mixed}> */
    private static function parseVariants(array $in): array {
        if (!empty($in['varian_json']) && is_string($in['varian_json'])) {
            $decoded = json_decode($in['varian_json'], true);
            if (is_array($decoded)) {
                return array_values(array_filter($decoded, 'is_array'));
            }
        }

        if (!empty($in['varian']) && is_array($in['varian'])) {
            return array_values(array_filter($in['varian'], 'is_array'));
        }

        return [];
    }

    private static function tambah(\PDO $pdo, int $profilId, int $userId, array $d, ?string $gambar): array {
        $status = ($d['stok_total'] === 0 && $d['status'] === 'aktif') ? 'habis' : $d['status'];

        $stmt = $pdo->prepare(
            'INSERT INTO produk (id_profil_penjual, id_kategori_produk, nama_produk, deskripsi, gambar, status_produk)
             VALUES (:p, :k, :n, :d, :g, :s)
             RETURNING id_produk'
        );
        $stmt->execute([
            ':p' => $profilId,
            ':k' => $d['kategori'],
            ':n' => $d['nama'],
            ':d' => $d['deskripsi'] !== '' ? $d['deskripsi'] : null,
            ':g' => $gambar,
            ':s' => $status,
        ]);
        $idProduk = (int) $stmt->fetchColumn();

        foreach ($d['variants'] as $variant) {
            self::insertVariant($pdo, $idProduk, $userId, $variant, true);
        }

        return ['id' => $idProduk, 'pesan' => 'Menu ditambahkan.'];
    }

    private static function ubah(\PDO $pdo, int $profilId, int $userId, int $id, array $d, ?string $gambarBaru): array {
        $stmt = $pdo->prepare(
            'SELECT gambar, status_produk FROM produk WHERE id_produk = :id AND id_profil_penjual = :p FOR UPDATE'
        );
        $stmt->execute([':id' => $id, ':p' => $profilId]);
        $produk = $stmt->fetch();
        if (!$produk) {
            throw new HttpException(404, 'Menu tidak ditemukan.');
        }

        $stmt = $pdo->prepare(
            'SELECT id_varian_produk, nama_varian, harga_satuan, stok, status_aktif
             FROM varian_produk
             WHERE id_produk = :id
             ORDER BY id_varian_produk
             FOR UPDATE'
        );
        $stmt->execute([':id' => $id]);
        $existing = $stmt->fetchAll();
        $byId = [];
        foreach ($existing as $row) {
            $byId[(int) $row['id_varian_produk']] = $row;
        }

        $status = $d['status'];
        if ($status === 'aktif' && $d['stok_total'] === 0) {
            $status = 'habis';
        } elseif ($status === 'habis' && $d['stok_total'] > 0) {
            $status = 'aktif';
        }

        $sets = 'id_kategori_produk = :k, nama_produk = :n, deskripsi = :d, status_produk = :s';
        $params = [
            ':k' => $d['kategori'],
            ':n' => $d['nama'],
            ':d' => $d['deskripsi'] !== '' ? $d['deskripsi'] : null,
            ':s' => $status,
            ':id' => $id,
        ];
        if ($gambarBaru !== null) {
            $sets .= ', gambar = :g';
            $params[':g'] = $gambarBaru;
        }
        $pdo->prepare("UPDATE produk SET $sets WHERE id_produk = :id")->execute($params);

        $keepIds = [];
        foreach ($d['variants'] as $variant) {
            $vid = (int) $variant['id'];
            if ($vid > 0 && isset($byId[$vid])) {
                self::updateVariant($pdo, $userId, $byId[$vid], $variant);
                $keepIds[$vid] = true;
            } else {
                $newId = self::insertVariant($pdo, $id, $userId, $variant, false);
                $keepIds[$newId] = true;
            }
        }

        foreach ($byId as $vid => $row) {
            if (isset($keepIds[$vid])) {
                continue;
            }
            if (!(bool) $row['status_aktif']) {
                continue;
            }
            // Soft-delete: jangan hard delete karena bisa sudah dipakai transaksi
            $pdo->prepare(
                'UPDATE varian_produk SET status_aktif = FALSE WHERE id_varian_produk = :v'
            )->execute([':v' => $vid]);
        }

        $activeLeft = $pdo->prepare(
            'SELECT COUNT(*) FROM varian_produk WHERE id_produk = :id AND status_aktif = TRUE'
        );
        $activeLeft->execute([':id' => $id]);
        if ((int) $activeLeft->fetchColumn() < 1) {
            throw new HttpException(400, 'Validasi gagal', ['varian' => 'Minimal satu varian aktif.']);
        }

        return [
            'id' => $id,
            'pesan' => 'Menu diperbarui.',
            'gambar_lama' => $gambarBaru !== null ? $produk['gambar'] : null,
        ];
    }

    /** @param array{id?:int,nama:string,harga:int,stok:int} $variant */
    private static function insertVariant(\PDO $pdo, int $idProduk, int $userId, array $variant, bool $isNewProduct): int {
        // Aktifkan ulang jika nama sama pernah dinonaktifkan
        $stmt = $pdo->prepare(
            'SELECT id_varian_produk, stok FROM varian_produk
             WHERE id_produk = :p AND lower(nama_varian) = lower(:n)
             LIMIT 1 FOR UPDATE'
        );
        $stmt->execute([':p' => $idProduk, ':n' => $variant['nama']]);
        $existing = $stmt->fetch();
        if ($existing) {
            $vid = (int) $existing['id_varian_produk'];
            $lama = (int) $existing['stok'];
            $pdo->prepare(
                'UPDATE varian_produk
                 SET nama_varian = :n, harga_satuan = :h, stok = :s, status_aktif = TRUE
                 WHERE id_varian_produk = :v'
            )->execute([
                ':n' => $variant['nama'],
                ':h' => $variant['harga'],
                ':s' => $variant['stok'],
                ':v' => $vid,
            ]);
            if ($variant['stok'] !== $lama) {
                self::catatStok(
                    $pdo,
                    $vid,
                    $userId,
                    'penyesuaian',
                    $variant['stok'] - $lama,
                    $lama,
                    $variant['stok'],
                    $isNewProduct ? 'Stok awal' : 'Diubah dari form menu'
                );
            }
            return $vid;
        }

        $stmt = $pdo->prepare(
            'INSERT INTO varian_produk (id_produk, nama_varian, harga_satuan, stok, batas_stok_minimum)
             VALUES (:p, :n, :h, :s, 5)
             RETURNING id_varian_produk'
        );
        $stmt->execute([
            ':p' => $idProduk,
            ':n' => $variant['nama'],
            ':h' => $variant['harga'],
            ':s' => $variant['stok'],
        ]);
        $vid = (int) $stmt->fetchColumn();

        if ($variant['stok'] > 0) {
            self::catatStok(
                $pdo,
                $vid,
                $userId,
                'masuk',
                $variant['stok'],
                0,
                $variant['stok'],
                $isNewProduct ? 'Stok awal' : 'Varian baru'
            );
        }

        return $vid;
    }

    /**
     * @param array $current row dari DB
     * @param array{nama:string,harga:int,stok:int} $variant
     */
    private static function updateVariant(\PDO $pdo, int $userId, array $current, array $variant): void {
        $vid = (int) $current['id_varian_produk'];
        $lama = (int) $current['stok'];

        $pdo->prepare(
            'UPDATE varian_produk
             SET nama_varian = :n, harga_satuan = :h, stok = :s, status_aktif = TRUE
             WHERE id_varian_produk = :v'
        )->execute([
            ':n' => $variant['nama'],
            ':h' => $variant['harga'],
            ':s' => $variant['stok'],
            ':v' => $vid,
        ]);

        if ($variant['stok'] !== $lama) {
            self::catatStok(
                $pdo,
                $vid,
                $userId,
                'penyesuaian',
                $variant['stok'] - $lama,
                $lama,
                $variant['stok'],
                'Diubah dari form menu'
            );
        }
    }

    private static function sinkronStatusDariStok(\PDO $pdo, int $idProduk, string $statusSekarang): void {
        $stmt = $pdo->prepare(
            'SELECT COALESCE(SUM(stok), 0) FROM varian_produk WHERE id_produk = :id AND status_aktif = TRUE'
        );
        $stmt->execute([':id' => $idProduk]);
        $stok = (int) $stmt->fetchColumn();

        if ($stok === 0 && $statusSekarang === 'aktif') {
            self::setStatus($pdo, $idProduk, 'habis');
        } elseif ($stok > 0 && $statusSekarang === 'habis') {
            self::setStatus($pdo, $idProduk, 'aktif');
        }
    }

    private static function setStatus(\PDO $pdo, int $idProduk, string $status): void {
        $pdo->prepare('UPDATE produk SET status_produk = :s WHERE id_produk = :id')
            ->execute([':s' => $status, ':id' => $idProduk]);
    }

    private static function catatStok(\PDO $pdo, int $idVarian, int $userId, string $jenis, int $perubahan, int $sebelum, int $sesudah, string $keterangan): void {
        $pdo->prepare(
            'INSERT INTO stok_log
                (id_varian_produk, id_user, jenis_perubahan, jumlah_perubahan, stok_sebelum, stok_sesudah, keterangan)
             VALUES (:v, :u, :j, :p, :sb, :sa, :k)'
        )->execute([
            ':v' => $idVarian, ':u' => $userId, ':j' => $jenis, ':p' => $perubahan,
            ':sb' => $sebelum, ':sa' => $sesudah, ':k' => mb_substr($keterangan, 0, 255),
        ]);
    }
}
