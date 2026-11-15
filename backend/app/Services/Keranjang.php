<?php
class KeranjangService
{
    public function __construct(private Produk $produk, private Keranjang $keranjang) {}

    public function daftar(int $userId): array
    {
        $items = $this->keranjang->getByUser($userId);
        $total = array_sum(array_map(fn($i) => $i['harga'] * $i['jumlah'], $items));
        return ['items' => $items, 'total' => $total];
    }

    public function tambah(int $userId, int $produkId, int $jumlah): void
    {
        if ($jumlah < 1) throw new DomainException('Jumlah minimal 1');

        $produk = $this->produk->find($produkId);
        if (!$produk) throw new DomainException('Produk tidak ditemukan');

        $item = $this->keranjang->findItem($userId, $produkId);
        $jumlahBaru = ($item['jumlah'] ?? 0) + $jumlah;

        if ($jumlahBaru > $produk['stok']) throw new DomainException('Stok tidak cukup');

        $item
            ? $this->keranjang->updateJumlah($item['id'], $jumlahBaru)
            : $this->keranjang->insert($userId, $produkId, $jumlah);
    }
}