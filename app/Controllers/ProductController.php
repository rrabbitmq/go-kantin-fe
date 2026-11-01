<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Keranjang;
use App\Models\Produk;

final class ProductController extends Controller
{
    public function show(): void
    {
        $this->requireRole('pembeli');

        $productId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        $product = $productId > 0 ? Produk::find($productId) : null;
        $related = $product ? Produk::related((int) $product['id'], 6) : [];

        $this->render('pembeli/detail-product', [
            'pageTitle' => 'Detail Produk - Go Kantin',
            'activeNav' => 'menu',
            'currentUser' => $this->currentUserForLayout(),
            'cartCount' => Keranjang::countForUser((int) Auth::id()),
            'product' => $product,
            'related' => $related,
        ]);
    }
}
