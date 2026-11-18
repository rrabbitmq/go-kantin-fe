<?php
use App\Core\HttpException;
use App\Models\Menu;
use App\Models\Penjual;
use App\Services\MenuService;

require_once __DIR__ . '/../../config/response.php';
require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../config/input.php';
require_once __DIR__ . '/../../config/auth.php';

handleCors();

/*
 * GET    /api/penjual/menu            daftar menu milik penjual
 * POST   /api/penjual/menu            tambah menu (tanpa "id") atau ubah menu (dengan "id")
 *                                     multipart/form-data: nama, id_kategori, status, deskripsi, gambar,
 *                                     varian_json=[{id?,nama,harga,stok},...] (atau harga+stok legacy → Reguler)
 * DELETE /api/penjual/menu?id=ID      nonaktifkan menu
 */
$auth   = requireRole('penjual');
$userId = (int) $auth['sub'];
$method = $_SERVER['REQUEST_METHOD'];

try {
    $profilId = Penjual::profilIdForUser($userId);
    if (!$profilId) {
        jsonResponse(403, ['message' => 'Profil penjual belum tersedia.']);
    }

    if ($method === 'GET') {
        jsonResponse(200, ['success' => true, 'data' => Menu::untukPenjual($profilId)]);
    }

    if ($method === 'POST') {
        $in = getInput();
        $file = (isset($_FILES['gambar']) && ($_FILES['gambar']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE)
            ? $_FILES['gambar']
            : null;

        $hasil = MenuService::simpan($profilId, $userId, $in, $file);
        jsonResponse(empty($in['id']) ? 201 : 200, ['success' => true, 'message' => $hasil['pesan'], 'data' => $hasil]);
    }

    if ($method === 'DELETE') {
        $id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if (!$id) {
            jsonResponse(400, ['message' => 'Validasi gagal', 'errors' => ['id' => 'ID menu wajib diisi.']]);
        }

        MenuService::nonaktifkan($profilId, $id);
        jsonResponse(200, ['success' => true, 'message' => 'Menu dinonaktifkan.']);
    }

    jsonResponse(405, ['message' => 'Method tidak diizinkan']);
} catch (HttpException $e) {
    jsonResponse($e->status(), ['message' => $e->getMessage(), 'errors' => $e->errors()]);
} catch (PDOException | RuntimeException $e) {
    error_log($e->getMessage());
    jsonResponse(500, ['message' => 'Terjadi kesalahan pada server']);
}