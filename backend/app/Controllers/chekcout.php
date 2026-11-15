<?php
class TransaksiController
{
    public function __construct(private TransaksiService $service) {}

    public function checkout()
    {
        try {
            $hasil = $this->service->checkout($_SESSION['user_id']);
            return Response::json(true, $hasil, 'Checkout berhasil', 201);
        } catch (DomainException $e) {
            return Response::json(false, null, $e->getMessage(), 422);
        }
    }
}