<?php

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(405, ['message' => 'Method tidak diizinkan']);
}

$user = verifyToken();

try {
    revokeToken($user['jti'], (int)$user['exp']);
    jsonResponse(200, ['message' => 'Logout berhasil']);
} catch (PDOException $e) {
    error_log($e->getMessage());
    jsonResponse(500, ['message' => 'Terjadi kesalahan pada server']);
}
?>