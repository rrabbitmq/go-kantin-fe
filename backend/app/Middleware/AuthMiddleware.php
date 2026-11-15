<?php
namespace App\Middleware;

use App\Core\Database;
use App\Helpers\Response;

final class AuthMiddleware
{
    private static ?array $user = null;

    public static function handle(?string $role = null): void
    {
        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (!preg_match('/Bearer\s+(\S+)/', $header, $m)) {
            self::deny(401, 'Token tidak ditemukan.');
        }

        $stmt = Database::connection()->prepare(
            "SELECT u.id_user AS id, u.role FROM api_tokens t
             JOIN users u ON u.id_user = t.id_user
             WHERE t.token_hash = :h AND t.expires_at > CURRENT_TIMESTAMP()"
        );
        $stmt->execute([':h' => hash('sha256', $m[1])]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$user) {
            self::deny(401, 'Token tidak valid atau kedaluwarsa.');
        }
        if ($role !== null && $user['role'] !== $role) {
            self::deny(403, 'Akses ditolak.');
        }
        self::$user = $user;
    }

    public static function user(): array
    {
        return self::$user ?? [];
    }

    private static function deny(int $status, string $msg): never
    {
        Response::json(['ok' => false, 'message' => $msg], $status);
        exit;
    }
}