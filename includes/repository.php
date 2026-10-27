<?php

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';

function gk_fallback_categories(): array
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

function gk_fallback_products(): array
{
    $cats = [];
    foreach (gk_fallback_categories() as $c) {
        $cats[$c['id']] = $c;
    }

    $rows = [
        [1, 1, 'Keju Goreng Madu', 14000, '/img/menu/1.jpg', 'Snack favorit dengan rasa manis gurih', 'keju, madu, tepung', false, 12, true],
        [2, 1, 'Salmon Asap', 19000, '/img/menu/4.jpg', 'Salmon asap lembut dengan topping segar', 'tomat, nori, keju feta, jamur, bihun, jagung, udang', true, 4, true],
        [3, 1, 'Ikan Krispi', 4000, '/img/menu/3.jpg', 'Ikan goreng renyah', 'ikan, tepung, rempah', false, 8, false],
        [4, 2, 'Sup Telur', 11000, '/img/menu/4.jpg', 'Hangat dan mengenyangkan', 'telur, kaldu, daun bawang', false, 6, false],
        [5, 3, 'Carpaccio Ikan', 7990, '/img/menu/2.jpg', 'Hidangan segar siap saji', 'ikan, lemon, minyak zaitun', false, 5, true],
        [6, 2, 'Keju Goreng Madu Minuman', 14000, '/img/menu/6.jpg', 'Varian minuman manis', 'susu, keju, madu', false, 3, false],
        [7, 1, 'Sup Telur Spesial', 11000, '/img/menu/7.jpg', 'Sup telur porsi kantin', 'telur, kaldu', false, 4, false],
        [8, 3, 'Carpaccio Ringan', 6000, '/img/menu/8.jpg', 'Porsi snack ringan', 'ikan, saus', false, 2, false],
        [9, 3, 'Keju Goreng Madu Snack', 14000, '/img/menu/9.jpg', 'Porsi snack keju madu', 'keju, madu', false, 7, false],
        [10, 2, 'Carpaccio Minuman', 6000, '/img/menu/10.jpg', 'Minuman khusus kantin', 'es, sirup', false, 1, false],
        [11, 3, 'Carpaccio Extra', 7000, '/img/menu/11.jpg', 'Snack extra', 'ikan', false, 2, false],
        [12, 3, 'Carpaccio Jumbo', 12000, '/img/menu/12.jpg', 'Porsi jumbo', 'ikan', false, 3, false],
        [13, 3, 'Oreo Soft Cake', 3000, '/img/menu/1.jpg', 'Kue lembut rasa oreo', 'oreo, tepung, susu', false, 15, true],
        [14, 2, 'Air Mineral', 4000, '/img/menu/4.jpg', 'Air mineral dingin/biasa', 'air mineral', true, 20, false],
        [15, 3, 'Kulit Crispy', 14000, '/img/menu/1.jpg', 'Kulit crispy renyah', 'kulit, bumbu', false, 10, true],
        [16, 1, 'Tumis Kacang', 9000, '/img/menu/2.jpg', 'Tumis kacang gurih', 'kacang, bawang', true, 9, true],
        [17, 1, 'Tahu Isi Pedas', 9000, '/img/menu/3.jpg', 'Tahu isi pedas', 'tahu, cabai', true, 11, true],
        [18, 2, 'Kopi', 4000, '/img/menu/4.jpg', 'Kopi panas kantin', 'kopi, gula', false, 18, true],
    ];

    $out = [];
    foreach ($rows as $r) {
        $cat = $cats[$r[1]];
        $out[] = [
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
        ];
    }
    return $out;
}

function gk_fallback_user(int $id): ?array
{
    if ($id !== 1) {
        return null;
    }
    return [
        'id' => 1,
        'name' => 'Farhan',
        'student_id' => '20241001',
        'avatar_letter' => 'F',
    ];
}

function gk_fallback_cart(int $userId): array
{
    if ($userId !== 1) {
        return [];
    }
    $products = [];
    foreach (gk_fallback_products() as $p) {
        $products[$p['id']] = $p;
    }
    $items = [
        ['id' => 1, 'product_id' => 1, 'quantity' => 1, 'note' => ''],
        ['id' => 2, 'product_id' => 5, 'quantity' => 1, 'note' => ''],
        ['id' => 3, 'product_id' => 4, 'quantity' => 1, 'note' => ''],
    ];
    $out = [];
    foreach ($items as $item) {
        $p = $products[$item['product_id']];
        $out[] = array_merge($item, [
            'user_id' => $userId,
            'name' => $p['name'],
            'price' => $p['price'],
            'image_path' => $p['image_path'],
            'description' => $p['description'],
            'line_total' => $p['price'] * $item['quantity'],
        ]);
    }
    return $out;
}

function gk_fallback_orders(int $userId): array
{
    if ($userId !== 1) {
        return [];
    }
    return [
        [
            'id' => 1,
            'user_id' => 1,
            'order_code' => 'ABC-123-XYZ',
            'status' => 'processing',
            'total_amount' => 11000,
            'eta_minutes' => 15,
            'pickup_label' => 'Ambil di Kantin',
            'pickup_location' => 'Kantin JTI • Lantai 8',
            'created_at' => '2026-10-20 11:42:00',
            'menu_count' => 2,
            'item_count' => 3,
            'thumbs' => ['/img/menu/1.jpg', '/img/menu/4.jpg'],
        ],
        [
            'id' => 2,
            'user_id' => 1,
            'order_code' => 'GK-884-21A',
            'status' => 'ready',
            'total_amount' => 9000,
            'eta_minutes' => 5,
            'pickup_label' => 'Ambil di Kantin',
            'pickup_location' => 'Kantin JTI • Lantai 8',
            'created_at' => '2026-10-20 10:15:00',
            'menu_count' => 1,
            'item_count' => 1,
            'thumbs' => ['/img/menu/2.jpg'],
        ],
        [
            'id' => 3,
            'user_id' => 1,
            'order_code' => 'GK-771-09C',
            'status' => 'done',
            'total_amount' => 29000,
            'eta_minutes' => null,
            'pickup_label' => 'Ambil di Kantin',
            'pickup_location' => 'Kantin JTI • Lantai 8',
            'created_at' => '2026-10-19 12:08:00',
            'menu_count' => 3,
            'item_count' => 4,
            'thumbs' => ['/img/menu/3.jpg', '/img/menu/5.jpg', '/img/menu/6.jpg'],
        ],
        [
            'id' => 4,
            'user_id' => 1,
            'order_code' => 'GK-650-44B',
            'status' => 'done',
            'total_amount' => 8000,
            'eta_minutes' => null,
            'pickup_label' => 'Ambil di Kantin',
            'pickup_location' => 'Kantin JTI • Lantai 8',
            'created_at' => '2026-10-18 13:30:00',
            'menu_count' => 1,
            'item_count' => 2,
            'thumbs' => ['/img/menu/4.jpg'],
        ],
    ];
}

function gk_fallback_order_items(int $orderId): array
{
    $map = [
        1 => [
            ['id' => 1, 'order_id' => 1, 'product_id' => 13, 'product_name' => 'Oreo Soft Cake', 'image_path' => '/img/menu/1.jpg', 'quantity' => 1, 'unit_price' => 3000, 'note' => ''],
            ['id' => 2, 'order_id' => 1, 'product_id' => 14, 'product_name' => 'Air Mineral', 'image_path' => '/img/menu/4.jpg', 'quantity' => 2, 'unit_price' => 4000, 'note' => 'satu dingin, satu biasa'],
        ],
        2 => [
            ['id' => 3, 'order_id' => 2, 'product_id' => 16, 'product_name' => 'Tumis Kacang', 'image_path' => '/img/menu/2.jpg', 'quantity' => 1, 'unit_price' => 9000, 'note' => ''],
        ],
        3 => [
            ['id' => 4, 'order_id' => 3, 'product_id' => 3, 'product_name' => 'Ikan Krispi', 'image_path' => '/img/menu/3.jpg', 'quantity' => 2, 'unit_price' => 4000, 'note' => ''],
            ['id' => 5, 'order_id' => 3, 'product_id' => 5, 'product_name' => 'Carpaccio Ikan', 'image_path' => '/img/menu/5.jpg', 'quantity' => 1, 'unit_price' => 6000, 'note' => ''],
            ['id' => 6, 'order_id' => 3, 'product_id' => 6, 'product_name' => 'Keju Goreng Madu Minuman', 'image_path' => '/img/menu/6.jpg', 'quantity' => 1, 'unit_price' => 14000, 'note' => ''],
        ],
        4 => [
            ['id' => 7, 'order_id' => 4, 'product_id' => 14, 'product_name' => 'Air Mineral', 'image_path' => '/img/menu/4.jpg', 'quantity' => 2, 'unit_price' => 4000, 'note' => ''],
        ],
    ];
    return $map[$orderId] ?? [];
}

function gk_fallback_queue_slots(): array
{
    return [
        ['id' => 1, 'slot_label' => '11.00–11.30', 'slot_value' => '11.00-11.30', 'queue_count' => 8, 'is_current' => false, 'sort_order' => 1],
        ['id' => 2, 'slot_label' => '11.30–12.00', 'slot_value' => '11.30-12.00', 'queue_count' => 18, 'is_current' => false, 'sort_order' => 2],
        ['id' => 3, 'slot_label' => '12.00–12.30', 'slot_value' => '12.00-12.30', 'queue_count' => 35, 'is_current' => true, 'sort_order' => 3],
        ['id' => 4, 'slot_label' => '12.30–13.00', 'slot_value' => '12.30-13.00', 'queue_count' => 22, 'is_current' => false, 'sort_order' => 4],
        ['id' => 5, 'slot_label' => '13.00–13.30', 'slot_value' => '13.00-13.30', 'queue_count' => 12, 'is_current' => false, 'sort_order' => 5],
        ['id' => 6, 'slot_label' => '13.30–14.00', 'slot_value' => '13.30-14.00', 'queue_count' => 6, 'is_current' => false, 'sort_order' => 6],
    ];
}

function gk_get_user(int $id): array
{
    $pdo = gk_db();
    if ($pdo) {
        try {
            $stmt = $pdo->prepare('SELECT id, name, student_id, avatar_letter FROM users WHERE id = ?');
            $stmt->execute([$id]);
            $row = $stmt->fetch();
            if ($row) {
                return $row;
            }
        } catch (Throwable $e) {
            // fall through
        }
    }
    return gk_fallback_user($id) ?? [
        'id' => 1,
        'name' => 'Farhan',
        'student_id' => '20241001',
        'avatar_letter' => 'F',
    ];
}

function gk_get_categories(): array
{
    $pdo = gk_db();
    if ($pdo) {
        try {
            return $pdo->query('SELECT id, name, slug, description, icon_path, filter_class FROM categories ORDER BY id')->fetchAll();
        } catch (Throwable $e) {
            // fall through
        }
    }
    return gk_fallback_categories();
}

function gk_get_category(int $id): ?array
{
    foreach (gk_get_categories() as $cat) {
        if ((int) $cat['id'] === $id) {
            return $cat;
        }
    }
    return null;
}

function gk_normalize_product(array $row): array
{
    $row['id'] = (int) $row['id'];
    $row['category_id'] = (int) $row['category_id'];
    $row['price'] = (float) $row['price'];
    $row['rating_count'] = (int) ($row['rating_count'] ?? 0);
    $row['is_vegan'] = filter_var($row['is_vegan'] ?? false, FILTER_VALIDATE_BOOLEAN);
    $row['is_bestseller'] = filter_var($row['is_bestseller'] ?? false, FILTER_VALIDATE_BOOLEAN);
    return $row;
}

function gk_get_products(?int $categoryId = null): array
{
    $pdo = gk_db();
    if ($pdo) {
        try {
            if ($categoryId) {
                $stmt = $pdo->prepare(
                    'SELECT p.*, c.name AS category_name, c.slug AS category_slug, c.filter_class
                     FROM products p
                     JOIN categories c ON c.id = p.category_id
                     WHERE p.category_id = ?
                     ORDER BY p.id'
                );
                $stmt->execute([$categoryId]);
            } else {
                $stmt = $pdo->query(
                    'SELECT p.*, c.name AS category_name, c.slug AS category_slug, c.filter_class
                     FROM products p
                     JOIN categories c ON c.id = p.category_id
                     ORDER BY p.id'
                );
            }
            return array_map('gk_normalize_product', $stmt->fetchAll());
        } catch (Throwable $e) {
            // fall through
        }
    }

    $products = gk_fallback_products();
    if ($categoryId) {
        $products = array_values(array_filter($products, static function ($p) use ($categoryId) {
            return (int) $p['category_id'] === $categoryId;
        }));
    }
    return $products;
}

function gk_get_product(int $id): ?array
{
    $pdo = gk_db();
    if ($pdo) {
        try {
            $stmt = $pdo->prepare(
                'SELECT p.*, c.name AS category_name, c.slug AS category_slug, c.filter_class
                 FROM products p
                 JOIN categories c ON c.id = p.category_id
                 WHERE p.id = ?'
            );
            $stmt->execute([$id]);
            $row = $stmt->fetch();
            return $row ? gk_normalize_product($row) : null;
        } catch (Throwable $e) {
            // fall through
        }
    }

    foreach (gk_fallback_products() as $p) {
        if ((int) $p['id'] === $id) {
            return $p;
        }
    }
    return null;
}

function gk_get_bestsellers(int $limit = 6): array
{
    $pdo = gk_db();
    if ($pdo) {
        try {
            $stmt = $pdo->prepare(
                'SELECT p.*, c.name AS category_name, c.slug AS category_slug, c.filter_class
                 FROM products p
                 JOIN categories c ON c.id = p.category_id
                 WHERE p.is_bestseller = TRUE
                 ORDER BY p.id
                 LIMIT ?'
            );
            $stmt->bindValue(1, $limit, PDO::PARAM_INT);
            $stmt->execute();
            return array_map('gk_normalize_product', $stmt->fetchAll());
        } catch (Throwable $e) {
            // fall through
        }
    }

    $products = array_values(array_filter(gk_fallback_products(), static function ($p) {
        return !empty($p['is_bestseller']);
    }));
    return array_slice($products, 0, $limit);
}

function gk_get_related_products(int $excludeId, int $limit = 6): array
{
    $products = array_values(array_filter(gk_get_products(), static function ($p) use ($excludeId) {
        return (int) $p['id'] !== $excludeId;
    }));
    return array_slice($products, 0, $limit);
}

function gk_get_cart_items(int $userId): array
{
    $pdo = gk_db();
    if ($pdo) {
        try {
            $stmt = $pdo->prepare(
                'SELECT ci.id, ci.user_id, ci.product_id, ci.quantity, ci.note,
                        p.name, p.price, p.image_path, p.description,
                        (p.price * ci.quantity) AS line_total
                 FROM cart_items ci
                 JOIN products p ON p.id = ci.product_id
                 WHERE ci.user_id = ?
                 ORDER BY ci.id'
            );
            $stmt->execute([$userId]);
            $rows = $stmt->fetchAll();
            foreach ($rows as &$row) {
                $row['id'] = (int) $row['id'];
                $row['product_id'] = (int) $row['product_id'];
                $row['quantity'] = (int) $row['quantity'];
                $row['price'] = (float) $row['price'];
                $row['line_total'] = (float) $row['line_total'];
            }
            unset($row);
            return $rows;
        } catch (Throwable $e) {
            // fall through
        }
    }
    return gk_fallback_cart($userId);
}

function gk_get_cart_count(int $userId): int
{
    $items = gk_get_cart_items($userId);
    $count = 0;
    foreach ($items as $item) {
        $count += (int) $item['quantity'];
    }
    return $count;
}

function gk_get_queue_slots(): array
{
    $pdo = gk_db();
    if ($pdo) {
        try {
            $rows = $pdo->query(
                'SELECT id, slot_label, slot_value, queue_count, is_current, sort_order
                 FROM queue_slots
                 ORDER BY sort_order, id'
            )->fetchAll();
            foreach ($rows as &$row) {
                $row['id'] = (int) $row['id'];
                $row['queue_count'] = (int) $row['queue_count'];
                $row['is_current'] = filter_var($row['is_current'], FILTER_VALIDATE_BOOLEAN);
            }
            unset($row);
            return $rows;
        } catch (Throwable $e) {
            // fall through
        }
    }
    return gk_fallback_queue_slots();
}

function gk_get_current_queue_slot(): ?array
{
    foreach (gk_get_queue_slots() as $slot) {
        if (!empty($slot['is_current'])) {
            return $slot;
        }
    }
    $slots = gk_get_queue_slots();
    return $slots[0] ?? null;
}

function gk_enrich_order(array $order): array
{
    $order['id'] = (int) $order['id'];
    $order['total_amount'] = (float) $order['total_amount'];
    $order['eta_minutes'] = isset($order['eta_minutes']) ? (int) $order['eta_minutes'] : null;

    if (!isset($order['menu_count']) || !isset($order['item_count']) || !isset($order['thumbs'])) {
        $items = gk_get_order_items($order['id']);
        $order['menu_count'] = count($items);
        $order['item_count'] = 0;
        $thumbs = [];
        foreach ($items as $item) {
            $order['item_count'] += (int) $item['quantity'];
            if (!empty($item['image_path'])) {
                $thumbs[] = $item['image_path'];
            }
        }
        $order['thumbs'] = array_values(array_unique($thumbs));
    }

    return $order;
}

function gk_get_orders(int $userId): array
{
    $pdo = gk_db();
    if ($pdo) {
        try {
            $stmt = $pdo->prepare(
                'SELECT id, user_id, order_code, status, total_amount, eta_minutes,
                        pickup_label, pickup_location, created_at
                 FROM orders
                 WHERE user_id = ?
                 ORDER BY created_at DESC, id DESC'
            );
            $stmt->execute([$userId]);
            return array_map('gk_enrich_order', $stmt->fetchAll());
        } catch (Throwable $e) {
            // fall through
        }
    }
    return array_map('gk_enrich_order', gk_fallback_orders($userId));
}

function gk_get_order(int $id): ?array
{
    $pdo = gk_db();
    if ($pdo) {
        try {
            $stmt = $pdo->prepare(
                'SELECT id, user_id, order_code, status, total_amount, eta_minutes,
                        pickup_label, pickup_location, created_at
                 FROM orders
                 WHERE id = ?'
            );
            $stmt->execute([$id]);
            $row = $stmt->fetch();
            return $row ? gk_enrich_order($row) : null;
        } catch (Throwable $e) {
            // fall through
        }
    }

    foreach (gk_fallback_orders(1) as $order) {
        if ((int) $order['id'] === $id) {
            return gk_enrich_order($order);
        }
    }
    return null;
}

function gk_get_order_items(int $orderId): array
{
    $pdo = gk_db();
    if ($pdo) {
        try {
            $stmt = $pdo->prepare(
                'SELECT id, order_id, product_id, product_name, image_path, quantity, unit_price, note
                 FROM order_items
                 WHERE order_id = ?
                 ORDER BY id'
            );
            $stmt->execute([$orderId]);
            $rows = $stmt->fetchAll();
            foreach ($rows as &$row) {
                $row['id'] = (int) $row['id'];
                $row['quantity'] = (int) $row['quantity'];
                $row['unit_price'] = (float) $row['unit_price'];
            }
            unset($row);
            return $rows;
        } catch (Throwable $e) {
            // fall through
        }
    }
    return gk_fallback_order_items($orderId);
}

function gk_format_order_datetime(string $datetime): string
{
    $ts = strtotime($datetime);
    if ($ts === false) {
        return $datetime;
    }
    $months = [
        1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun',
        7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
    ];
    $day = date('j', $ts);
    $month = $months[(int) date('n', $ts)];
    $year = date('Y', $ts);
    $time = date('H:i', $ts);
    return $day . ' ' . $month . ' ' . $year . ' • ' . $time;
}

function gk_ingredient_spans(string $ingredients): string
{
    $parts = array_filter(array_map('trim', explode(',', $ingredients)));
    if (!$parts) {
        return gk_h($ingredients);
    }
    $html = [];
    foreach ($parts as $part) {
        $html[] = '<span>' . gk_h($part) . '</span>';
    }
    return implode(', ', $html);
}
