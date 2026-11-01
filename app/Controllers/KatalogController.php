<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\KategoriProduk;
use App\Models\Keranjang;
use App\Models\Produk;
use App\Core\Auth;

final class KatalogController extends Controller
{
    public function index(): void
    {
        $this->requireRole('pembeli');

        $this->render('pembeli/katalog', [
            'pageTitle' => 'Katalog - Go Kantin',
            'activeNav' => 'menu',
            'currentUser' => $this->currentUserForLayout(),
            'cartCount' => Keranjang::countForUser((int) Auth::id()),
            'categories' => KategoriProduk::all(),
            'bestsellers' => Produk::bestsellers(6),
        ]);
    }
}
