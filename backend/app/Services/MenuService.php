<?php

namespace App\Services;

use App\Core\Database;
use App\Core\HttpException;
use App\Core\Media;

/**
 * Tambah, ubah, nonaktifkan menu, dan sesuaikan stok.
 * Setiap menu memiliki satu varian "Reguler" yang menyimpan harga dan stok.
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
            Media::hapus($gambarBaru); // jangan tinggalkan file lama
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

            // Status ikut menyesuaikan: stok habis -> "habis", terisi lagi -> "aktif"
            if ($baru === 0 && $row['status_produk'] === 'aktif') {
                self::setStatus($pdo, (int) $row['id_produk'], 'habis');
            } elseif ($baru > 0 && $row['status_produk'] === 'habis') {
                self::setStatus($pdo, (int) $row['id_produk'], 'aktif');
            }

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

        $harga = filter_var($in['harga'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 10000000]]);
        if ($harga === false || $harga === null) {
            $err['harga'] = 'Harga harus berupa angka 0 - 10.000.000.';
        }

        $stok = filter_var($in['stok'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 100000]]);
        if ($stok === false || $stok === null) {
            $err['stok'] = 'Stok harus berupa angka 0 - 100000.';
        }

        $status = (string) ($in['status'] ?? 'aktif');
        if (!in_array($status, self::STATUS, true)) {
            $err['status'] = 'Status tidak valid.';
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

        return [
            'nama' => $nama, 'deskripsi' => $deskripsi, 'kategori' => (int) $kategori,
            'harga' => (int) $harga, 'stok' => (int) $stok, 'status' => $status,
        ];
    }

    private static function tambah(\PDO $pdo, int $profilId, int $userId, array $d, ?string $gambar): array {
        $status = ($d['stok'] === 0 && $d['status'] === 'aktif') ? 'habis' : $d['status'];

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

        $stmt = $pdo->prepare(
            "INSERT INTO varian_produk (id_produk, nama_varian, harga_satuan, stok, batas_stok_minimum)
             VALUES (:p, 'Reguler', :h, :s, 5)
             RETURNING id_varian_produk"
        );
        $stmt->execute([':p' => $idProduk, ':h' => $d['harga'], ':s' => $d['stok']]);
        $idVarian = (int) $stmt->fetchColumn();

        if ($d['stok'] > 0) {
            self::catatStok($pdo, $idVarian, $userId, 'masuk', $d['stok'], 0, $d['stok'], 'Stok awal');
        }

        return ['id' => $idProduk, 'pesan' => 'Menu ditambahkan.'];
    }

    private static function ubah(\PDO $pdo, int $profilId, int $userId, int $id, array $d, ?string $gambarBaru): array {
        $stmt = $pdo->prepare(
            'SELECT gambar FROM produk WHERE id_produk = :id AND id_profil_penjual = :p FOR UPDATE'
        );
        $stmt->execute([':id' => $id, ':p' => $profilId]);
        $produk = $stmt->fetch();
        if (!$produk) {
            throw new HttpException(404, 'Menu tidak ditemukan.');
        }

        // Varian utama = varian aktif pertama
        $stmt = $pdo->prepare(
            'SELECT id_varian_produk, stok FROM varian_produk
             WHERE id_produk = :id AND status_aktif = TRUE
             ORDER BY id_varian_produk LIMIT 1 FOR UPDATE'
        );
        $stmt->execute([':id' => $id]);
        $varian = $stmt->fetch();
        if (!$varian) {
            throw new HttpException(409, 'Menu ini belum punya varian aktif.');
        }

        $status = ($d['stok'] === 0 && $d['status'] === 'aktif') ? 'habis' : $d['status'];

        $sets = 'id_kategori_produk = :k, nama_produk = :n, deskripsi = :d, status_produk = :s';
        $params = [
            ':k' => $d['kategori'], ':n' => $d['nama'],
            ':d' => $d['deskripsi'] !== '' ? $d['deskripsi'] : null,
            ':s' => $status, ':id' => $id,
        ];
        if ($gambarBaru !== null) {
            $sets .= ', gambar = :g';
            $params[':g'] = $gambarBaru;
        }
        $pdo->prepare("UPDATE produk SET $sets WHERE id_produk = :id")->execute($params);

        $lama = (int) $varian['stok'];
        $pdo->prepare('UPDATE varian_produk SET harga_satuan = :h, stok = :s WHERE id_varian_produk = :v')
            ->execute([':h' => $d['harga'], ':s' => $d['stok'], ':v' => (int) $varian['id_varian_produk']]);

        if ($d['stok'] !== $lama) {
            self::catatStok($pdo, (int) $varian['id_varian_produk'], $userId, 'penyesuaian',
                $d['stok'] - $lama, $lama, $d['stok'], 'Diubah dari form menu');
        }

        return [
            'id' => $id,
            'pesan' => 'Menu diperbarui.',
            'gambar_lama' => $gambarBaru !== null ? $produk['gambar'] : null,
        ];
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