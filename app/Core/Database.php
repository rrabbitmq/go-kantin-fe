<?php

namespace App\Core;

use PDO;
use PDOException;

final class Database
{
    private static ?PDO $pdo = null;
    private static bool $tried = false;

    public static function connection(): ?PDO
    {
        if (self::$tried) {
            return self::$pdo;
        }

        self::$tried = true;
        $db = require dirname(__DIR__, 2) . '/config/database.php';

        $dsn = sprintf(
            'pgsql:host=%s;port=%d;dbname=%s',
            $db['host'],
            (int) $db['port'],
            $db['dbname']
        );

        try {
            self::$pdo = new PDO($dsn, $db['user'], $db['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (PDOException $e) {
            self::$pdo = null;
        }

        return self::$pdo;
    }

    public static function available(): bool
    {
        return self::connection() instanceof PDO;
    }
}
