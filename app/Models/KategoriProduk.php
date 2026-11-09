<?php
 
namespace App\Models;
 
use App\Core\ApiClient;
 
final class KategoriProduk
{
    public static function all(): array
    {
        $res = ApiClient::get('/kategori');
        if (!$res['ok'] || !is_array($res['data'])) {
            return [];
        }
 
        return array_map(static function (array $c): array {
            $slug = (string) ($c['slug'] ?? '');
 
            return [
                'id' => (int) $c['id'],
                'name' => $c['name'],
                'description' => $c['description'] ?? '',
                'slug' => $slug,
                'icon_path' => self::iconForSlug($slug),
                'filter_class' => gk_filter_class_for_category($slug),
            ];
        }, $res['data']);
    }
 
    public static function find(int $id): ?array
    {
        foreach (self::all() as $cat) {
            if ($cat['id'] === $id) {
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
}