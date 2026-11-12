<?php

namespace App\Models;

use App\Core\ApiClient;

final class Produk
{
    public static function all(?int $categoryId = null): array
    {
        $path = '/produk' . ($categoryId ? '?kategori=' . $categoryId : '');
        return self::fetchList($path);
    }    

    public static function find(int $id): ?array
    {
        $rows = self::fetchList('/produk?id=' . $id);
        return $rows[0] ?? null;
    }        

    public static function bestsellers(int $limit = 6): array
    {
        return self::fetchList('/produk?terlaris=1&limit=' . max(1, $limit));
    }

    public static function related(int $excludeId, int $limit = 6): array
    {
        $products = array_values(array_filter(
            self::all(),
            static fn(array $p): bool => (int) $p['id'] !== $excludeId
        ));

        return array_slice($products, 0, $limit);
    }

    private static function fetchList(string $path): array
    {
        $res = ApiClient::get($path);
        if (!$res['ok'] || !is_array($res['data'])) {
            return [];
        }

        return array_map([self::class, 'normalize'], $res['data']);
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
            [1, 3, 'Oreo Soft Cake', 3000, '/img/menus/oreo-soft-cake.jpg', 'Kue lembut rasa oreo', 'oreo, tepung, susu', false, 15, true],
            [2, 1, 'Ayam Krispi', 12000, '/img/menus/ayam-krispi.jpg', 'Ayam goreng tepung renyah', 'ayam, tepung, rempah', false, 12, true],
            [3, 2, 'Air Mineral Club', 3000, '/img/menus/air-mineral-club.jpg', 'Air mineral dingin atau biasa', 'air mineral', true, 20, true],
            [4, 2, 'Kapal Api', 4000, '/img/menus/kapal-api.jpg', 'Kopi panas kantin', 'kopi, gula', true, 8, false],
            [6, 2, 'Teh Pucuk', 4000, '/img/menus/teh-pucuk.png', 'Teh dalam kemasan dingin', 'teh, gula', true, 9, true],
            [7, 3, 'Sosis Solo Ayam', 2500, '/img/menus/Sosis%20solo.jpg', 'Sosis solo isi daging gurih', 'daging, kulit dadar', false, 6, false],
            [8, 3, 'Tahu Isi Sayur', 2500, '/img/menus/Tahu%20isi%20sayur.jpg', 'Tahu goreng isi sayuran', 'tahu, wortel, kol', true, 7, false],
            [9, 1, 'Nasi Ayam Geprek', 15000, '/img/menus/nasi-ayam-geprek.jpg', 'Nasi dengan ayam geprek sambal', 'nasi, ayam, sambal', false, 14, true],
            [12, 3, 'Risol Mayo', 3500, '/img/menus/risol-mayo.jpg', 'Risol isi mayones dan telur', 'tepung, mayones, telur', false, 10, true],
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
