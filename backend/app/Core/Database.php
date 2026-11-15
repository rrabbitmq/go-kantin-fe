<?php

namespace App\Core;

use PDO;

require_once dirname(__DIR__, 2) . '/config/database.php';

final class Database
{
    public static function connection(): PDO
    {
        return \getConnection();
    } 
}