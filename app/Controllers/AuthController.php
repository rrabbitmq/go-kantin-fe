<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Rbac;
use App\Models\User;

final class AuthController extends Controller
{
    public function showLogin(string $intent = 'pembeli'): void
    {
        $this->requireGuest();

        $this->render('auth/login', [
            'pageTitle' => $intent === 'penjual' ? 'Login Penjual - Go Kantin' : 'Login - Go Kantin',
            'intent' => $intent,
            'error' => gk_flash('error'),
            'success' => gk_flash('success'),
            'old' => gk_flash('old', []),
        ], 'layouts/auth');
    }

    public function login(string $intent = 'pembeli'): void
    {
        $this->requireGuest();

        if (!Database::available()) {
            $this->flash('error', 'Database tidak tersedia. Pastikan PostgreSQL dan seed RBAC sudah dijalankan.');
            $this->redirect($intent === 'penjual' ? '/auth/penjual/login.php' : '/auth/login.php');
        }

        $login = trim((string) ($_POST['login'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        $this->flash('old', ['login' => $login]);

        if ($login === '' || $password === '') {
            $this->flash('error', 'Username/email dan password wajib diisi.');
            $this->redirect($intent === 'penjual' ? '/auth/penjual/login.php' : '/auth/login.php');
        }

        $user = User::findByLogin($login);
        if (!$user || !password_verify($password, $user['password_hash'])) {
            $this->flash('error', 'Username/email atau password salah.');
            $this->redirect($intent === 'penjual' ? '/auth/penjual/login.php' : '/auth/login.php');
        }

        if (($user['status_akun'] ?? '') !== 'aktif') {
            $message = match ($user['status_akun']) {
                'pending' => 'Akun masih menunggu konfirmasi.',
                'ditolak' => 'Akun ditolak. Hubungi admin.',
                'nonaktif' => 'Akun nonaktif.',
                default => 'Status akun tidak valid untuk login.',
            };
            $this->flash('error', $message);
            $this->redirect($intent === 'penjual' ? '/auth/penjual/login.php' : '/auth/login.php');
        }

        $roles = User::rolesForUser((int) $user['id_user']);
        $permissions = User::permissionsForUser((int) $user['id_user']);

        if ($intent === 'penjual' && !Rbac::hasAnyRole($roles, ['penjual', 'super_admin'])) {
            $this->flash('error', 'Akun ini tidak memiliki akses penjual/admin.');
            $this->redirect('/auth/penjual/login.php');
        }

        Auth::login($user, $roles, $permissions);
        $this->redirect(Auth::redirectPathForRoles($roles));
    }

    public function showRegister(): void
    {
        $this->requireGuest();

        $this->render('auth/register', [
            'pageTitle' => 'Daftar Pembeli - Go Kantin',
            'error' => gk_flash('error'),
            'old' => gk_flash('old', []),
        ], 'layouts/auth');
    }

    public function register(): void
    {
        $this->requireGuest();

        if (!Database::available()) {
            $this->flash('error', 'Database tidak tersedia. Pastikan PostgreSQL dan seed RBAC sudah dijalankan.');
            $this->redirect('/auth/register.php');
        }

        $username = trim((string) ($_POST['username'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $passwordConfirm = (string) ($_POST['password_confirm'] ?? '');

        $this->flash('old', [
            'username' => $username,
            'email' => $email,
        ]);

        if ($username === '' || $password === '') {
            $this->flash('error', 'Username dan password wajib diisi.');
            $this->redirect('/auth/register.php');
        }

        if (!preg_match('/^[a-zA-Z0-9._-]{3,50}$/', $username)) {
            $this->flash('error', 'Username 3–50 karakter (huruf, angka, titik, underscore, strip).');
            $this->redirect('/auth/register.php');
        }

        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->flash('error', 'Format email tidak valid.');
            $this->redirect('/auth/register.php');
        }

        if (strlen($password) < 8) {
            $this->flash('error', 'Password minimal 8 karakter.');
            $this->redirect('/auth/register.php');
        }

        if ($password !== $passwordConfirm) {
            $this->flash('error', 'Konfirmasi password tidak cocok.');
            $this->redirect('/auth/register.php');
        }

        if (User::usernameExists($username)) {
            $this->flash('error', 'Username sudah digunakan.');
            $this->redirect('/auth/register.php');
        }

        if ($email !== '' && User::emailExists($email)) {
            $this->flash('error', 'Email sudah digunakan.');
            $this->redirect('/auth/register.php');
        }

        try {
            $idUser = User::createPembeli($username, $email, $password);
            $user = User::findById($idUser);
            $roles = User::rolesForUser($idUser);
            $permissions = User::permissionsForUser($idUser);
            Auth::login([
                'id_user' => $idUser,
                'username' => $user['username'],
                'email' => $user['email'] ?? null,
                'status_akun' => $user['status_akun'],
            ], $roles, $permissions);
            $this->flash('success', 'Registrasi berhasil. Selamat datang!');
            $this->redirect('/user/katalog.php');
        } catch (\Throwable $e) {
            $this->flash('error', 'Gagal mendaftar. Pastikan role pembeli sudah di-seed.');
            $this->redirect('/auth/register.php');
        }
    }

    public function logout(): void
    {
        Auth::logout();
        $this->flash('success', 'Anda sudah logout.');
        $this->redirect('/auth/login.php');
    }

    public function pendingArea(): void
    {
        $this->requireRole('penjual', 'super_admin');

        $roles = Auth::roles();
        $primary = Rbac::primaryRole($roles);

        $this->render('auth/pending-area', [
            'pageTitle' => 'Area Belum Tersedia - Go Kantin',
            'primaryRole' => $primary,
            'username' => Auth::user()['username'] ?? '',
        ], 'layouts/auth');
    }
}
