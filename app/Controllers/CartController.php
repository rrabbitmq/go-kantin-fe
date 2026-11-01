<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Keranjang;

final class CartController extends Controller
{
    public function index(): void
    {
        $this->requireRole('pembeli');

        $userId = (int) Auth::id();
        $cartItems = Keranjang::itemsForUser($userId);
        $subtotal = 0.0;
        foreach ($cartItems as $item) {
            $subtotal += (float) $item['line_total'];
        }
        $shipping = $cartItems ? 5000 : 0;
        $total = $subtotal + $shipping;

        $this->render('pembeli/cart', [
            'pageTitle' => 'Keranjang - Go Kantin',
            'activeNav' => 'cart',
            'currentUser' => $this->currentUserForLayout(),
            'cartCount' => Keranjang::countForUser($userId),
            'cartItems' => $cartItems,
            'subtotal' => $subtotal,
            'shipping' => $shipping,
            'total' => $total,
        ]);
    }
}
