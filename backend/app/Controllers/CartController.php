<?php
namespace App\Controllers;

use App\Helpers\Response;
use App\Middleware\AuthMiddleware;
use App\Services\CartService;

final class CartController
{
    private CartService $cart;

    public function __construct()
    {
        AuthMiddleware::handle('pembeli');
        $this->cart = new CartService();
    }

    private function userId(): int
    {
        return (int) AuthMiddleware::user()['id'];
    }

    private function body(): array
    {
        return json_decode(file_get_contents('php://input'), true) ?: [];
    }

    public function index(): void
    {
        $res = $this->cart->list($this->userId());
        Response::json($res, $res['status']);
    }

    public function add(): void
    {
        $in  = $this->body();
        $produkId = (int) ($in['produk_id'] ?? $in['id_varian_produk'] ?? $in['id_produk'] ?? 0);
        $jumlah   = (int) ($in['jumlah'] ?? 1);
        $res = $this->cart->add($this->userId(), $produkId, max(1, $jumlah));
        Response::json($res, $res['status']);
    }

    public function update(int $id): void
    {
        $in  = $this->body();
        $res = $this->cart->update($this->userId(), $id, (int) ($in['jumlah'] ?? 0));
        Response::json($res, $res['status']);
    }

    public function remove(int $id): void
    {
        $res = $this->cart->remove($this->userId(), $id);
        Response::json($res, $res['status']);
    }
}