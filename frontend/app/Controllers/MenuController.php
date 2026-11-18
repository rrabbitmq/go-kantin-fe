<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\KategoriProduk;
use App\Models\Keranjang;
use App\Models\Produk;

final class MenuController extends Controller
{
    private const PER_PAGE = 10;
    private const CATEGORY_ORDER = ['makanan', 'minuman', 'snack'];

    public function index(): void
    {
        $this->requireRole('pembeli');

        $query = trim((string) ($_GET['q'] ?? ''));
        $categoryId = isset($_GET['kategori']) ? (int) $_GET['kategori'] : 0;
        $page = max(1, (int) ($_GET['page'] ?? 1));

        $categories = KategoriProduk::all();
        $bySlug = [];
        foreach ($categories as $category) {
            $bySlug[(string) ($category['slug'] ?? '')] = $category;
        }

        $tabCategories = [];
        foreach (self::CATEGORY_ORDER as $slug) {
            if (isset($bySlug[$slug])) {
                $tabCategories[] = $bySlug[$slug];
            }
        }

        $allProducts = Produk::all();
        $allCount = count($allProducts);

        $categoryCounts = [];
        foreach ($tabCategories as $category) {
            $id = (int) $category['id'];
            $categoryCounts[$id] = count(array_filter(
                $allProducts,
                static fn(array $product): bool => (int) $product['category_id'] === $id
            ));
        }

        $validCategoryIds = array_map(
            static fn(array $category): int => (int) $category['id'],
            $tabCategories
        );
        if ($categoryId > 0 && !in_array($categoryId, $validCategoryIds, true)) {
            $categoryId = 0;
        }

        $filtered = $categoryId > 0
            ? array_values(array_filter(
                $allProducts,
                static fn(array $product): bool => (int) $product['category_id'] === $categoryId
            ))
            : $allProducts;

        if ($query !== '') {
            $needle = mb_strtolower($query);
            $filtered = array_values(array_filter(
                $filtered,
                static fn(array $product): bool => str_contains(mb_strtolower((string) $product['name']), $needle)
                    || str_contains(mb_strtolower((string) ($product['category_name'] ?? '')), $needle)
            ));
        }

        $totalProducts = count($filtered);
        $totalPages = max(1, (int) ceil($totalProducts / self::PER_PAGE));
        if ($page > $totalPages) {
            $page = $totalPages;
        }

        $offset = ($page - 1) * self::PER_PAGE;
        $products = array_slice($filtered, $offset, self::PER_PAGE);

        $this->render('pembeli/menu', [
            'pageTitle' => 'Menu - Go Kantin',
            'activeNav' => 'menu',
            'currentUser' => $this->currentUserForLayout(),
            'cartCount' => Keranjang::countForUser((int) Auth::id()),
            'products' => $products,
            'categories' => $tabCategories,
            'activeCategoryId' => $categoryId,
            'query' => $query,
            'page' => $page,
            'perPage' => self::PER_PAGE,
            'totalPages' => $totalPages,
            'totalProducts' => $totalProducts,
            'allCount' => $allCount,
            'categoryCounts' => $categoryCounts,
        ]);
    }
}
