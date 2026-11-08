<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/response.php';
require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../config/input.php';
require_once __DIR__ . '/../../config/auth.php';

handleCors();

if ($_SERVER['REQUEST_METHOD'] !== 'POST'){
    jsonResponse(405, ['message' => 'Method tidak diizinkan']);
}

$roleMap = [
    'pembeli' => ['status_akun' => 'aktif', 'pesan' => 'Registrasi berhasil. Silahkan Login',
    ],
    'penjual' => ['status_akun' => 'pending', 'pesan' => 'Pendaftaran berhasil. Akun menunggu persetujuan admin',
    ],
    'super_admin' => ['status_akun' => 'aktif','pesan' => 'Akun admin berhasil dibuat',
    ],
];

$input = getInput();
$str = fn(string $k): string => is_string($input[$k] ?? null) ? trim($input[$k]) : ''; 

$role = $str['role'] ?: 'pembeli';
if (!isset($roleMap[$role])) {
    jsonResponse(400, ['message' => 'validasi gagal', 'errors' => ['role' => 'Role tidak valid']]);
}
$cfg = $roleMap[$role];


if ($role === 'super_admin') {
    requireRole('super_admin');
}

$username = $str('username');
$email = strtolower ($str('email'));
$password = is_string ($input['password'] ?? null) ? $input['password'] : '';
$noHp     = $str('no_hp');

$errors = [];
if (!preg_match('/^[A-Za-z0-9._-]{3,50}$/', $username)) {
    $errors['username'] = 'Username 3-50 karakter (huruf, angka, titik, underscore, strip).';
}
if ($email !== '' && (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 100)) {
    $errors['email'] = 'Format email tidak valid.';
}
if(strlen($password) < 8 || strlen($password) > 72) {
    $errors['password'] = 'Password 8-72 karakter';
}
if (in_array($role, ['pembeli', 'penjual'], true) && !preg_match('/^(\+62|62|0)8[0-9]{8,12}$/', $noHp)) {
    $errors['no_hp'] = 'Nomor HP tidak valid.';
}

$namaUsaha = $noHp = $alamat = '';
$idKategori = null;
if ($role === 'penjual') {
    $namaUsaha = $str('nama_usaha');
    if ($namaUsaha === '' || strlen($namaUsaha) > 100) {
        $errors['nama_usaha'] = 'Nama usaha wajib diisi (maks 100 karakter)';
    }
    $idKategori = filter_var($input['id_kategori_penjual'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($idKategori === false || $idKategori === null) {
        $errors['id_kategori_penjual'] = 'Kategori penjual wajib dipilih.';
    }
    $alamat = $str('alamat');
}

if ($errors) {
    jsonResponse(400, ['message' => 'Validasi gagal', 'errors' => $errors]);
}


try {
    $pdo  = getConnection();
    $pdo->beginTransaction();

    $stmt = $pdo->prepare(
        'INSERT INTO users (username, email, password_hash, status_akun)
         VALUES (:username, :email, :hash, :status)
         RETURNING id_user'
    );
    $stmt->execute([
        ':username' => $username,
        ':email'    => $email,
        ':no_hp'    => $role === 'pembeli' ? $noHp : null,
        ':hash'     => password_hash($password, PASSWORD_DEFAULT),
        ':status'   => $cfg['status_akun'],
    ]);
    $idUser = (int) $stmt->fetchColumn();
 
    $stmt = $pdo->prepare(
        'INSERT INTO user_roles (id_user, id_role)
         SELECT CAST(:id_user AS BIGINT), id_role FROM roles WHERE nama_role = :role'
    );
    $stmt->execute([':id_user' => $idUser, ':role' => $role]);
    if ($stmt->rowCount() === 0) {
        throw new RuntimeException("Role '$role' belum di-seed (jalankan 002_seed_rbac.sql)");
    }
 
    if ($role === 'pembeli') {
        $pdo->prepare('INSERT INTO keranjang (id_user) VALUES (:id)')->execute([':id' => $idUser]);
    }
 
    if ($role === 'penjual') {
        $pdo->prepare(
            'INSERT INTO profil_penjual (id_user, nama_usaha, id_kategori_penjual, no_hp, alamat)
             VALUES (:id_user, :nama, :kategori, :no_hp, :alamat)'
        )->execute([
            ':id_user'  => $idUser,
            ':nama'     => $namaUsaha,
            ':kategori' => $idKategori,
            ':no_hp'    => $noHp,
            ':alamat'   => $alamat !== '' ? $alamat : null,
        ]);
    }
 
    $pdo->commit();

     jsonResponse(201, [
        'success' => true,
        'message' => $cfg['pesan'],
        'data'    => ['id' => $idUser, 'role' => $role, 'status_akun' => $cfg['status_akun']],
    ]);

} catch (PDOException $e) {
     if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $kode = $e->getCode();
    $msg  = $e->getMessage();
 
    if ($kode === '23505') {
        if (str_contains($msg, 'users_email_key')) {
            jsonResponse(409, ['message' => 'Validasi gagal', 'errors' => ['email' => 'Email sudah terdaftar']]);
        }
        if (str_contains($msg, 'users_username_key')) {
            jsonResponse(409, ['message' => 'Validasi gagal', 'errors' => ['username' => 'Username sudah dipakai']]);
        }
        jsonResponse(409, ['message' => 'Data sudah terdaftar']);
    }
    if ($kode === '23503') {
        jsonResponse(400, ['message' => 'Validasi gagal', 'errors' => ['id_kategori_penjual' => 'Kategori tidak ditemukan']]);
    }
    error_log($msg);
    jsonResponse(500, ['message' => 'Terjadi kesalahan pada server']);
 
} catch (RuntimeException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log($e->getMessage());
    jsonResponse(500, ['message' => 'Terjadi kesalahan pada server']);
}
?>