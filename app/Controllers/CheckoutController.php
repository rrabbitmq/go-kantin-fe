<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Antrian;
use App\Models\Keranjang;

final class CheckoutController extends Controller
{
    public function index(): void
    {
        $this->requireRole('pembeli');

        $userId = (int) Auth::id();
        $cartItems = Keranjang::itemsForUser($userId);
        $slots = Antrian::slots();
        $currentSlot = Antrian::current();
        $queueThreshold = 30;
        $currentQueue = $currentSlot ? (int) $currentSlot['queue_count'] : 0;

        $subtotal = 0.0;
        foreach ($cartItems as $item) {
            $subtotal += (float) $item['line_total'];
        }
        $shipping = $cartItems ? 5000 : 0;
        $total = $subtotal + $shipping;

        $this->render('pembeli/checkout', [
            'pageTitle' => 'Checkout - Go Kantin',
            'activeNav' => 'cart',
            'currentUser' => $this->currentUserForLayout(),
            'cartCount' => Keranjang::countForUser($userId),
            'cartItems' => $cartItems,
            'slots' => $slots,
            'currentSlot' => $currentSlot,
            'queueThreshold' => $queueThreshold,
            'currentQueue' => $currentQueue,
            'subtotal' => $subtotal,
            'shipping' => $shipping,
            'total' => $total,
        ]);
    }
}
