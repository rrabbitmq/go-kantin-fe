<?php
function handleCors(): void
{
    $allowed = array_map('trim', explode(',', getenv('CORS_ORIGINS')
        ?: 'http://localhost:8080,http://127.0.0.1:8080,http://localhost:5500,http://127.0.0.1:5500'));
 
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    if ($origin !== '' && in_array($origin, $allowed, true)) {
        header('Access-Control-Allow-Origin: ' . $origin);
        header('Vary: Origin');
    }
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
 
    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
        http_response_code(204);
        exit;
    }
}