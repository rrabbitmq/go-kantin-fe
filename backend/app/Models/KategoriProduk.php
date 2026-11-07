<?php

namespace App\Models;

use App\Core\Database;

final class KategoriProduk
{
    public static function all(): array
    {
        $pdo = Database::connection();
        if ($pdo) {
            try {
                $rows = $pdo->query(
                    'SELECT id_kategori_produk AS id,
                            nama_kategori AS name,
                            lower(replace(nama_kategori, \' \', \'-\')) AS slug,
                            deskripsi AS description,
                            status_aktif
                     FROM kategori_produk
                     WHERE status_aktif = TRUE
                     ORDER BY id_kategori_produk'
                )->fetchAll();

                if ($rows) {
                    return array_map(static function (array $row): array {
                        $slug = $row['slug'] ?: 'makanan';
                        $row['icon_path'] = self::iconForSlug($slug);
                        $row['filter_class'] = gk_filter_class_for_category($slug);
                        return $row;
                    }, $rows);
                }
            } catch (\Throwable $e) {
                // fallback
            }
        }

        return self::fallback();
    }

    public static function find(int $id): ?array
    {
        foreach (self::all() as $cat) {
            if ((int) $cat['id'] === $id) {
                return $cat;
            }
        }

        return null;
    }

    private static function iconForSlug(string $slug): string
    {
        $map = [
            'makanan' => '/img/categories/2.png',
            'minuman' => '/img/categories/3.png',
            'snack' => '/img/categories/4.png',
        ];

        return $map[$slug] ?? '/img/categories/2.png';
    }

    private static function fallback(): array
    {
        return [
            [
                'id' => 1,
                'name' => 'Makanan',
                'slug' => 'makanan',
                'description' => 'Menu utama siap santap di kantin.',
                'icon_path' => '/img/categories/2.png',
                'filter_class' => 'main-dishes',
            ],
            [
                'id' => 2,
                'name' => 'Minuman',
                'slug' => 'minuman',
                'description' => 'Minuman segar dan hangat.',
                'icon_path' => '/img/categories/3.png',
                'filter_class' => 'drinks',
            ],
            [
                'id' => 3,
                'name' => 'Snack',
                'slug' => 'snack',
                'description' => 'Cemilan ringan favorit mahasiswa.',
                'icon_path' => '/img/categories/4.png',
                'filter_class' => 'desserts',
            ],
        ];
    }
}
