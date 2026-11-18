<?php
use App\Core\HttpException;
use App\Models\Penjual;

require_once __DIR__ . '/../../config/response.php';
require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../config/input.php';
require_once __DIR__ . '/../../config/auth.php';

handleCors();

/*
 * GET  /api/penjual/stand   profil usaha + jam operasional
 * PUT  /api/penjual/stand   simpan pengaturan stand
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
        $profil = Penjual::profil($profilId);
        jsonResponse(200, ['success' => true, 'data' => $profil]);
    }

    if ($method === 'PUT' || $method === 'POST') {
        $in = getInput();
        $nama = trim((string) ($in['nama_usaha'] ?? ''));
        $lokasi = trim((string) ($in['lokasi'] ?? ''));
        $noHp = trim((string) ($in['no_hp'] ?? ''));
        $jamBuka = trim((string) ($in['jam_buka'] ?? ''));
        $jamTutup = trim((string) ($in['jam_tutup'] ?? ''));
        $statusBuka = filter_var($in['status_buka'] ?? true, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        if ($statusBuka === null) {
            $statusBuka = in_array((string) ($in['status_buka'] ?? ''), ['1', 'true', 'on', 'yes'], true);
        }

        $errors = [];
        if ($nama === '' || mb_strlen($nama) > 100) {
            $errors['nama_usaha'] = 'Nama usaha wajib diisi (maks. 100 karakter).';
        }
        if ($jamBuka === '' || !preg_match('/^\d{2}:\d{2}$/', $jamBuka)) {
            $errors['jam_buka'] = 'Jam buka tidak valid.';
        }
        if ($jamTutup === '' || !preg_match('/^\d{2}:\d{2}$/', $jamTutup)) {
            $errors['jam_tutup'] = 'Jam tutup tidak valid.';
        }
        if ($errors) {
            jsonResponse(400, ['message' => 'Validasi gagal', 'errors' => $errors]);
        }

        $data = Penjual::updateProfil($profilId, [
            'nama_usaha' => $nama,
            'lokasi' => $lokasi,
            'no_hp' => $noHp,
            'jam_buka' => $jamBuka,
            'jam_tutup' => $jamTutup,
            'status_buka' => (bool) $statusBuka,
        ]);

        jsonResponse(200, ['success' => true, 'message' => 'Pengaturan stand disimpan.', 'data' => $data]);
    }

    jsonResponse(405, ['message' => 'Method tidak diizinkan']);
} catch (HttpException $e) {
    jsonResponse($e->status(), ['message' => $e->getMessage(), 'errors' => $e->errors()]);
} catch (PDOException | RuntimeException $e) {
    error_log($e->getMessage());
    jsonResponse(500, ['message' => 'Terjadi kesalahan pada server']);
}
