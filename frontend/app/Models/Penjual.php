<?php

namespace App\Models;

use App\Core\ApiClient;
use App\Core\Auth;

/** untuk Data dasboard/atrian penjual diambil dari API backend, hasil: array ApiClient (ok, message, data)*/
final class Penjual {
    public static function dashboard(): array {
        return ApiClient::get('/penjual/dashboard', Auth::token());
    }

    /** untuk Antrean berjalan (belum selesai/dibatalkan), terlama dulu. */
    public static function antrian():array {
        return ApiClient::get('/penjual/pesanan?status=aktif', Auth::token());
    }

    /** untuk Semua pesanan (riwayat), terbaru dulu. */
    public static function semuaPesanan(): array {
        return ApiClient::get('/penjual/pesanan?status=semua&limit=200', Auth::token());
    }

    /** untuk $aksi: konfirmasi | proses | siap | selesai | batal */
    public static function ubahStatus(int $id, string $aksi): array {
        return ApiClient::request('PUT', '/penjual/pesanan', ['id' => $id, 'aksi' => $aksi], Auth::token());
    }

    public static function menu(): array {
        return ApiClient::get('/penjual/menu', Auth::token());
    }

    public static function simpanMenu(array $fields, ?array $upload): array {
        if ($upload && ($upload['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            $fields['gambar'] = new \CURLFile (
                (string) $upload['tmp_name'],
                (string) ($upload['type'] ?? ''),
                (string) ($upload['name'] ?? 'gambar'),
            );
        }
        return ApiClient::postForm('/penjual/menu', $fields, Auth::token());
    }

    public static function nonaktifkanMenu(int $id): array {
        return ApiClient::request('DELETE', '/penjual/menu?id=' . $id, [], Auth::token());
    }

    public static function stokLog(): array {
        return ApiClient::get('/penjual/stok', Auth::token());
    }

    public static function ubahStok(int $idVarian, string $jenis, int $jumlah, string $keterangan = ''): array {
        return ApiClient::post('/penjual/stok', [
            'id_varian' => $idVarian,
            'jenis' => $jenis,
            'jumlah' => $jumlah,
            'keterangan' => $keterangan,
        ], Auth::token());
    }
}