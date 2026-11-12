<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Penjual;

final class AdminController extends Controller
{
    public function dashboard(): void
    {
        $this->requirePenjual();

        $res = Penjual::dashboard();
        if ($res['ok'] && is_array($res['data'])) {
            $data = $res['data'];
        }else {
            $this->flash('error', $res['message'] ?? 'Data dashboard tidak dapat dimuat');
            $data = $this->emptyDashboard();
        }

        $this->renderAdmin('admin/dashboard', [
            'pageTitle' => 'Dashboard Admin - Go Kantin',
            'activeNav' => 'dashboard',
            'stats' => $data['stats'],
            'lowStock' => $data['lowStock'],
            'categories' => $data['categories'],
            'weeklyRevenue' => $data['weeklyRevenue'],
            'bestsellers' => $data['bestsellers'],
            'queueBadge' => (int) ($data['antiran_aktif'] ?? 0),
        ]);
    }

    public function antrian(): void
    {
        $this->requirePenjual();

        $res = Penjual::antrian();
        $orders = [];
        if ($res['ok'] && is_array($res['data'])) {
            $orders = $res['data'];
        } else {
            $this->flash('error', $res['message'] ?? 'Antrian pesanan tidak dapat dimuat');
        }

        $orders = [
            [
                'queue' => 'A-014',
                'kode' => '#ABC-123-XYZ',
                'status' => 'diproses',
                'slot' => '09:30 - 10:00',
                'pembayar' => 'QRIS - Bukti Diunggah',
                'metode' => 'qris',
                'pembeli' => 'Farhan Ramadhan',
                'nim' => '2341720001',
                'waktu' => '09:15',
                'total' => 11000,
                'catatan' => 'satu dingin, satu biasa',
                'items' => [
                    ['qty' => 1, 'nama' => 'Oreo Soft Cake', 'harga' => 3000],
                    ['qty' => 2, 'nama' => 'Air Mineral', 'harga' => 3000],
                    ['qty' => 1, 'nama' => 'Teh Pucuk', 'harga' => 4000],
                ],
            ],
            [
                'queue' => 'A-017',
                'kode' => '#JTI-126-SNK',
                'status' => 'dibayar',
                'slot' => '10:00 - 10:30',
                'pembayar' => 'QRIS - Bukti Diunggah',
                'metode' => 'qris',
                'pembeli' => 'Dewi Anggraini',
                'nim' => '2341720110',
                'waktu' => '09:40',
                'total' => 10000,
                'catatan' => 'pedas level 1',
                'items' => [
                    ['qty' => 2, 'nama' => 'Tahu Isi Sayur', 'harga' => 2500],
                    ['qty' => 2, 'nama' => 'Sosis Solo Ayam', 'harga' => 2500],
                ],
            ],
        ];

        $this->renderAdmin('admin/antrian', [
            'pageTitle' => 'Antrian Pesanan - Go Kantin',
            'activeNav' => 'antrian',
            'orders' => $orders,
            'queueCount' => count($orders),
        ]);
    }

    /** POST untuk /admin/antrian.php: digunakan konfirmasi, proses, siap, selesai, atau batalkan pesanan. */
    public function ubahStatus(): void {
        $this->requirePenjual();

        $id = (int) ($_POST['id'] ?? 0);
        $aksi = (string) ($_POST['aksi'] ?? '');

        if ($id < 1 || $aksi === '') {
            $this->flash('error', 'Permintaan tidak valid');
            $this->redirect('/admin/antrian.php');
            return;
        }

        $res = Penjual::ubahStatus($id, $aksi);

        if ($res['ok']) {
            $this->flash('success', $res['message'] ?? 'Status pesanan diperbarui');
        } else {
            $this->flash('error', $res['message'] ?? 'Status pesanan gagal diperbarui');
        }
        $this->redirect('/admin/antrian.php');
    }

    private function emptyDashboard(): array {
        return[
            'stats' => [
                'pendapatan' => 0, 'transaksi' => 0, 'qris' => 0, 'tunai' => 0,
                'menu_aktif' => 0, 'makanan' => 0, 'minuman' => 0, 'snack' => 0, 'stok_menipis' => 0,
            ],
            'lowStock' => [],
            'categories' => [],
            'weeklyRevenue' => [],
            'bestsellers' => [],
            'antrian_aktif' => 0,
        ];
    }

    public function pos(): void
    {
        $this->requirePenjual();

        $products = [
            ['id' => 1, 'nama' => 'Oreo Soft Cake', 'kategori' => 'Snack', 'harga' => 3000, 'stok' => 25, 'gambar' => '/img/menu/1.jpg'],
            ['id' => 2, 'nama' => 'Ayam Krispi', 'kategori' => 'Makanan', 'harga' => 12000, 'stok' => 4, 'gambar' => '/img/menu/2.jpg'],
            ['id' => 3, 'nama' => 'Air Mineral', 'kategori' => 'Minuman', 'harga' => 3000, 'stok' => 48, 'gambar' => '/img/menu/3.jpg'],
            ['id' => 4, 'nama' => 'Kapal Api', 'kategori' => 'Minuman', 'harga' => 4000, 'stok' => 2, 'gambar' => '/img/menu/4.jpg'],
            ['id' => 5, 'nama' => 'La Segar Twist', 'kategori' => 'Minuman', 'harga' => 5000, 'stok' => 18, 'gambar' => '/img/menu/5.jpg'],
            ['id' => 6, 'nama' => 'Teh Pucuk', 'kategori' => 'Minuman', 'harga' => 4000, 'stok' => 30, 'gambar' => '/img/menu/6.jpg'],
            ['id' => 7, 'nama' => 'Sosis Solo Ayam', 'kategori' => 'Snack', 'harga' => 2500, 'stok' => 18, 'gambar' => '/img/menu/7.jpg'],
            ['id' => 8, 'nama' => 'Tahu Isi Sayur', 'kategori' => 'Snack', 'harga' => 2500, 'stok' => 22, 'gambar' => '/img/menu/8.jpg'],
        ];

        $this->renderAdmin('admin/pos', [
            'pageTitle' => 'Kasir POS - Go Kantin',
            'activeNav' => 'pos',
            'products' => $products,
            'receiptNo' => 'POS-2026-089',
        ]);
    }

    public function menu(): void
    {
        $this->requirePenjual();

        $products = [
            ['id' => 1, 'nama' => 'Oreo Soft Cake', 'kategori' => 'Snack', 'varian' => 1, 'harga' => 3000, 'stok' => 25, 'status' => 'aktif', 'gambar' => '/img/menu/1.jpg'],
            ['id' => 2, 'nama' => 'Ayam Krispi', 'kategori' => 'Makanan', 'varian' => 2, 'harga' => 12000, 'stok' => 4, 'status' => 'aktif', 'gambar' => '/img/menu/2.jpg'],
            ['id' => 3, 'nama' => 'Air Mineral', 'kategori' => 'Minuman', 'varian' => 1, 'harga' => 3000, 'stok' => 48, 'status' => 'aktif', 'gambar' => '/img/menu/3.jpg'],
            ['id' => 4, 'nama' => 'Kapal Api', 'kategori' => 'Minuman', 'varian' => 1, 'harga' => 4000, 'stok' => 2, 'status' => 'aktif', 'gambar' => '/img/menu/4.jpg'],
            ['id' => 5, 'nama' => 'La Segar Twist', 'kategori' => 'Minuman', 'varian' => 1, 'harga' => 5000, 'stok' => 18, 'status' => 'aktif', 'gambar' => '/img/menu/5.jpg'],
            ['id' => 6, 'nama' => 'Teh Pucuk', 'kategori' => 'Minuman', 'varian' => 1, 'harga' => 4000, 'stok' => 30, 'status' => 'aktif', 'gambar' => '/img/menu/6.jpg'],
            ['id' => 7, 'nama' => 'Sosis Solo Ayam', 'kategori' => 'Snack', 'varian' => 1, 'harga' => 2500, 'stok' => 18, 'status' => 'aktif', 'gambar' => '/img/menu/7.jpg'],
            ['id' => 8, 'nama' => 'Tahu Isi Sayur', 'kategori' => 'Snack', 'varian' => 1, 'harga' => 2500, 'stok' => 22, 'status' => 'aktif', 'gambar' => '/img/menu/8.jpg'],
            ['id' => 9, 'nama' => 'Nasi Ayam Geprek', 'kategori' => 'Makanan', 'varian' => 3, 'harga' => 15000, 'stok' => 12, 'status' => 'aktif', 'gambar' => '/img/menu/9.jpg'],
            ['id' => 10, 'nama' => 'Mie Goreng', 'kategori' => 'Makanan', 'varian' => 2, 'harga' => 10000, 'stok' => 0, 'status' => 'habis', 'gambar' => '/img/menu/10.jpg'],
            ['id' => 11, 'nama' => 'Es Teh Manis', 'kategori' => 'Minuman', 'varian' => 1, 'harga' => 3000, 'stok' => 40, 'status' => 'nonaktif', 'gambar' => '/img/menu/11.jpg'],
            ['id' => 12, 'nama' => 'Risoles Mayo', 'kategori' => 'Snack', 'varian' => 1, 'harga' => 3500, 'stok' => 15, 'status' => 'aktif', 'gambar' => '/img/menu/12.jpg'],
        ];

        $this->renderAdmin('admin/menu', [
            'pageTitle' => 'Manajemen Menu & Stok - Go Kantin',
            'activeNav' => 'menu',
            'products' => $products,
        ]);
    }

    public function mutasiStok(): void
    {
        $this->requirePenjual();

        $logs = [
            ['waktu' => '2026-11-02 08:05', 'produk' => 'Ayam Krispi / Original', 'jenis' => 'masuk', 'jumlah' => 20, 'sebelum' => 0, 'sesudah' => 20, 'petugas' => 'Pak Purwo', 'keterangan' => 'Stok pagi'],
            ['waktu' => '2026-11-02 08:10', 'produk' => 'Air Mineral / 600ml', 'jenis' => 'masuk', 'jumlah' => 50, 'sebelum' => 0, 'sesudah' => 50, 'petugas' => 'Pak Purwo', 'keterangan' => 'Restock'],
            ['waktu' => '2026-11-02 09:18', 'produk' => 'Oreo Soft Cake / Default', 'jenis' => 'keluar', 'jumlah' => 1, 'sebelum' => 26, 'sesudah' => 25, 'petugas' => 'Sistem', 'keterangan' => 'Pesanan A-014'],
            ['waktu' => '2026-11-02 09:22', 'produk' => 'Ayam Krispi / Original', 'jenis' => 'keluar', 'jumlah' => 1, 'sebelum' => 5, 'sesudah' => 4, 'petugas' => 'Sistem', 'keterangan' => 'Pesanan A-015'],
            ['waktu' => '2026-11-02 09:45', 'produk' => 'Kapal Api / Sachet', 'jenis' => 'penyesuaian', 'jumlah' => -3, 'sebelum' => 5, 'sesudah' => 2, 'petugas' => 'Pak Purwo', 'keterangan' => 'Opname rusak'],
            ['waktu' => '2026-11-02 10:02', 'produk' => 'Mie Goreng / Pedas', 'jenis' => 'keluar', 'jumlah' => 2, 'sebelum' => 2, 'sesudah' => 0, 'petugas' => 'Sistem', 'keterangan' => 'POS walk-in'],
            ['waktu' => '2026-11-01 14:30', 'produk' => 'Teh Pucuk / 350ml', 'jenis' => 'pembatalan', 'jumlah' => 2, 'sebelum' => 28, 'sesudah' => 30, 'petugas' => 'Sistem', 'keterangan' => 'Pesanan dibatalkan'],
            ['waktu' => '2026-11-01 11:00', 'produk' => 'Sosis Solo Ayam / Default', 'jenis' => 'masuk', 'jumlah' => 30, 'sebelum' => 0, 'sesudah' => 30, 'petugas' => 'Bu Sari', 'keterangan' => 'Stok siang'],
        ];

        $this->renderAdmin('admin/mutasi-stok', [
            'pageTitle' => 'Mutasi Stok - Go Kantin',
            'activeNav' => 'mutasi',
            'logs' => $logs,
        ]);
    }

    public function laporan(): void
    {
        $this->requirePenjual();

        $res = Penjual::semuaPesanan();
        $orders = [];
        if ($res['ok'] && is_array($res['data'])) {
            $orders = $res['data'];
        } else {
            $this->flash('error', $res['message'] ?? 'Laporan transaksi tidak dapat dimuat');
        }

        $rows = array_map(static fn (array $o): array => [
            'kode' => $o['kode_raw'],
            'waktu' => $o['tanggal'],
            'pembeli' => $o['pembeli'],
            'slot' => $o['slot'],
            'sumber' => 'online',
            'metode' => $o['metode'] === 'qris' ? 'QRIS' : 'Tunai',
            'total' => $o['total'],
            'status' => $o['status'],
        ], $orders);

        $this->renderAdmin('admin/laporan', [
            'pageTitle' => 'Laporan Transaksi - Go Kantin',
            'activeNav' => 'laporan',
            'rows' => $rows,
        ]);
    }

    public function pengaturan(): void
    {
        $this->requirePenjual();

        $this->renderAdmin('admin/pengaturan', [
            'pageTitle' => 'Pengaturan Stand - Go Kantin',
            'activeNav' => 'pengaturan',
            'stand' => [
                'nama_usaha' => 'Stand Kantin JTI',
                'lokasi' => 'Kantin JTI • Lantai 8',
                'jam_buka' => '08:00',
                'jam_tutup' => '15:00',
                'status_buka' => true,
                'no_hp' => '081234567890',
            ],
        ]);
    }

    public function kasir(): void
    {
        $this->requirePenjual();

        $cashiers = [
            ['id' => 1, 'username' => 'purwo', 'nama' => 'Pak Purwo', 'status' => 'aktif', 'peran' => 'Pemilik'],
            ['id' => 2, 'username' => 'sari', 'nama' => 'Bu Sari', 'status' => 'aktif', 'peran' => 'Kasir'],
            ['id' => 3, 'username' => 'dono', 'nama' => 'Mas Dono', 'status' => 'nonaktif', 'peran' => 'Kasir'],
        ];

        $this->renderAdmin('admin/kasir', [
            'pageTitle' => 'Akun Kasir Stand - Go Kantin',
            'activeNav' => 'kasir',
            'cashiers' => $cashiers,
        ]);
    }

    private function requirePenjual(): void
    {
        $this->requireRole('penjual');
    }

    private function renderAdmin(string $view, array $data): void
    {
        $data['currentUser'] = $data['currentUser'] ?? $this->currentUserForLayout();
        $data['standMeta'] = $data['standMeta'] ?? [
            'nama' => 'Stand Kantin JTI',
            'lokasi' => 'Kantin JTI • Lantai 8',
            'jam' => '08.00 - 15.00 WIB',
            'status_buka' => true,
            'role_label' => 'Kasir Stand Terpadu',
        ];
        $data['queueBadge'] = $data['queueBadge'] ?? (int) ($data['queueCount'] ?? 4);

        $this->render($view, $data, 'layouts/admin');
    }
}
