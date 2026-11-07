<?php
if ($_SERVER['REQUEST_METHOD'] !== 'POST'){
    jsonResponse(405, ['message' => 'Method tidak diizinkan']);
}

$roleMap = [
    'pembeli'     => ['tabel' => 'pembeli',     'id' => 'id_pembeli',      'kolom_status' => null],
    'penjual'     => ['tabel' => 'penjual',     'id' => 'id_penjual',      'kolom_status' => 'status_akun'],
    'super_admin' => ['tabel' => 'super_admin', 'id' => 'id_super_admin',  'kolom_status' => null],
];

$pesanStatus = [
    'pending' => 'Akun Anda masih menunggu persetujuan admin',
    'reject'  => 'Pendaftaran akun ditolak',
    'approve' => 'Akun anda telah disetujui oleh admin',
];

$input = getInput();

$role     = $input['role'] ?? '';
$email    = strtolower(trim($input['email'] ?? ''));
$password = $input['password'] ?? '';

$errors = [];
if (!is_string($role) || !array_key_exists($role, $roleMap)) $errors['role'] = 'Role tidak valid';
if (!filter_var($email, FILTER_VALIDATE_EMAIL))              $errors['email'] = 'Format email tidak valid';
if (!is_string($password) || $password === '')               $errors['password'] = 'Password wajib diisi';

if ($errors) {
    jsonResponse(400, ['message' => 'Validasi gagal', 'errors' => $errors]);
}

$cfg = $roleMap[$role];

$kolom = "{$cfg['id']} AS id, username, password_hash";
if ($cfg['kolom_status'] !== null) {
    $kolom .= ", {$cfg['kolom_status']} AS status";
}
$sql = "SELECT $kolom FROM {$cfg['tabel']} WHERE email = :email";

try {
    $pdo  = getConnection();
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch();

    $password = password_verify($password, $hashDb);

    if (!$user || !$passwordCocok) {
        jsonResponse(401, ['message' => 'Email atau password salah']);
    }

    if ($cfg['kolom_status'] !== null && $user['status'] !== 'aktif') {
        jsonResponse(403, ['message' => $pesanStatus[$user['status']] ?? 'Akun tidak aktif']);
    }

    $token = createToken((int)$user['id'], $role);

    jsonResponse(200, [
        'success' => true,
        'message' => 'Login berhasil',
        'data'    => [
            'token' => $token,
            'user'  => ['id' => (int)$user['id'], 'username' => $user['username'], 'role' => $role],
        ],
    ]);

} catch (PDOException $e) {
    error_log($e->getMessage());
    jsonResponse(500, ['message' => 'Terjadi kesalahan pada server']);
}
?>