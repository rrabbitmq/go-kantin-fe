<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Models\Keranjang;
use App\Models\Transaksi;
use App\Models\User;

final class ProfileController extends Controller
{
    private const PATH = '/user/profil.php';

    public function show(): void
    {
        $this->requireRole('pembeli');

        $userId = (int) Auth::id();
        $session = Auth::user() ?? [];
        $account = User::findById($userId) ?? [
            'username' => $session['username'] ?? '',
            'email' => $session['email'] ?? null,
            'status_akun' => $session['status_akun'] ?? 'aktif',
            'created_at' => null,
        ];

        $this->render('pembeli/profil', [
            'pageTitle' => 'Profil & Pengaturan - Go Kantin',
            'activeNav' => 'profil',
            'currentUser' => $this->currentUserForLayout(),
            'cartCount' => Keranjang::countForUser($userId),
            'account' => $account,
            'summary' => Transaksi::summaryForUser($userId),
            'old' => gk_flash('old', []),
            'flashSuccess' => gk_flash('success'),
            'flashError' => gk_flash('error'),
        ]);
    }

    public function handle(): void
    {
        $this->requireRole('pembeli');

        if (!gk_csrf_valid()) {
            $this->fail('Sesi formulir kedaluwarsa. Silakan coba lagi.');
        }

        if (!Database::available()) {
            $this->fail('Database tidak tersedia.');
        }

        match ($_POST['action'] ?? '') {
            'account' => $this->updateAccount(),
            'password' => $this->updatePassword(),
            default => $this->fail('Aksi tidak dikenal.'),
        };
    }

    private function updateAccount(): void
    {
        $userId = (int) Auth::id();
        $username = trim((string) ($_POST['username'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));

        $this->flash('old', ['username' => $username, 'email' => $email]);

        if (!preg_match('/^[a-zA-Z0-9._-]{3,50}$/', $username)) {
            $this->fail('Username 3–50 karakter (huruf, angka, titik, underscore, strip).');
        }

        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->fail('Format email tidak valid.');
        }

        if (User::usernameTakenByOther($username, $userId)) {
            $this->fail('Username sudah digunakan.');
        }

        if ($email !== '' && User::emailTakenByOther($email, $userId)) {
            $this->fail('Email sudah digunakan.');
        }

        try {
            User::updateAccount($userId, $username, $email);
        } catch (\Throwable $e) {
            $this->fail('Gagal menyimpan data akun.');
        }

        Auth::login(User::findById($userId), User::rolesForUser($userId), User::permissionsForUser($userId));

        gk_flash('old');
        $this->flash('success', 'Data akun berhasil diperbarui.');
        $this->redirect(self::PATH);
    }

    private function updatePassword(): void
    {
        $userId = (int) Auth::id();
        $current = (string) ($_POST['current_password'] ?? '');
        $new = (string) ($_POST['new_password'] ?? '');
        $confirm = (string) ($_POST['new_password_confirm'] ?? '');

        $hash = User::passwordHash($userId);
        if ($hash === null || !password_verify($current, $hash)) {
            $this->fail('Password saat ini salah.');
        }

        if (strlen($new) < 8) {
            $this->fail('Password baru minimal 8 karakter.');
        }

        if ($new !== $confirm) {
            $this->fail('Konfirmasi password baru tidak cocok.');
        }

        if (password_verify($new, $hash)) {
            $this->fail('Password baru harus berbeda dari password saat ini.');
        }

        try {
            User::updatePassword($userId, $new);
        } catch (\Throwable $e) {
            $this->fail('Gagal mengganti password.');
        }

        $this->flash('success', 'Password berhasil diganti.');
        $this->redirect(self::PATH);
    }

    private function fail(string $message): void
    {
        $this->flash('error', $message);
        $this->redirect(self::PATH);
    }
}
