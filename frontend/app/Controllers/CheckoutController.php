<?php
 
namespace App\Controllers;
 
use App\Core\Controller;
use App\Models\Antrian;
use App\Models\Keranjang;
use App\Models\Transaksi;
 
final class CheckoutController extends Controller
{
    public function index(): void
    {
        $this->requireRole('pembeli');

        $userId = (int) Auth::id();
        $cartItems = Keranjang::itemsForUser($userId);
 
        if (!$cartItems) {
            $this->redirect('/user/cart.php');
            return;
        }
 
        $slots = Antrian::slots();
        $currentSlot = Antrian::current();
        if (class_exists(Antrian::class)) {
            $slots = Antrian::slots();
            $currentSlot = Antrian::current();
        }
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
 
    /** Membuat pesanan dari keranjang (POST /user/checkout.php, field opsional: catatan). */
    public function store(): void
    {
        $this->requireRole('pembeli');
 
        $note = trim((string) ($_POST['catatan'] ?? ''));
        $res = Transaksi::checkout($note);
 
        if (!$res['ok']) {
            $errors = array_map('strval', array_values($res['errors']));
            $this->flash('error', $errors ? implode(' ', $errors) : ($res['message'] ?? 'Checkout gagal.'));
            $this->redirect('/user/checkout.php');
            return;
        }
 
        $orders = $res['data']['orders'] ?? [];
        $this->flash('success', 'Pesanan berhasil dibuat.');
 
        // Satu penjual = satu pesanan -> langsung ke status. Beberapa penjual -> ke riwayat.
        $this->redirect(count($orders) === 1
            ? '/user/status-pesanan.php?id=' . (int) $orders[0]['id']
            : '/user/riwayat-pesanan.php');
    }
}