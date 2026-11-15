<?php
class KeranjangController
{
    public function __construct(private KeranjangService $service) {}

    public function index()
    {
        $data = $this->service->daftar($_SESSION['user_id']);
        return Response::json(true, $data);
    }

    public function tambah(Request $req)
    {
        try {
            $this->service->tambah(
                $_SESSION['user_id'],              
                (int) $req->input('produk_id'),
                (int) $req->input('jumlah', 1)
            );
            return Response::json(true, null, 'Produk ditambahkan ke keranjang');
        } catch (DomainException $e) {
            return Response::json(false, null, $e->getMessage(), 422);
        }
    }
}