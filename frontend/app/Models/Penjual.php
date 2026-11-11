<?php

namespace App\Models;

use App\Core\ApiClient;
use App\Core\Auth;

/** untuk Data dasboard/atrian penjual diambil dari API backend, hasil: array ApiClient (ok, message, data)*/
final class Penjual {
    public static function dashboar(): array {
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
}