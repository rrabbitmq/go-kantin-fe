<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Models\Role;
use App\Models\User;

final class SuperAdminController extends Controller
{
    private const USERS_PATH = '/admin/pengguna.php';
    private const ROLES_PATH = '/admin/hak-akses.php';

    public function users(): void
    {
        $this->requireSuperAdmin();

        $this->renderAdmin('admin/pengguna', [
            'pageTitle' => 'Manajemen Pengguna - Go Kantin',
            'activeNav' => 'pengguna',
            'users' => User::allWithRoles(),
            'roles' => Role::all(),
            'statuses' => User::STATUSES,
            'currentUserId' => Auth::id(),
            'old' => gk_flash('old'),
        ]);
    }

    public function handleUsers(): void
    {
        $this->requireSuperAdmin();
        $this->guardPost(self::USERS_PATH);

        match ($_POST['action'] ?? '') {
            'save' => $this->saveUser(),
            'delete' => $this->deleteUser(),
            default => $this->fail(self::USERS_PATH, 'Aksi tidak dikenal.'),
        };
    }

    public function roles(): void
    {
        $this->requireSuperAdmin();

        $this->renderAdmin('admin/hak-akses', [
            'pageTitle' => 'Hak Akses - Go Kantin',
            'activeNav' => 'hak-akses',
            'roles' => Role::all(),
            'permissions' => Role::allPermissions(),
            'permissionMap' => Role::permissionMap(),
            'builtInRoles' => Role::BUILT_IN,
        ]);
    }

    public function handleRoles(): void
    {
        $this->requireSuperAdmin();
        $this->guardPost(self::ROLES_PATH);

        match ($_POST['action'] ?? '') {
            'permissions' => $this->savePermissions(),
            'create_role' => $this->createRole(),
            'delete_role' => $this->deleteRole(),
            default => $this->fail(self::ROLES_PATH, 'Aksi tidak dikenal.'),
        };
    }

    private function saveUser(): void
    {
        $idUser = (int) ($_POST['id_user'] ?? 0);
        $isEdit = $idUser > 0;
        $username = trim((string) ($_POST['username'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $status = (string) ($_POST['status_akun'] ?? '');
        $roleIds = array_map('intval', (array) ($_POST['roles'] ?? []));

        $this->flash('old', [
            'id_user' => $idUser,
            'username' => $username,
            'email' => $email,
            'status_akun' => $status,
            'roles' => $roleIds,
        ]);

        if ($isEdit && !User::findById($idUser)) {
            $this->fail(self::USERS_PATH, 'Pengguna tidak ditemukan.');
        }

        if (!preg_match('/^[a-zA-Z0-9._-]{3,50}$/', $username)) {
            $this->fail(self::USERS_PATH, 'Username 3–50 karakter (huruf, angka, titik, underscore, strip).');
        }

        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->fail(self::USERS_PATH, 'Format email tidak valid.');
        }

        if ((!$isEdit || $password !== '') && strlen($password) < 8) {
            $this->fail(self::USERS_PATH, 'Password minimal 8 karakter.');
        }

        if (!in_array($status, User::STATUSES, true)) {
            $this->fail(self::USERS_PATH, 'Status akun tidak valid.');
        }

        $roleIds = Role::existingIds($roleIds);
        if (!$roleIds) {
            $this->fail(self::USERS_PATH, 'Pilih minimal satu role.');
        }

        if (User::usernameTakenByOther($username, $idUser)) {
            $this->fail(self::USERS_PATH, 'Username sudah digunakan.');
        }

        if ($email !== '' && User::emailTakenByOther($email, $idUser)) {
            $this->fail(self::USERS_PATH, 'Email sudah digunakan.');
        }

        $isSelf = $isEdit && $idUser === Auth::id();
        if ($isSelf) {
            $roleNames = array_column(array_filter(
                Role::all(),
                fn ($r) => in_array((int) $r['id_role'], $roleIds, true)
            ), 'nama_role');

            if (!in_array('super_admin', $roleNames, true) || $status !== 'aktif') {
                $this->fail(self::USERS_PATH, 'Akun Anda sendiri harus tetap aktif dan memiliki role super_admin.');
            }
        }

        try {
            if ($isEdit) {
                User::update($idUser, $username, $email, $password, $status, $roleIds);
            } else {
                User::create($username, $email, $password, $status, $roleIds);
            }
        } catch (\Throwable $e) {
            $this->fail(self::USERS_PATH, 'Gagal menyimpan pengguna.');
        }

        if ($isSelf) {
            Auth::login(User::findById($idUser), User::rolesForUser($idUser), User::permissionsForUser($idUser));
        }

        gk_flash('old');
        $this->flash('success', $isEdit ? "Pengguna {$username} diperbarui." : "Pengguna {$username} ditambahkan.");
        $this->redirect(self::USERS_PATH);
    }

    private function deleteUser(): void
    {
        $idUser = (int) ($_POST['id_user'] ?? 0);

        if ($idUser === Auth::id()) {
            $this->fail(self::USERS_PATH, 'Anda tidak dapat menghapus akun sendiri.');
        }

        $user = User::findById($idUser);
        if (!$user) {
            $this->fail(self::USERS_PATH, 'Pengguna tidak ditemukan.');
        }

        try {
            User::delete($idUser);
        } catch (\PDOException $e) {
            $message = $e->getCode() === '23503'
                ? "Pengguna {$user['username']} masih memiliki data transaksi/profil penjual. Ubah statusnya menjadi nonaktif."
                : 'Gagal menghapus pengguna.';
            $this->fail(self::USERS_PATH, $message);
        }

        $this->flash('success', "Pengguna {$user['username']} dihapus.");
        $this->redirect(self::USERS_PATH);
    }

    private function savePermissions(): void
    {
        $idRole = (int) ($_POST['id_role'] ?? 0);
        $role = Role::find($idRole);
        if (!$role) {
            $this->fail(self::ROLES_PATH, 'Role tidak ditemukan.');
        }

        try {
            Role::syncPermissions($idRole, (array) ($_POST['permissions'] ?? []));
        } catch (\Throwable $e) {
            $this->fail(self::ROLES_PATH, 'Gagal menyimpan hak akses.');
        }

        if (Auth::hasRole($role['nama_role'])) {
            $id = (int) Auth::id();
            Auth::login(User::findById($id), User::rolesForUser($id), User::permissionsForUser($id));
        }

        $this->flash('success', "Hak akses role {$role['nama_role']} disimpan.");
        $this->redirect(self::ROLES_PATH);
    }

    private function createRole(): void
    {
        $nama = strtolower(trim((string) ($_POST['nama_role'] ?? '')));
        $deskripsi = trim((string) ($_POST['deskripsi'] ?? ''));

        if (!preg_match('/^[a-z0-9_]{3,50}$/', $nama)) {
            $this->fail(self::ROLES_PATH, 'Nama role 3–50 karakter (huruf kecil, angka, underscore).');
        }

        if (mb_strlen($deskripsi) > 255) {
            $this->fail(self::ROLES_PATH, 'Deskripsi maksimal 255 karakter.');
        }

        if (Role::nameExists($nama)) {
            $this->fail(self::ROLES_PATH, 'Nama role sudah ada.');
        }

        try {
            Role::create($nama, $deskripsi);
        } catch (\Throwable $e) {
            $this->fail(self::ROLES_PATH, 'Gagal menambah role.');
        }

        $this->flash('success', "Role {$nama} ditambahkan. Atur hak aksesnya di bawah.");
        $this->redirect(self::ROLES_PATH);
    }

    private function deleteRole(): void
    {
        $idRole = (int) ($_POST['id_role'] ?? 0);
        $role = Role::find($idRole);
        if (!$role) {
            $this->fail(self::ROLES_PATH, 'Role tidak ditemukan.');
        }

        if (in_array($role['nama_role'], Role::BUILT_IN, true)) {
            $this->fail(self::ROLES_PATH, 'Role bawaan tidak dapat dihapus.');
        }

        try {
            Role::delete($idRole);
        } catch (\PDOException $e) {
            $message = $e->getCode() === '23503'
                ? "Role {$role['nama_role']} masih dipakai pengguna. Lepaskan role dari pengguna terlebih dahulu."
                : 'Gagal menghapus role.';
            $this->fail(self::ROLES_PATH, $message);
        }

        $this->flash('success', "Role {$role['nama_role']} dihapus.");
        $this->redirect(self::ROLES_PATH);
    }

    private function requireSuperAdmin(): void
    {
        $this->requireRole('super_admin');
    }

    private function guardPost(string $back): void
    {
        if (!gk_csrf_valid()) {
            $this->fail($back, 'Sesi formulir kedaluwarsa. Silakan coba lagi.');
        }

        if (!Database::available()) {
            $this->fail($back, 'Database tidak tersedia.');
        }
    }

    private function fail(string $path, string $message): void
    {
        $this->flash('error', $message);
        $this->redirect($path);
    }
}
