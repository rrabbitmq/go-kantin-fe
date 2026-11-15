<?php

declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');

require APP_PATH . '/Core/Autoloader.php';
App\Core\Autoloader::register(APP_PATH);

require APP_PATH . '/Core/helpers.php';

$appConfig = require BASE_PATH . '/config/app.php';

date_default_timezone_set($appConfig['timezone'] ?? 'Asia/Jakarta');

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name($appConfig['session_name'] ?? 'go_kantin_session');
    session_start();
}
