<?php

namespace App\Core;

final class Auth
{
    private const SESSION_KEY = 'auth';

    public static function check(): bool
    {
        $auth = $_SESSION[self::SESSION_KEY] ?? null;
        if (!isset($auth['id_user'])) {
            return false;
        }

        if (isset($auth['token_exp']) && (int) $auth['token_exp'] <= time()) {
            unset($_SESSION[self::SESSION_KEY]);
            return false;
        }
 
        return true;
    }

    public static function guest(): bool
    {
        return !self::check();
    }

    public static function user(): ?array
    {
        return $_SESSION[self::SESSION_KEY] ?? null;
    }

    public static function id(): ?int
    {
        $user = self::user();
        return $user ? (int) $user['id_user'] : null;
    }

    public static function token(): ?string
    {
        return self::user()['token'] ?? null;
    }

    public static function roles(): array
    {
        return self::user()['roles'] ?? [];
    }

    public static function permissions(): array
    {
        return self::user()['permissions'] ?? [];
    }

    public static function hasRole(string $role): bool
    {
        return Rbac::hasRole(self::roles(), $role);
    }

    public static function can(string $permission): bool
    {
        return Rbac::can(self::permissions(), $permission);
    }

    public static function login(
        array $user,
        array $roles,
        array $permissions,
        ?string $token = null,
        int $expiresIn = 7200
    ): void {
        session_regenerate_id(true);

        $_SESSION[self::SESSION_KEY] = [
            'id_user' => (int) $user['id_user'],
            'username' => $user['username'],
            'email' => $user['email'] ?? null,
            'status_akun' => $user['status_akun'] ?? 'aktif',
            'roles' => array_values($roles),
            'permissions' => array_values($permissions),
            'token' => $token,
            'token_exp' => $token ? time() + $expiresIn : null,
        ];
    }

    public static function logout(): void
    {
        unset($_SESSION[self::SESSION_KEY]);
        session_regenerate_id(true);
    }

    public static function redirectPathForRoles(?array $roles = null): string
    {
        $roles = $roles ?? self::roles();
        $primary = Rbac::primaryRole($roles);

        return match ($primary) {
            'penjual' => '/admin/dashboard.php',
            'super_admin' => '/auth/pending-area.php',
            default => '/user/katalog.php',
        };
    }

    public static function requireAuth(): void
    {
        if (!self::check()) {
            gk_flash_set('error', 'Silakan login terlebih dahulu.');
            header('Location: /auth/login.php');
            exit;
        }
    }

    public static function requireGuest(): void
    {
        if (self::check()) {
            header('Location: ' . self::redirectPathForRoles());
            exit;
        }
    }

    public static function requireRole(string ...$roles): void
    {
        self::requireAuth();

        if (!Rbac::hasAnyRole(self::roles(), $roles)) {
            gk_flash_set('error', 'Anda tidak memiliki akses ke halaman tersebut.');
            header('Location: ' . self::redirectPathForRoles());
            exit;
        }
    }
}