<?php
require_once __DIR__ . '/response.php';
require_once __DIR__ . '/database.php';
 
const TOKEN_TTL = 7200; // 2 jam
 
function appSecret(): string
{
    $secret = getenv('APP_SECRET');
    if ($secret) {
        return $secret;
    }
    if (getenv('APP_ENV') === 'production') {
        error_log('APP_SECRET belum di-set');
        jsonResponse(500, ['message' => 'Terjadi kesalahan pada server']);
    }
    return 'dev-only-secret-ganti-di-produksi';
}
 
function b64urlEncode(string $data): string
{
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}
 
function b64urlDecode(string $data)
{
    return base64_decode(strtr($data, '-_', '+/'));
}
 
function createToken(int $idUser, string $role): string
{
    $now = time();
    $payload = [
        'sub'  => $idUser,
        'role' => $role,
        'jti'  => bin2hex(random_bytes(16)),
        'iat'  => $now,
        'exp'  => $now + TOKEN_TTL,
    ];
    $header = b64urlEncode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
    $body   = b64urlEncode(json_encode($payload));
    $sig    = b64urlEncode(hash_hmac('sha256', "$header.$body", appSecret(), true));
 
    return "$header.$body.$sig";
}
 
function getBearerToken(): ?string
{
    $h = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
    if ($h === '' && function_exists('getallheaders')) {
        foreach (getallheaders() as $k => $v) {
            if (strcasecmp($k, 'Authorization') === 0) {
                $h = $v;
            }
        }
    }
    return preg_match('/^Bearer\s+(\S+)$/i', $h, $m) ? $m[1] : null;
}
 
// Mengembalikan payload token (sub, role, jti, exp) atau langsung 401.
function verifyToken(): array
{
    $token = getBearerToken();
    if (!$token) {
        jsonResponse(401, ['message' => 'Token tidak ditemukan']);
    }
 
    $parts = explode('.', $token);
    if (count($parts) !== 3) {
        jsonResponse(401, ['message' => 'Token tidak valid']);
    }
    [$h, $b, $s] = $parts;
 
    $expected = b64urlEncode(hash_hmac('sha256', "$h.$b", appSecret(), true));
    if (!hash_equals($expected, $s)) {
        jsonResponse(401, ['message' => 'Token tidak valid']);
    }
 
    $payload = json_decode((string) b64urlDecode($b), true);
    if (!is_array($payload) || !isset($payload['exp'], $payload['jti'], $payload['sub'], $payload['role'])) {
        jsonResponse(401, ['message' => 'Token tidak valid']);
    }
    if ((int) $payload['exp'] < time()) {
        jsonResponse(401, ['message' => 'Token sudah kedaluwarsa']);
    }
 
    try {
        $stmt = getConnection()->prepare('SELECT 1 FROM token_blacklist WHERE jti = :jti');
        $stmt->execute([':jti' => $payload['jti']]);
        if ($stmt->fetchColumn()) {
            jsonResponse(401, ['message' => 'Token sudah tidak berlaku']);
        }
    } catch (PDOException $e) {
        error_log($e->getMessage());
        jsonResponse(500, ['message' => 'Terjadi kesalahan pada server']);
    }
 
    return $payload;
}
 
function revokeToken(string $jti, int $exp): void
{
    $pdo = getConnection();
    $pdo->exec('DELETE FROM token_blacklist WHERE expired_at < CURRENT_TIMESTAMP');
    $stmt = $pdo->prepare(
        'INSERT INTO token_blacklist (jti, expired_at)
         VALUES (:jti, to_timestamp(:exp))
         ON CONFLICT (jti) DO NOTHING'
    );
    $stmt->execute([':jti' => $jti, ':exp' => $exp]);
}
 
function requireRole(string ...$allowed): array
{
    $payload = verifyToken();
    if (!in_array($payload['role'], $allowed, true)) {
        jsonResponse(403, ['message' => 'Anda tidak memiliki akses']);
    }
    return $payload;
}
