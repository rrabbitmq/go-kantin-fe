<?php
if ($_SERVER['REQUEST_METHOD'] !== 'POST'){
    jsonResponse(405, ['message' => 'Method tidak diizinkan']);
}

$roleMap = [
    'pembeli' => [
        'tabel' => 'pembeli', 'id' => 'id_pembeli',
        'no_hp' => true, 'kategori' => false,
        'status_akun' => null,
        'pesan' => 'Registrasi berhasil. Silahkan login',
    ],
    'penjual' => [
        'tabel' => 'penjual', 'id' => 'id_penjual',
        'no_hp' => true, 'kategori' => true,
        'status_akun' => 'pending',
        'pesan' => 'Pendaftaran akun menunggu disetujui admin',
    ],
    'super_admin' => [
        'tabel' => 'super_admin', 'id' => 'id_super_admin',
        'no_hp' => false, 'kategori' => false,
        'status_awal' => null,
        'pesan' => 'Akun admin berhasil dibuat',
    ],
];

$input = getInput();

$role = $input['role'] ?? '';
if (!is_string($role) || !array_key_exists($role, $roleMap)) {
    jsonResponse(400, ['message' => 'validasi gagal', 'errors' => ['role' => 'Role tidak valid']]);
}
$cfg = $roleMap[$role];

if ($role === 'super_admin') {
    requireRole('super_admin');
}

$username = trim($input['username'] ?? '');
$email = strtolower (trim($input['email'] ?? ''));
$password = $input['password'] ?? '';

$errors = [];
if (strlen($username) > 100) {
   $errors['username'] = 'Username terlalu panjang! Maksimal 100 karakter';
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Format email tidak valid';
}
if(strlen($password) < 8) {
    $errors['password'] = 'Gagal, Password minimal 8 karakter';
}

$noHp = '';
if ($cfg['no_hp']) {
    $noHp = trim($input['no_hp'] ?? '');
    if (!preg_match('/^(\+62|62|0)8[0-9]{8,12}$/', $noHp)) {
    $errors['no_hp'] = 'Nomor HP tidak valid';
    }
}

$idKategoriPenjual = null;
if ($cfg['kategori']) {
    $idKategori = filter_var($input['id_kategori_penjual'] ?? null, FILTER_VALIDATE_INT,
             ['options' => ['min_range' => 1]]
    );
    if ($idKategoriPenjual === false) {
    $errors['id_kategori_penjual'] = 'Kategori penjual wajib dipilih';
}
}

if ($errors) {
    jsonResponse(400, ['message' => 'Validasi gagal', 'errors' => $errors]);
}

$data = [
    'username'      => $username,
    'email'         => $email,
    'password_hash' => password_hash($password, PASSWORD_DEFAULT),
];
if ($cfg['no_hp'])       $data['no_hp'] = $noHp;
if ($cfg['kategori'])    $data['id_kategori_penjual'] = $idKategoriPenjual;
if ($cfg['status_awal'] !== null) $data['status_akun'] = $cfg['status_awal'];

$kolom        = array_keys($data);
$placeholders = array_map(fn($k) => ':' . $k, $kolom);

$sql = sprintf(
    'INSERT INTO %s (%s) VALUES (%s) RETURNING %s',
    $cfg['tabel'], implode(', ', $kolom), implode(', ', $placeholders), $cfg['id']
);

try {
    $pdo  = getConnection();
    $stmt = $pdo->prepare($sql);
    $stmt->execute($data);

    $responData = ['id' => $stmt->fetchColumn(), 'role' => $role];
    if ($cfg['status_awal'] !== null) $responData['status_akun'] = $cfg['status_awal'];

    jsonResponse(201, ['message' => $cfg['pesan'], 'data' => $responData]);

} catch (PDOException $e) {
    $kode = $e->getCode();
    if ($kode === '23505') {
        jsonResponse(409, ['message' => 'Validasi gagal',
                           'errors' => ['email' => 'Email sudah terdaftar']]);
    }
    if ($kode === '23503') {
        jsonResponse(400, ['message' => 'Validasi gagal',
                           'errors' => ['id_kategori_penjual' => 'Kategori tidak ditemukan']]);
    }
    error_log($e->getMessage());
    jsonResponse(500, ['message' => 'Terjadi kesalahan pada server']);
}
?>