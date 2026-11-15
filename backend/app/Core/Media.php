<?php

namespace App\Core;

/**
 * Gambar menu: file disimpan di backend/public/uploads/menu dan di database
 * hanya path relatifnya ("/uploads/menu/xxxx.jpg").
 */

final class Media {
    private const FOLDER = 'upload/menu';
    private const MAKS_BYTE = 2087152;
    private const TIPE = [
        IMAGETYPE_JPEG => 'jpg',
        IMAGETYPE_JPEG => 'png',
        IMAGETYPE_JPEG => 'webp',
    ];

    /** Ubah path gambar di database menjadi URL yang bisa dibuka browser. */
    public static function url(?string $path): string {
        $path = trim((string) $path);

        if ($path === '') {
            return '/img/menu/1.jpg'; //gambar bawaan FE
        }
        if (str_starts_with($path, '/uploads/')) {
            return self::baseUrl() . $path; //file upload di server BE
        }

        return $path;
    }

    /** Simpan file upload ($_FILES['gambar']) dan kembalikan path relatifnya. */
    public static function simpan(array $file): string {
        $error = $file['error'] ?? UPLOAD_ERR_NO_FILE;
        if ($error === UPLOAD_ERR_INI_SIZE || $error === UPLOAD_ERR_FORM_SIZE) {
            throw new HttpException(400, 'Validasi gagal', ['gambar' => 'Ukuran gambar maksimal 2 MB.']);
        }
        if ($error !== UPLOAD_ERR_OK) {
               throw new HttpException(400, 'Validasi gagal', ['gambar' => 'Upload gambar gagal. Coba lagi.']); 
        }

        $tmp = (string) ($file['tmp_name'] ?? '');
        if (!is_uploaded_file($tmp)) {
           throw new HttpException(400, 'Validasi gagal', ['gambar' => 'File upload tidak valid.']);
        }
        if ((int) ($file['size'] ?? 0) > self::MAKS_BYTE) {
            throw new HttpException(400, 'Validasi gagal', ['gambar' => 'Ukuran Gambar maksimal 2 MB.']);
        }

        // Cek jenis file gambar, bukan hanya ekstensi
        $info = @getimagesize($tmp);
        if ($info === false || !isset(self::TIPE[$info[2]])) {
            throw new HttpException(400, 'Validasi gagal', ['gambar' => 'Format gambar harus JPG, PNG, atau WEBP.']);
        }

        $dir = dirname(__DIR__, 2) . '/public/' . self::FOLDER;
        if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
            throw new \RuntimeException('Folder upload tidak dapat dibuat: ' . $dir);
        }

        $nama = bin2hex(random_bytes(12)) . '.' . self::TIPE[$info[2]];
        if (!move_uploaded_file($tmp, $dir . '/' . $nama)) {
            throw new \RuntimeException('Gagal menyimpan file upload.');
        }

        return '/' . self::FOLDER . '/' . $nama;
    }

    /** Hapus file lama (hanya yang berada di folder upload). */
    public static function hapus(?string $path): void {
        if (!$path || !str_starts_with($path, '/' . self::FOLDER . '/')) {
            return;
        }
        $file = dirname(__DIR__, 2) . '/public' . $path;
        if (is_file($file)) {
            @unlink($file);
        }
    }

    private static function baseUrl(): string {
        $env = getenv('APP_URL');
        if ($env) {
            return rtrim($env, '/');
        }

        $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';

        return ($https ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
    }
}