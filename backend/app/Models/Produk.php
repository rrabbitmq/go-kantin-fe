<?php

namespace App\Models;

use App\Core\Database;
use PDO;

final class Produk
{
    public static function all(?int $categoryId = null): array
    {
        $pdo = Database::connection();
        if ($pdo) {
            try {
                if ($categoryId) {
                    $stmt = $pdo->prepare(
                        'SELECT p.id_produk AS id,
                                p.id_kategori_produk AS category_id,
                                p.nama_produk AS name,
                                p.deskripsi AS description,
                                COALESCE(p.gambar, \'/img/menu/1.jpg\') AS image_path,
                                COALESCE(MIN(v.harga_satuan), 0) AS price,
                                k.nama_kategori AS category_name,
                                lower(replace(k.nama_kategori, \' \', \'-\')) AS category_slug
                         FROM produk p
                         JOIN kategori_produk k ON k.id_kategori_produk = p.id_kategori_produk
                         LEFT JOIN varian_produk v ON v.id_produk = p.id_produk AND v.status_aktif = TRUE
                         WHERE p.status_produk = \'aktif\'
                           AND p.id_kategori_produk = ?
                         GROUP BY p.id_produk, k.nama_kategori
                         ORDER BY p.id_produk'
                    );
                    $stmt->execute([$categoryId]);
                } else {
                    $stmt = $pdo->query(
                        'SELECT p.id_produk AS id,
                                p.id_kategori_produk AS category_id,
                                p.nama_produk AS name,
                                p.deskripsi AS description,
                                COALESCE(p.gambar, \'/img/menu/1.jpg\') AS image_path,
                                COALESCE(MIN(v.harga_satuan), 0) AS price,
                                k.nama_kategori AS category_name,
                                lower(replace(k.nama_kategori, \' \', \'-\')) AS category_slug
                         FROM produk p
                         JOIN kategori_produk k ON k.id_kategori_produk = p.id_kategori_produk
                         LEFT JOIN varian_produk v ON v.id_produk = p.id_produk AND v.status_aktif = TRUE
                         WHERE p.status_produk = \'aktif\'
                         GROUP BY p.id_produk, k.nama_kategori
                         ORDER BY p.id_produk'
                    );
                }

                $rows = $stmt->fetchAll();
                if ($rows) {
                    return array_map([self::class, 'normalize'], $rows);
                }
            } catch (\Throwable $e) {
                // fallback
            }
        }

        $products = self::fallback();
        if ($categoryId) {
            $products = array_values(array_filter(
                $products,
                static fn(array $p): bool => (int) $p['category_id'] === $categoryId
            ));
        }

        return $products;
    }

    public static function find(int $id): ?array
    {
        $pdo = Database::connection();
        if ($pdo) {
            try {
                $stmt = $pdo->prepare(
                    'SELECT p.id_produk AS id,
                            p.id_kategori_produk AS category_id,
                            p.nama_produk AS name,
                            p.deskripsi AS description,
                            COALESCE(p.gambar, \'/img/menu/1.jpg\') AS image_path,
                            COALESCE(MIN(v.harga_satuan), 0) AS price,
                            k.nama_kategori AS category_name,
                            lower(replace(k.nama_kategori, \' \', \'-\')) AS category_slug
                     FROM produk p
                     JOIN kategori_produk k ON k.id_kategori_produk = p.id_kategori_produk
                     LEFT JOIN varian_produk v ON v.id_produk = p.id_produk AND v.status_aktif = TRUE
                     WHERE p.id_produk = ?
                     GROUP BY p.id_produk, k.nama_kategori
                     LIMIT 1'
                );
                $stmt->execute([$id]);
                $row = $stmt->fetch();
                if ($row) {
                    return self::normalize($row);
                }
            } catch (\Throwable $e) {
                // fallback
            }
        }

        foreach (self::fallback() as $product) {
            if ((int) $product['id'] === $id) {
                return $product;
            }
        }

        return null;
    }

    public static function bestsellers(int $limit = 6): array
    {
        $products = array_values(array_filter(
            self::all(),
            static fn(array $p): bool => !empty($p['is_bestseller'])
        ));

        if ($products) {
            return array_slice($products, 0, $limit);
        }

        return array_slice(self::all(), 0, $limit);
    }

    public static function related(int $excludeId, int $limit = 6): array
    {
        $products = array_values(array_filter(
            self::all(),
            static fn(array $p): bool => (int) $p['id'] !== $excludeId
        ));

        return array_slice($products, 0, $limit);
    }

    private static function normalize(array $row): array
    {
        $slug = $row['category_slug'] ?? 'makanan';
        $row['id'] = (int) $row['id'];
        $row['category_id'] = (int) $row['category_id'];
        $row['price'] = (float) $row['price'];
        $row['rating_count'] = (int) ($row['rating_count'] ?? 0);
        $row['is_vegan'] = filter_var($row['is_vegan'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $row['is_bestseller'] = filter_var($row['is_bestseller'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $row['ingredients'] = $row['ingredients'] ?? ($row['description'] ?? '');
        $row['filter_class'] = $row['filter_class'] ?? gk_filter_class_for_category($slug);

        return $row;
    }

    private static function fallback(): array
    {
        $cats = [];
        foreach (KategoriProduk::all() as $c) {
            $cats[$c['id']] = $c;
        }

        $rows = [
            [1, 1, 'Keju Goreng Madu', 14000, '/img/menu/1.jpg', 'Snack favorit dengan rasa manis gurih', 'keju, madu, tepung', false, 12, true],
            [2, 1, 'Salmon Asap', 19000, '/img/menu/4.jpg', 'Salmon asap lembut dengan topping segar', 'tomat, nori, keju feta', true, 4, true],
            [3, 1, 'Ikan Krispi', 4000, '/img/menu/3.jpg', 'Ikan goreng renyah', 'ikan, tepung, rempah', false, 8, false],
            [4, 2, 'Sup Telur', 11000, '/img/menu/4.jpg', 'Hangat dan mengenyangkan', 'telur, kaldu, daun bawang', false, 6, false],
            [5, 3, 'Carpaccio Ikan', 7990, '/img/menu/2.jpg', 'Hidangan segar siap saji', 'ikan, lemon, minyak zaitun', false, 5, true],
            [6, 2, 'Keju Goreng Madu Minuman', 14000, '/img/menu/6.jpg', 'Varian minuman manis', 'susu, keju, madu', false, 3, false],
            [7, 1, 'Sup Telur Spesial', 11000, '/img/menu/7.jpg', 'Sup telur porsi kantin', 'telur, kaldu', false, 4, false],
            [8, 3, 'Carpaccio Ringan', 6000, '/img/menu/8.jpg', 'Porsi snack ringan', 'ikan, saus', false, 2, false],
            [9, 3, 'Keju Goreng Madu Snack', 14000, '/img/menu/9.jpg', 'Porsi snack keju madu', 'keju, madu', false, 7, false],
            [10, 2, 'Carpaccio Minuman', 6000, '/img/menu/10.jpg', 'Minuman khusus kantin', 'es, sirup', false, 1, false],
            [13, 3, 'Oreo Soft Cake', 3000, '/img/menu/1.jpg', 'Kue lembut rasa oreo', 'oreo, tepung, susu', false, 15, true],
            [14, 2, 'Air Mineral', 4000, '/img/menu/4.jpg', 'Air mineral dingin/biasa', 'air mineral', true, 20, false],
            [15, 3, 'Kulit Crispy', 14000, '/img/menu/1.jpg', 'Kulit crispy renyah', 'kulit, bumbu', false, 10, true],
            [16, 1, 'Tumis Kacang', 9000, '/img/menu/2.jpg', 'Tumis kacang gurih', 'kacang, bawang', true, 9, true],
            [17, 1, 'Tahu Isi Pedas', 9000, '/img/menu/3.jpg', 'Tahu isi pedas', 'tahu, cabai', true, 11, true],
            [18, 2, 'Kopi', 4000, '/img/menu/4.jpg', 'Kopi panas kantin', 'kopi, gula', false, 18, true],
        ];

        $out = [];
        foreach ($rows as $r) {
            $cat = $cats[$r[1]] ?? ['name' => 'Menu', 'slug' => 'makanan', 'filter_class' => 'main-dishes'];
            $out[] = self::normalize([
                'id' => $r[0],
                'category_id' => $r[1],
                'name' => $r[2],
                'price' => $r[3],
                'image_path' => $r[4],
                'description' => $r[5],
                'ingredients' => $r[6],
                'is_vegan' => $r[7],
                'rating_count' => $r[8],
                'is_bestseller' => $r[9],
                'category_name' => $cat['name'],
                'category_slug' => $cat['slug'],
                'filter_class' => $cat['filter_class'],
            ]);
        }

        return $out;
    }
}
