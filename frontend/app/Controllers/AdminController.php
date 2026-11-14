<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\KategoriProduk;
use App\Models\Penjual;

final class AdminController extends Controller {
    public function dashboard(): void {
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

    public function antrian(): void {
        $this->requirePenjual();

        $res = Penjual::antrian();
        $orders = [];
        if ($res['ok'] && is_array($res['data'])) {
            $orders = $res['data'];
        } else {
            $this->flash('error', $res['message'] ?? 'Antrian pesanan tidak dapat dimuat');
        }

        $this->renderAdmin('admin/antrian', [
            'pageTitle' => 'Antrian Pesanan - Go Kantin',
            'activeNav' => 'antrian',
            'orders' => $orders,
            'queueCount' => count($orders),
        ]);

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

    public function pos(): void {
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

    public function menu(): void {
        $this->requirePenjual();

        $res = Penjual::menu();
        $products = [];
        if ($res['ok'] && is_array($res['data'])) {
            $products = $res['data'];
        } else {
            $this->flash('error', $res['message'] ?? 'Daftar menu tidak dapat dimuat.');
        }

        $this->renderAdmin('admin/menu', [
            'pageTitle' => 'Manajemen Menu & Stok - Go Kantin',
            'activeNav' => 'menu',
            'products' => $products,
            'categories' => KategoriProduk::all(),
        ]);

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

   /** POST /admin/menu.php (aksi=simpan): tambah atau ubah menu, termasuk upload gambar. */
    public function simpanMenu(): void
    {
        $this->requirePenjual();

        // Kalau ukuran request melebihi post_max_size, PHP mengosongkan $_POST dan $_FILES
        if (!$_POST && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
            $this->flash('error', 'Ukuran data yang dikirim terlalu besar. Gunakan gambar maksimal 2 MB.');
            $this->redirect('/admin/menu.php');
            return;
        }

        $upload = $_FILES['gambar'] ?? null;
        $kodeUpload = $upload['error'] ?? UPLOAD_ERR_NO_FILE;
        if ($kodeUpload === UPLOAD_ERR_INI_SIZE || $kodeUpload === UPLOAD_ERR_FORM_SIZE) {
            $this->flash('error', 'Ukuran gambar maksimal 2 MB.');
            $this->redirect('/admin/menu.php');
            return;
        }

        $fields = [
            'nama' => trim((string) ($_POST['nama'] ?? '')),
            'id_kategori' => (int) ($_POST['id_kategori'] ?? 0),
            'harga' => trim((string) ($_POST['harga'] ?? '')),
            'stok' => trim((string) ($_POST['stok'] ?? '')),
            'status' => (string) ($_POST['status'] ?? 'aktif'),
            'deskripsi' => trim((string) ($_POST['deskripsi'] ?? '')),
        ];
        $id = (int) ($_POST['id'] ?? 0);
        if ($id > 0) {
            $fields['id'] = $id;
        }

        $res = Penjual::simpanMenu($fields, $upload);

        if ($res['ok']) {
            $this->flash('success', $res['message'] ?? 'Menu disimpan.');
        } else {
            $messages = array_map('strval', array_values($res['errors']));
            $this->flash('error', $messages ? implode(' ', $messages) : ($res['message'] ?? 'Menu gagal disimpan.'));
        }

        $this->redirect('/admin/menu.php');
    }

    /** POST /admin/menu.php (aksi=nonaktif). */
    public function nonaktifkanMenu(): void
    {
        $this->requirePenjual();

        $id = (int) ($_POST['id'] ?? 0);
        if ($id < 1) {
            $this->flash('error', 'Menu tidak valid.');
            $this->redirect('/admin/menu.php');
            return;
        }

        $res = Penjual::nonaktifkanMenu($id);
        $this->flash($res['ok'] ? 'success' : 'error', $res['message'] ?? ($res['ok'] ? 'Menu dinonaktifkan.' : 'Menu gagal dinonaktifkan.'));
        $this->redirect('/admin/menu.php');
    }

    /** POST /admin/menu.php (aksi=stok): tambah, kurangi, atau atur stok. */
    public function ubahStok(): void
    {
        $this->requirePenjual();

        $idVarian = (int) ($_POST['id_varian'] ?? 0);
        $jenis = (string) ($_POST['jenis'] ?? '');
        $jumlah = (int) ($_POST['jumlah'] ?? 0);
        $keterangan = trim((string) ($_POST['keterangan'] ?? ''));

        if ($idVarian < 1) {
            $this->flash('error', 'Menu tidak valid.');
            $this->redirect('/admin/menu.php');
            return;
        }

        $res = Penjual::ubahStok($idVarian, $jenis, $jumlah, $keterangan);

        if ($res['ok']) {
            $this->flash('success', $res['message'] ?? 'Stok diperbarui.');
        } else {
            $messages = array_map('strval', array_values($res['errors']));
            $this->flash('error', $messages ? implode(' ', $messages) : ($res['message'] ?? 'Stok gagal diperbarui.'));
        }

        $this->redirect('/admin/menu.php');
    }

    public function mutasiStok(): void
    {
        $this->requirePenjual();

        $res = Penjual::stokLog();
        $logs = [];
        if ($res['ok'] && is_array($res['data'])) {
            $logs = $res['data'];
        } else {
            $this->flash('error', $res['message'] ?? 'Riwayat stok tidak dapat dimuat.');
        }

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

    public function kasir(): void {
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

    private function requirePenjual(): void {
        $this->requireRole('penjual');
    }

    protected function renderAdmin(string $view, array $data): void
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
