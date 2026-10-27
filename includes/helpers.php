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

function gk_order_status_label(string $status): string
{
    $map = [
        'processing' => 'Sedang Diproses',
        'ready' => 'Siap Ambil',
        'done' => 'Selesai',
    ];
    return $map[$status] ?? $status;
}

function gk_order_badge_class(string $status): string
{
    $map = [
        'processing' => 'gk-badge-warn',
        'ready' => 'gk-badge-ready',
        'done' => 'gk-badge-done',
    ];
    return $map[$status] ?? 'gk-badge-warn';
}

function gk_filter_class_for_category(string $slug): string
{
    $map = [
        'makanan' => 'main-dishes',
        'minuman' => 'drinks',
        'snack' => 'desserts',
    ];
    return $map[$slug] ?? 'main-dishes';
}
