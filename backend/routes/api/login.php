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

$pesanStatus = [
    'pending' => 'Akun Anda masih menunggu persetujuan admin',
    'ditolak'  => 'Pendaftaran akun ditolak',
    'nonaktif' => 'Akun anda tidak aktif',
];

$input = getInput();
$email    = strtolower(trim(is_string($input['email'] ?? null) ? $input['email'] : ''));
$password = is_string($input['password'] ?? null) ? $input['password'] : '';

$errors = [];
if (!filter_var($email, FILTER_VALIDATE_EMAIL)){
    $errors['email'] = 'Format email tidak valid';
}
if ($password === '') {
    $errors['password'] = 'Password wajib diisi';
}

if ($errors) {
    jsonResponse(400, ['message' => 'Validasi gagal', 'errors' => $errors]);
}

try {
    $pdo  = getConnection();
    $stmt = $pdo->prepare(
        'SELECT id_user, username, password_hash, status_akun
         FROM users WHERE email = :email LIMIT 1'
    );
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch();

    $passwordCocok = $user && password_verify($password, $user['password_hash']);
    if (!$passwordCocok) {
        jsonResponse(401, ['message' => 'Email atau password salah']);
    }

    if ($user['status_akun'] !== 'aktif') {
        jsonResponse(403, ['message' => $pesanStatus[$user['status_akun']] ?? 'Akun tidak aktif']);
    }


    $stmt = $pdo->prepare(
        'SELECT r.nama_role
         FROM user_roles ur JOIN roles r ON r.id_role = ur.id_role
         WHERE ur.id_user = :id ORDER BY r.id_role'
    );
    $stmt->execute([':id' => $user['id_user']]);
    $roles = array_column($stmt->fetchAll(), 'nama_role');
 
    if (!$roles) {
        jsonResponse(403, ['message' => 'Akun belum memiliki role']);
    }
    if ($roleDiminta !== '') {
        if (!in_array($roleDiminta, $roles, true)) {
            jsonResponse(403, ['message' => 'Akun tidak memiliki role tersebut']);
        }
        $role = $roleDiminta;
    } else {
        $role = $roles[0];
    }
    $token = createToken((int)$user['id_user'], $role);

    jsonResponse(200, [
        'success' => true,
        'message' => 'Login berhasil',
        'data'    => [
            'token' => $token,
            'user'  => ['id' => (int)$user['id_user'], 'username' => $user['username'], 'role' => $role, 'roles' => $roles,
            ],
        ],
    ]);

} catch (PDOException $e) {
    error_log($e->getMessage());
    jsonResponse(500, ['message' => 'Terjadi kesalahan pada server']);
}
?>