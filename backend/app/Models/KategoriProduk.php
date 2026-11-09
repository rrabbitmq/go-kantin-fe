<?php
 
namespace App\Models;
 
use App\Core\Database;
 
final class KategoriProduk
{
    public static function all(): array
    {
        $rows = Database::connection()->query(
            "SELECT id_kategori_produk AS id,
                    nama_kategori AS name,
                    lower(replace(nama_kategori, ' ', '-')) AS slug,
                    COALESCE(deskripsi, '') AS description
             FROM kategori_produk
             WHERE status_aktif = TRUE
             ORDER BY id_kategori_produk"
        )->fetchAll();
 
        foreach ($rows as &$row) {
            $row['id'] = (int) $row['id'];
        }
        unset($row);
 
        return $rows;
    }
 
    public static function find(int $id): ?array
    {
        foreach (self::all() as $category) {
            if ($category['id'] === $id) {
                return $category;
            }
        }
 
        return null;
    }
}