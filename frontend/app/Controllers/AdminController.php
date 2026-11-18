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

        $res = Penjual::pos();
        $products = [];
        $receiptNo = 'POS-' . date('ymd') . '-…';
        if ($res['ok'] && is_array($res['data'])) {
            $products = $res['data']['products'] ?? [];
            $prefix = (string) ($res['data']['receipt_prefix'] ?? ('POS-' . date('ymd')));
            $receiptNo = $prefix . '-…';
        } else {
            $this->flash('error', $res['message'] ?? 'Katalog POS tidak dapat dimuat.');
        }

        $this->renderAdmin('admin/pos', [
            'pageTitle' => 'Kasir POS - Go Kantin',
            'activeNav' => 'pos',
            'products' => $products,
            'receiptNo' => $receiptNo,
        ]);
    }

    /** POST /admin/pos.php — jual | tiket | selesai */
    public function aksiPos(): void
    {
        $this->requirePenjual();
        header('Content-Type: application/json; charset=utf-8');

        $raw = file_get_contents('php://input');
        $body = is_string($raw) && $raw !== '' ? json_decode($raw, true) : $_POST;
        if (!is_array($body)) {
            echo json_encode(['ok' => false, 'message' => 'Body tidak valid.']);
            exit;
        }

        $res = Penjual::aksiPos($body);
        http_response_code($res['ok'] ? 200 : (int) ($res['status'] ?: 400));
        echo json_encode([
            'ok' => $res['ok'],
            'message' => $res['message'] ?? ($res['ok'] ? 'OK' : 'Gagal'),
            'data' => $res['data'],
            'errors' => $res['errors'] ?? [],
        ]);
        exit;
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

        $variants = [];
        $rawJson = trim((string) ($_POST['varian_json'] ?? ''));
        if ($rawJson !== '') {
            $decoded = json_decode($rawJson, true);
            if (is_array($decoded)) {
                foreach ($decoded as $row) {
                    if (!is_array($row)) {
                        continue;
                    }
                    $variants[] = [
                        'id' => (int) ($row['id'] ?? 0),
                        'nama' => trim((string) ($row['nama'] ?? '')),
                        'harga' => (int) ($row['harga'] ?? 0),
                        'stok' => (int) ($row['stok'] ?? 0),
                    ];
                }
            }
        } elseif (is_array($_POST['varian'] ?? null)) {
            foreach ($_POST['varian'] as $row) {
                if (!is_array($row)) {
                    continue;
                }
                $variants[] = [
                    'id' => (int) ($row['id'] ?? 0),
                    'nama' => trim((string) ($row['nama'] ?? '')),
                    'harga' => (int) ($row['harga'] ?? 0),
                    'stok' => (int) ($row['stok'] ?? 0),
                ];
            }
        }

        $fields = [
            'nama' => trim((string) ($_POST['nama'] ?? '')),
            'id_kategori' => (int) ($_POST['id_kategori'] ?? 0),
            'status' => (string) ($_POST['status'] ?? 'aktif'),
            'deskripsi' => trim((string) ($_POST['deskripsi'] ?? '')),
            'varian_json' => json_encode($variants, JSON_UNESCAPED_UNICODE),
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

        $stand = $this->standData();
        if ($stand === null) {
            $this->flash('error', 'Pengaturan stand tidak dapat dimuat.');
            $stand = [
                'nama_usaha' => '',
                'lokasi' => '',
                'jam_buka' => '08:00',
                'jam_tutup' => '15:00',
                'status_buka' => true,
                'no_hp' => '',
            ];
        }

        $this->renderAdmin('admin/pengaturan', [
            'pageTitle' => 'Pengaturan Stand - Go Kantin',
            'activeNav' => 'pengaturan',
            'stand' => $stand,
        ]);
    }

    public function simpanPengaturan(): void
    {
        $this->requirePenjual();

        $fields = [
            'nama_usaha' => trim((string) ($_POST['nama_usaha'] ?? '')),
            'lokasi' => trim((string) ($_POST['lokasi'] ?? '')),
            'jam_buka' => trim((string) ($_POST['jam_buka'] ?? '')),
            'jam_tutup' => trim((string) ($_POST['jam_tutup'] ?? '')),
            'no_hp' => trim((string) ($_POST['no_hp'] ?? '')),
            'status_buka' => isset($_POST['status_buka']),
        ];

        $res = Penjual::simpanStand($fields);
        if ($res['ok']) {
            $this->flash('success', $res['message'] ?? 'Pengaturan stand disimpan.');
        } else {
            $messages = array_map('strval', array_values($res['errors'] ?? []));
            $this->flash('error', $messages ? implode(' ', $messages) : ($res['message'] ?? 'Pengaturan gagal disimpan.'));
        }
        $this->redirect('/admin/pengaturan.php');
    }

    private function requirePenjual(): void {
        $this->requireRole('penjual');
    }

    private function standData(): ?array
    {
        static $cached = false;
        static $stand = null;
        if ($cached) {
            return $stand;
        }
        $cached = true;
        $res = Penjual::stand();
        if ($res['ok'] && is_array($res['data'])) {
            $stand = $res['data'];
        }
        return $stand;
    }

    protected function renderAdmin(string $view, array $data): void
    {
        $data['currentUser'] = $data['currentUser'] ?? $this->currentUserForLayout();
        if (!isset($data['standMeta'])) {
            $stand = $this->standData();
            $data['standMeta'] = $stand ? [
                'nama' => $stand['nama_usaha'] ?? 'Stand',
                'lokasi' => $stand['lokasi'] ?? '',
                'jam' => $stand['jam'] ?? '08.00 - 15.00 WIB',
                'status_buka' => !empty($stand['status_buka']),
                'role_label' => 'Admin Stand',
            ] : [
                'nama' => 'Stand',
                'lokasi' => '',
                'jam' => '08.00 - 15.00 WIB',
                'status_buka' => true,
                'role_label' => 'Admin Stand',
            ];
        }
        $data['queueBadge'] = $data['queueBadge'] ?? (int) ($data['queueCount'] ?? 0);

        parent::renderAdmin($view, $data);
    }
}
