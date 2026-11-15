<?php

namespace App\Models;

use App\Core\Database;

final class KategoriPenjual {
    public static function all(): array {
        $rows = Database::connection()->query(
            "SELECT id_kategori_penjual AS id,
                    nama_kategori AS name,
                    COALESCE(deskripsi, '') AS description
            FROM kategori_penjual
            ORDER BY id_kategori_penjual"
        )->fetchAll();

        foreach ($rows as &$row) {
            $row['id'] = (int) $row['id'];
        }
        unset($row);

        return $rows;
    }
}