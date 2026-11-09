<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;

final class AdminController extends Controller
{
    public function dashboard(): void
    {
        $this->requirePenjual();

        $this->renderAdmin('admin/dashboard', [
            'pageTitle' => 'Dashboard Admin - Go Kantin',
            'activeNav' => 'dashboard',
            'stats' => [
                'pendapatan' => 1257500,
                'transaksi' => 153,
                'qris' => 98,
                'tunai' => 55,
                'menu_aktif' => 16,
                'makanan' => 7,
                'minuman' => 5,
                'snack' => 4,
                'stok_menipis' => 3,
            ],
            'lowStock' => [
                ['nama' => 'Ayam Krispi', 'stok' => 4],
                ['nama' => 'Sosis Solo', 'stok' => 3],
                ['nama' => 'Kapal Api', 'stok' => 2],
            ],
            'categories' => [
                ['nama' => 'Makanan', 'terjual' => 62, 'sisa' => 84, 'omzet' => 620000],
                ['nama' => 'Minuman', 'terjual' => 71, 'sisa' => 120, 'omzet' => 355000],
                ['nama' => 'Snack', 'terjual' => 20, 'sisa' => 45, 'omzet' => 282500],
            ],
            'weeklyRevenue' => [
                ['label' => 'Sen', 'value' => 980000],
                ['label' => 'Sel', 'value' => 1120000],
                ['label' => 'Rab', 'value' => 870000],
                ['label' => 'Kam', 'value' => 1340000],
                ['label' => 'Jum', 'value' => 1257500],
            ],
            'bestsellers' => [
                ['rank' => 1, 'nama' => 'Air Mineral', 'harga' => 3000, 'stok' => 48, 'terjual' => 62],
                ['rank' => 2, 'nama' => 'Oreo Soft Cake', 'harga' => 3000, 'stok' => 25, 'terjual' => 41],
                ['rank' => 3, 'nama' => 'Ayam Krispi', 'harga' => 12000, 'stok' => 4, 'terjual' => 38],
                ['rank' => 4, 'nama' => 'Teh Pucuk', 'harga' => 4000, 'stok' => 30, 'terjual' => 29],
                ['rank' => 5, 'nama' => 'Sosis Solo Ayam', 'harga' => 2500, 'stok' => 18, 'terjual' => 24],
            ],
        ]);
    }

    public function antrian(): void
    {
        $this->requirePenjual();

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
                'queue' => 'A-015',
                'kode' => '#JTI-118-MNM',
                'status' => 'dibayar',
                'slot' => '09:30 - 10:00',
                'pembayar' => 'Tunai di Kasir - Bayar saat Ambil',
                'metode' => 'tunai',
                'pembeli' => 'Siti Nurhaliza',
                'nim' => '2341720042',
                'waktu' => '09:20',
                'total' => 15000,
                'catatan' => '',
                'items' => [
                    ['qty' => 1, 'nama' => 'Ayam Krispi', 'harga' => 12000],
                    ['qty' => 1, 'nama' => 'Air Mineral', 'harga' => 3000],
                ],
            ],
            [
                'queue' => 'A-016',
                'kode' => '#JTI-119-MKN',
                'status' => 'siap_diambil',
                'slot' => '10:00 - 10:30',
                'pembayar' => 'QRIS - Lunas',
                'metode' => 'qris',
                'pembeli' => 'Budi Santoso',
                'nim' => '2341720088',
                'waktu' => '09:05',
                'total' => 8000,
                'catatan' => 'tanpa es',
                'items' => [
                    ['qty' => 2, 'nama' => 'Teh Pucuk', 'harga' => 4000],
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

        $rows = [
            ['kode' => 'ABC-123-XYZ', 'waktu' => '2026-11-02 09:15', 'pembeli' => 'Farhan Ramadhan', 'slot' => '09:30-10:00', 'sumber' => 'online', 'metode' => 'QRIS', 'total' => 11000, 'status' => 'diproses'],
            ['kode' => 'JTI-118-MNM', 'waktu' => '2026-11-02 09:20', 'pembeli' => 'Siti Nurhaliza', 'slot' => '09:30-10:00', 'sumber' => 'online', 'metode' => 'Tunai', 'total' => 15000, 'status' => 'dibayar'],
            ['kode' => 'JTI-119-MKN', 'waktu' => '2026-11-02 09:05', 'pembeli' => 'Budi Santoso', 'slot' => '10:00-10:30', 'sumber' => 'online', 'metode' => 'QRIS', 'total' => 8000, 'status' => 'siap_diambil'],
            ['kode' => 'POS-2026-088', 'waktu' => '2026-11-02 08:45', 'pembeli' => 'Walk-in', 'slot' => '-', 'sumber' => 'pos', 'metode' => 'Tunai', 'total' => 16000, 'status' => 'selesai'],
            ['kode' => 'POS-2026-087', 'waktu' => '2026-11-02 08:30', 'pembeli' => 'Walk-in', 'slot' => '-', 'sumber' => 'pos', 'metode' => 'QRIS', 'total' => 7000, 'status' => 'selesai'],
            ['kode' => 'JTI-110-SNK', 'waktu' => '2026-11-02 08:10', 'pembeli' => 'Rina Melati', 'slot' => '08:30-09:00', 'sumber' => 'online', 'metode' => 'QRIS', 'total' => 9000, 'status' => 'selesai'],
            ['kode' => 'JTI-109-MKN', 'waktu' => '2026-11-01 14:20', 'pembeli' => 'Andi Wijaya', 'slot' => '14:30-15:00', 'sumber' => 'online', 'metode' => 'Tunai', 'total' => 24000, 'status' => 'dibatalkan'],
            ['kode' => 'JTI-108-MNM', 'waktu' => '2026-11-01 13:00', 'pembeli' => 'Citra Lestari', 'slot' => '13:00-13:30', 'sumber' => 'online', 'metode' => 'QRIS', 'total' => 5000, 'status' => 'menunggu_pembayaran'],
            ['kode' => 'POS-2026-080', 'waktu' => '2026-11-01 12:15', 'pembeli' => 'Walk-in', 'slot' => '-', 'sumber' => 'pos', 'metode' => 'Tunai', 'total' => 12000, 'status' => 'selesai'],
            ['kode' => 'JTI-100-ALL', 'waktu' => '2026-11-01 10:00', 'pembeli' => 'Eko Prasetyo', 'slot' => '10:00-10:30', 'sumber' => 'online', 'metode' => 'QRIS', 'total' => 18500, 'status' => 'selesai'],
        ];

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
        $this->requireRole('penjual', 'super_admin');
    }
}
