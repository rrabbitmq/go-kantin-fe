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
$login = '';
foreach (['login', 'email'] as $k) {
    if (is_string($input[$k] ?? null) && trim($input[$k]) !== '') {
        $login = trim($input[$k]);
        break;
    }
}
$password = is_string($input['password'] ?? null) ? $input['password'] : '';
$roleDiminta = is_string($input['role'] ?? null) ? $input['role'] : '';

$errors = [];
if ($login === '' || strlen($login) > 100) {
    $errors['login'] = 'Username/email wajib diisi';
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
         FROM users WHERE username = :login OR email = :email LIMIT 1'
    );
    $stmt->execute([':login' => $login, ':email' => strtolower($login)]);
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

    $stmt = $pdo->prepare(
        'SELECT DISTINCT p.nama_permission
         FROM user_roles ur
         JOIN role_permissions rp ON rp.id_role = ur.id_role
         JOIN permissions p ON p.id_permission = rp.id_permission
         WHERE ur.id_user = :id
         ORDER BY p.nama_permission'
    );
    $stmt->execute([':id' => $user['id_user']]);
    $permissions = array_column($stmt->fetchAll(), 'nama_permission');

    if ($roleDiminta !== '') {
        if (!in_array($roleDiminta, $roles, true)) {
            jsonResponse(403, ['message' => 'Akun tidak memiliki role tersebut']);
        }
        $role = $roleDiminta;
    } else {
        $role = $roles[0];
        foreach (['super_admin', 'penjual', 'pembeli'] as $r) {
            if (in_array($r, $roles, true)) {
                $role = $r;
                break;
            }
        }
    }
    $token = createToken((int)$user['id_user'], $role);

    jsonResponse(200, [
        'success' => true,
        'message' => 'Login berhasil',
        'data'    => [
            'token' => $token,
            'expires_in' => TOKEN_TTL,
            'user'  => ['id' => (int)$user['id_user'], 'username' => $user['username'], 'role' => $role, 'roles' => $roles, 'permissions' => $permissions,
            ],
        ],
    ]);

} catch (PDOException $e) {
    error_log($e->getMessage());
    jsonResponse(500, ['message' => 'Terjadi kesalahan pada server']);
}
?>