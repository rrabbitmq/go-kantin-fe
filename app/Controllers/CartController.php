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
 
        $summary = Keranjang::summary();
        $subtotal = $summary['subtotal'];
        $shipping = $summary['biaya_layanan'];
        $total = $subtotal + $shipping;
 
        $this->render('pembeli/cart', [
            'pageTitle' => 'Keranjang - Go Kantin',
            'activeNav' => 'cart',
            'currentUser' => $this->currentUserForLayout(),
            'cartCount' => $summary['jumlah'],
            'cartItems' => $summary['items'],
            'subtotal' => $subtotal,
            'shipping' => $shipping,
            'total' => $total,
        ]);
    }
 
    /**
     * Dipanggil lewat fetch() (halaman keranjang, tombol "Tambah", dll). Selalu membalas JSON.
     * POST: aksi = add | update | remove
     *   add    -> id = id produk,        jumlah
     *   update -> id = id item keranjang, jumlah
     *   remove -> id = id item keranjang
     */
    public function action(): void
    {
        header('Content-Type: application/json; charset=utf-8');
 
        if (!Auth::check() || !Auth::hasRole('pembeli')) {
            http_response_code(401);
            echo json_encode(['ok' => false, 'message' => 'Silakan login sebagai pembeli.']);
            return;
        }
 
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['ok' => false, 'message' => 'Method tidak diizinkan.']);
            return;
        }
 
        $aksi = (string) ($_POST['aksi'] ?? '');
        $id = (int) ($_POST['id'] ?? 0);
        $jumlah = (int) ($_POST['jumlah'] ?? 1);
 
        if ($id < 1) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'message' => 'ID tidak valid.']);
            return;
        }
 
        if ($aksi === 'add') {
            $res = Keranjang::add($id, max(1, $jumlah));
        } elseif ($aksi === 'update') {
            $res = Keranjang::updateQuantity($id, $jumlah);
        } elseif ($aksi === 'remove') {
            $res = Keranjang::remove($id);
        } else {
            http_response_code(400);
            echo json_encode(['ok' => false, 'message' => 'Aksi tidak dikenal.']);
            return;
        }
 
        $errors = array_map('strval', array_values($res['errors']));
        if (!$res['ok']) {
            http_response_code($res['status'] >= 400 ? $res['status'] : 502);
        }
 
        echo json_encode([
            'ok' => $res['ok'],
            'message' => $errors ? implode(' ', $errors) : ($res['message'] ?? null),
            'jumlah' => $res['data']['jumlah'] ?? null,
        ]);
    }
}