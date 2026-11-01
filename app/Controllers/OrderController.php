<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Keranjang;
use App\Models\Transaksi;

final class OrderController extends Controller
{
    public function status(): void
    {
        $this->requireRole('pembeli');

        $orderId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        $order = $orderId > 0 ? Transaksi::find($orderId) : Transaksi::find(1);
        $items = $order ? Transaksi::items((int) $order['id']) : [];

        $statusSteps = [
            'received' => ['title' => 'Diterima', 'desc' => 'Pesanan diterima', 'icon' => 'fas fa-check'],
            'processing' => ['title' => 'Dibuat', 'desc' => 'Sedang diproses', 'icon' => 'fas fa-utensils'],
            'ready' => ['title' => 'Siap Ambil', 'desc' => 'Menunggu', 'icon' => 'fas fa-shopping-bag'],
            'done' => ['title' => 'Selesai', 'desc' => 'Menunggu', 'icon' => 'fas fa-check'],
        ];
        $statusOrder = ['received', 'processing', 'ready', 'done'];
        $currentStatus = $order['status'] ?? 'processing';

        if ($currentStatus === 'ready') {
            $currentIndex = 2;
        } elseif ($currentStatus === 'done') {
            $currentIndex = 3;
        } else {
            $currentIndex = 1;
        }

        $this->render('pembeli/status-pesanan', [
            'pageTitle' => 'Status Pesanan — Go Kantin',
            'activeNav' => 'status',
            'currentUser' => $this->currentUserForLayout(),
            'cartCount' => Keranjang::countForUser((int) Auth::id()),
            'order' => $order,
            'items' => $items,
            'statusSteps' => $statusSteps,
            'statusOrder' => $statusOrder,
            'currentIndex' => $currentIndex,
        ]);
    }

    public function riwayat(): void
    {
        $this->requireRole('pembeli');

        $userId = (int) Auth::id();

        $this->render('pembeli/riwayat-pesanan', [
            'pageTitle' => 'Riwayat Pesanan — Go Kantin',
            'activeNav' => 'status',
            'currentUser' => $this->currentUserForLayout(),
            'cartCount' => Keranjang::countForUser($userId),
            'orders' => Transaksi::forUser($userId),
        ]);
    }
}
