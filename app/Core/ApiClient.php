<?php

namespace App\Core;

/**
 * Pemanggil API backend lewat cURL (server ke server).
*/

final class ApiClient
{
    private static function baseUrl(): string
    {
        static $url = null;
        if ($url === null) {
            $config = require dirname(__DIR__, 2) . '/config/app.php';
            $url = rtrim($config['api_base_url'] ?? 'http://127.0.0.1:8000/api', '/');
        }
        return $url;
    }
 
    public static function post(string $path, array $body = [], ?string $token = null): array
    {
        return self::request('POST', $path, $body, $token);
    }
 
    public static function get(string $path, ?string $token = null): array
    {
        return self::request('GET', $path, [], $token);
    }
 
    public static function request(string $method, string $path, array $body = [], ?string $token = null): array
    {
        $headers = ['Accept: application/json'];
        if ($token) {
            $headers[] = 'Authorization: Bearer ' . $token;
        }
 
        $ch = curl_init(self::baseUrl() . $path);
        $opts = [
            CURLOPT_CUSTOMREQUEST  => strtoupper($method),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT        => 15,
        ];
        if ($body) {
            $headers[] = 'Content-Type: application/json';
            $opts[CURLOPT_POSTFIELDS] = json_encode($body);
        }
        $opts[CURLOPT_HTTPHEADER] = $headers;
        curl_setopt_array($ch, $opts);
 
        $raw = curl_exec($ch);
        if ($raw === false) {
            error_log('ApiClient: ' . curl_error($ch));
            return [
                'ok' => false, 'status' => 0, 'data' => null, 'errors' => [],
                'message' => 'Server backend tidak dapat dihubungi. Pastikan backend sudah berjalan.',
            ];
        }
 
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $json = json_decode($raw, true);
        if (!is_array($json)) {
            $json = [];
        }
 
        return [
            'ok'      => $status >= 200 && $status < 300,
            'status'  => $status,
            'message' => $json['message'] ?? null,
            'errors'  => is_array($json['errors'] ?? null) ? $json['errors'] : [],
            'data'    => $json['data'] ?? null,
        ];
    }
}