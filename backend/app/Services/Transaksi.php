<?php
class TransaksiService
{
    public function __construct(
        private PDO $db,
        private Keranjang $keranjang,
        private Produk $produk,
        private Transaksi $transaksi,
        private DetailTransaksi $detail,
        private Antrian $antrian
    ) {}

    public function checkout(int $userId): array
    {
        $this->db->beginTransaction();
        try {
            $items = $this->keranjang->getByUserForUpdate($userId);
            if (!$items) throw new DomainException('Keranjang kosong');

            $total = 0;
            foreach ($items as $i) {
                if ($i['stok'] < $i['jumlah']) throw new DomainException('Stok tidak cukup');
                $total += $i['harga'] * $i['jumlah'];
            }

            $transaksiId = $this->transaksi->create($userId, $total);

            foreach ($items as $i) {
                $this->detail->create($transaksiId, $i['produk_id'], $i['jumlah'], $i['harga']);
                $this->produk->kurangiStok($i['produk_id'], $i['jumlah']);
            }

            $nomor = $this->antrian->create($transaksiId);
            $this->keranjang->kosongkan($userId);

            $this->db->commit();
            return ['transaksi_id' => $transaksiId, 'total' => $total, 'nomor_antrian' => $nomor];
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}