<?php

namespace App\Models;

use App\Core\ApiClient;

final class KategoriPenjual {
    public static function all(): array {
        $res = ApiClient::get('/kategori-penjual');
        if (!$res['ok'] || !is_array($res['data'])) {
            return [];
        }

        return array_map(static fn (array $c): array => [
            'id' => (int) $c['id'],
            'name' => $c['name'],
            'description' => $c['description'] ?? '',
        ], $res['data']);
    }
}