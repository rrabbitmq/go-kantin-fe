<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Keranjang;
use App\Models\Produk;

final class MenuController extends Controller
{
    public function index(): void
    {
        $this->requireRole('pembeli');

        $categoryId = isset($_GET['kategori']) ? (int) $_GET['kategori'] : null;

        $this->render('pembeli/menu', [
            'pageTitle' => 'Menu - Go Kantin',
            'activeNav' => 'menu',
            'currentUser' => $this->currentUserForLayout(),
            'cartCount' => Keranjang::countForUser((int) Auth::id()),
            'products' => Produk::all($categoryId ?: null),
        ]);
    }
}
