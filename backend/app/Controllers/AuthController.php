<?php
namespace App\Controllers;

use App\Core\Database;
use App\Helpers\Response;

final class AuthController
{
    public function login(): void
    {
        $in    = json_decode(file_get_contents('php://input'), true) ?: [];
        $pdo   = Database::connection();
        $stmt  = $pdo->prepare("SELECT id_user, role, password FROM users WHERE email = :e");
        $stmt->execute([':e' => (string) ($in['email'] ?? '')]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$user || !password_verify((string) ($in['password'] ?? ''), $user['password'])) {
            Response::json(['ok' => false, 'message' => 'Email atau password salah.'], 401);
            return;
        }

        $token = bin2hex(random_bytes(32));
        $pdo->prepare("INSERT INTO api_tokens (id_user, token_hash, expires_at)
                       VALUES (:u, :h, CURRENT_TIMESTAMP, INTERVAL '1 DAY'))")
            ->execute([':u' => $user['id_user'], ':h' => hash('sha256', $token)]);

        Response::json(['ok' => true, 'data' => [
            'token' => $token,
            'user'  => ['id' => $user['id_user'], 'role' => $user['role']],
        ]]);
    }
}