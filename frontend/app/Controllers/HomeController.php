<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Antrian;
use App\Models\KategoriProduk;
use App\Models\Keranjang;
use App\Models\Produk;
use App\Models\Transaksi;

final class HomeController extends Controller
{
    private const CATEGORY_ORDER = ['makanan', 'minuman', 'snack'];

    public function index(): void
    {
        $this->requireRole('pembeli');

        $userId = (int) Auth::id();
        $query = trim((string) ($_GET['q'] ?? ''));
        $categories = KategoriProduk::all();

        $cartItems = Keranjang::itemsForUser($userId);
        $cartTotal = 0.0;
        foreach ($cartItems as $item) {
            $cartTotal += (float) $item['line_total'];
        }

        $orders = Transaksi::forUser($userId);
        $activeOrder = null;
        foreach ($orders as $order) {
            $status = gk_normalize_order_status((string) ($order['status'] ?? ''));
            if ($status !== 'done') {
                $activeOrder = $order;
                break;
            }
        }

        $this->render('pembeli/beranda', [
            'pageTitle' => 'Beranda - Go Kantin',
            'activeNav' => 'beranda',
            'currentUser' => $this->currentUserForLayout(),
            'cartCount' => Keranjang::countForUser($userId),
            'categories' => $categories,
            'query' => $query,
            'cartItems' => array_slice($cartItems, 0, 3),
            'cartTotal' => $cartTotal,
            'activeOrder' => $activeOrder,
            'menuCount' => count(Produk::all()),
            'queueDensity' => Antrian::kepadatan(),
            'carousels' => $this->buildCarousels($categories, $query),
        ]);
    }

    /** @param array<int, array> $categories */
    private function buildCarousels(array $categories, string $query): array
    {
        if ($query !== '') {
            $needle = mb_strtolower($query);
            $products = array_values(array_filter(
                Produk::all(),
                static fn(array $product): bool => str_contains(mb_strtolower($product['name']), $needle)
                    || str_contains(mb_strtolower((string) ($product['category_name'] ?? '')), $needle)
            ));

            return [[
                'title' => 'Hasil pencarian',
                'subtitle' => 'Menu yang cocok dengan pencarianmu',
                'products' => $products,
                'more_href' => '/user/menu.php',
                'more_label' => 'Selengkapnya',
            ]];
        }

        $bySlug = [];
        foreach ($categories as $category) {
            $bySlug[(string) ($category['slug'] ?? '')] = $category;
        }

        $bestsellers = Produk::bestsellers(8);
        if ($bestsellers === []) {
            $bestsellers = array_slice(Produk::all(), 0, 8);
        }

        $carousels = [[
            'title' => 'Menu Best Seller',
            'subtitle' => 'Rekomendasi menu yang paling banyak disukai dan dibeli',
            'products' => $bestsellers,
            'more_href' => '/user/menu.php',
            'more_label' => 'Selengkapnya',
        ]];

        $copy = [
            'makanan' => 'Hidangan utama yang siap mengenyangkan',
            'minuman' => 'Minuman segar dan hangat pilihan kantin',
            'snack' => 'Camilan ringan untuk teman istirahat',
        ];

        foreach (self::CATEGORY_ORDER as $slug) {
            $category = $bySlug[$slug] ?? null;
            if (!$category) {
                continue;
            }

            $carousels[] = [
                'title' => 'Menu ' . $category['name'],
                'subtitle' => $copy[$slug] ?? (string) ($category['description'] ?? ''),
                'products' => array_slice(Produk::all((int) $category['id']), 0, 8),
                'more_href' => '/user/menu.php?kategori=' . (int) $category['id'],
                'more_label' => 'Selengkapnya',
            ];
        }

        return $carousels;
    }
}
