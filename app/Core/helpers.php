<?php

function gk_h(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function gk_format_rupiah($amount, bool $withSpace = false): string
{
    $n = (int) round((float) $amount);
    $formatted = number_format($n, 0, ',', '.');
    return $withSpace ? 'Rp ' . $formatted : 'Rp' . $formatted;
}

function gk_avatar_letter(string $name): string
{
    $name = trim($name);
    return $name !== '' ? strtoupper(mb_substr($name, 0, 1)) : '?';
}

function gk_queue_label(int $count, int $threshold = 30): string
{
    if ($count > $threshold) {
        return 'Ramai';
    }
    if ($count >= 15) {
        return 'Lancar';
    }
    return 'Sepi';
}

function gk_normalize_order_status(string $status): string
{
    $map = [
        'menunggu_pembayaran' => 'processing',
        'dibayar' => 'processing',
        'diproses' => 'processing',
        'siap_diambil' => 'ready',
        'selesai' => 'done',
        'dibatalkan' => 'done',
        'processing' => 'processing',
        'ready' => 'ready',
        'done' => 'done',
    ];

    return $map[$status] ?? $status;
}

function gk_order_status_label(string $status): string
{
    $normalized = gk_normalize_order_status($status);
    $map = [
        'processing' => 'Sedang Diproses',
        'ready' => 'Siap Ambil',
        'done' => 'Selesai',
        'menunggu_pembayaran' => 'Menunggu Pembayaran',
        'dibayar' => 'Dibayar',
        'diproses' => 'Sedang Diproses',
        'siap_diambil' => 'Siap Ambil',
        'selesai' => 'Selesai',
        'dibatalkan' => 'Dibatalkan',
    ];

    return $map[$status] ?? ($map[$normalized] ?? $status);
}

function gk_order_badge_class(string $status): string
{
    $normalized = gk_normalize_order_status($status);
    $map = [
        'processing' => 'gk-badge-warn',
        'ready' => 'gk-badge-ready',
        'done' => 'gk-badge-done',
    ];

    return $map[$normalized] ?? 'gk-badge-warn';
}

function gk_filter_class_for_category(string $slug): string
{
    $map = [
        'makanan' => 'main-dishes',
        'minuman' => 'drinks',
        'snack' => 'desserts',
    ];

    return $map[strtolower($slug)] ?? 'main-dishes';
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

function gk_flash(?string $key = null, $default = null)
{
    if (!isset($_SESSION['_flash'])) {
        return $key === null ? [] : $default;
    }

    if ($key === null) {
        $all = $_SESSION['_flash'];
        unset($_SESSION['_flash']);
        return $all;
    }

    if (!array_key_exists($key, $_SESSION['_flash'])) {
        return $default;
    }

    $value = $_SESSION['_flash'][$key];
    unset($_SESSION['_flash'][$key]);
    return $value;
}

function gk_flash_set(string $key, $value): void
{
    $_SESSION['_flash'][$key] = $value;
}

function gk_csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['_csrf'];
}

function gk_csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . gk_h(gk_csrf_token()) . '">';
}

function gk_csrf_valid(): bool
{
    $token = (string) ($_POST['_csrf'] ?? '');

    return $token !== '' && hash_equals(gk_csrf_token(), $token);
}
