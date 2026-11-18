<?php
 
namespace App\Controllers;
 
use App\Core\Auth;
use App\Core\Controller;
use App\Models\Keranjang;
 
final class CartController extends Controller {
    public function index(): void {
        $this->requireRole('pembeli');

        $summary = Keranjang::summary();

        $cartItems = $summary['items'] ?? [];
        $subtotal  = (float) ($summary['subtotal'] ?? 0.0);
        $shipping  = (float) ($summary['biaya_layanan'] ?? 0.0);
        $total     = $subtotal + $shipping;

        $this->render('pembeli/cart', [
            'pageTitle'   => 'Keranjang - Go Kantin',
            'activeNav'   => 'cart',
            'currentUser' => $this->currentUserForLayout(),
            'cartCount'   => (int) ($summary['jumlah'] ?? 0),
            'cartItems'   => $cartItems,
            'subtotal'    => $subtotal,
            'shipping'    => $shipping,
            'total'       => $total,
        ]);
    }

    /** Form "Tambahkan" di halaman detail produk (POST /user/cart.php, tanpa JavaScript). */
    public function addFromForm(): void
    {
        $this->requireRole('pembeli');

        $id = (int) ($_POST['id'] ?? 0);
        $variantId = (int) ($_POST['id_varian_produk'] ?? $_POST['id_varian'] ?? 0);
        $jumlah = max(1, (int) ($_POST['jumlah'] ?? 1));

        if ($id < 1 && $variantId < 1) {
            $this->flash('error', 'Produk tidak valid.');
            $this->redirect('/user/menu.php');
            return;
        }

        $res = Keranjang::add($id, $jumlah, $variantId > 0 ? $variantId : null);

        if (!$res['ok']) {
            $errors = array_map('strval', array_values($res['errors']));
            $this->flash('error', $errors ? implode(' ', $errors) : ($res['message'] ?? 'Gagal menambahkan ke keranjang.'));
            $this->redirect($id > 0 ? '/user/detail-product.php?id=' . $id : '/user/menu.php');
            return;
        }

        $this->flash('success', 'Produk ditambahkan ke keranjang.');
        $this->redirect('/user/cart.php');
    }
 
    /**
     * Dipanggil lewat fetch() (halaman keranjang, tombol "Tambah", dll). Selalu membalas JSON.
     * POST: aksi = add | update | remove
     *   add    -> id = id produk (opsional jika id_varian_produk diisi), jumlah, id_varian_produk?
     *   update -> id = id item keranjang, jumlah
     *   remove -> id = id item keranjang
     */
    public function action(): void {
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
        $variantId = (int) ($_POST['id_varian_produk'] ?? $_POST['id_varian'] ?? 0);
        $jumlah = (int) ($_POST['jumlah'] ?? 1);

        if ($aksi === 'add') {
            if ($id < 1 && $variantId < 1) {
                http_response_code(400);
                echo json_encode(['ok' => false, 'message' => 'ID tidak valid.']);
                return;
            }
            $res = Keranjang::add($id, max(1, $jumlah), $variantId > 0 ? $variantId : null);
        } elseif ($aksi === 'update') {
            if ($id < 1) {
                http_response_code(400);
                echo json_encode(['ok' => false, 'message' => 'ID tidak valid.']);
                return;
            }
            $res = Keranjang::updateQuantity($id, $jumlah);
        } elseif ($aksi === 'remove') {
            if ($id < 1) {
                http_response_code(400);
                echo json_encode(['ok' => false, 'message' => 'ID tidak valid.']);
                return;
            }
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