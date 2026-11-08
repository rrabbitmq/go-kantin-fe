<?php

namespace App\Controllers;

use App\Core\ApiClient;
use App\Core\Auth;
use App\Core\Controller;
use App\Core\Rbac;

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
        
        $loginUrl = $intent === 'penjual' ? '/auth/penjual/login.php' : '/auth/login.php';
        $login = trim((string) ($_POST['login'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        $this->flash('old', ['login' => $login]);

        if ($login === '' || $password === '') {
            $this->flash('error', 'Username/email dan password wajib diisi.');
            $this->redirect($loginUrl);
        }

        $res = ApiClient::post('/login', ['login' => $login, 'password' => $password]);
        if (!$res['ok']) {
            $this->flash('error', $res['message'] ?? 'Login gagal.');
            $this->redirect($loginUrl);
        }

        $roles = $res['data']['user']['roles'] ?? [];


        if ($intent === 'penjual' && !Rbac::hasAnyRole($roles, ['penjual', 'super_admin'])) {
            $this->flash('error', 'Akun ini tidak memiliki akses penjual/admin.');
            $this->redirect('/auth/penjual/login.php');
        }

        $this->startSession($res['data']);
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

        $username = trim((string) ($_POST['username'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $noHp = trim((string) ($_POST['no_hp'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $passwordConfirm = (string) ($_POST['password_confirm'] ?? '');

        $this->flash('old', [
            'username' => $username,
            'email' => $email,
            'no_hp' => $noHp,
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

        if (!preg_match('/^(\+62|62|0)8[0-9]{8,12}$/', $noHp)) {
            $this->flash('error', 'Nomor HP tidak valid.');
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
        
        $res = ApiClient::post('/register', [
            'role' => 'pembeli',
            'username' => $username,
            'email' => $email,
            'no_hp' => $noHp,
            'password' => $password,
        ]);

        if (!$res['ok']) {
            $messages = array_map('strval', array_values($res['errors']));
            $this->flash('error', $messages ? implode(' ', $messages) : ($res['message'] ?? 'Gagal mendaftar.'));
            $this->redirect('/auth/register.php');
        }

        // Daftar berhasil -> langsung login supaya pengguna tidak perlu mengetik ulang
        $loginRes = ApiClient::post('/login', ['login' => $username, 'password' => $password]);
        if ($loginRes['ok']) {
            $this->startSession($loginRes['data']);
            $this->flash('success', 'Registrasi berhasil. Selamat datang!');
            $this->redirect('/user/katalog.php');
        }
 
        $this->flash('success', 'Registrasi berhasil. Silakan login.');
        $this->redirect('/auth/login.php');
    }
 
    public function logout(): void
    {
        $token = Auth::token();
        if ($token) {
            ApiClient::post('/logout', [], $token); // cabut token di backend
        }
 
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
 
    /** Simpan hasil login API (token + data user) ke session. */
    private function startSession(array $data): void
    {
        $user = $data['user'] ?? [];
 
        Auth::login(
            $user,
            $user['roles'] ?? [],
            $user['permissions'] ?? [],
            $data['token'] ?? null,
            (int) ($data['expires_in'] ?? 7200)
        );
    }
}
