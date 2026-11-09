<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\KategoriProduk;
use App\Models\Keranjang;
use App\Models\Produk;
use App\Models\Transaksi;

final class HomeController extends Controller
{
    public function index(): void
    {
        $this->requireRole('pembeli');

        $userId = (int) Auth::id();
        $query = trim((string) ($_GET['q'] ?? ''));
        $products = Produk::bestsellers(8);
        if ($products === []) {
            $products = array_slice(Produk::all(), 0, 8);
        }

        if ($query !== '') {
            $needle = mb_strtolower($query);
            $products = array_values(array_filter(
                Produk::all(),
                static fn(array $product): bool => str_contains(mb_strtolower($product['name']), $needle)
                    || str_contains(mb_strtolower((string) ($product['category_name'] ?? '')), $needle)
            ));
        }

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
            'categories' => KategoriProduk::all(),
            'products' => $products,
            'query' => $query,
            'cartItems' => array_slice($cartItems, 0, 3),
            'cartTotal' => $cartTotal,
            'activeOrder' => $activeOrder,
            'menuCount' => count(Produk::all()),
        ]);
    }
}
