<?php

namespace App\Core;

final class Autoloader
{
    public static function register(string $appPath): void
    {
        spl_autoload_register(static function (string $class) use ($appPath): void {
            $prefix = 'App\\';
            if (strncmp($prefix, $class, strlen($prefix)) !== 0) {
                return;
            }

            $relative = str_replace('\\', '/', substr($class, strlen($prefix)));
            $file = rtrim($appPath, '/\\') . '/'. $relative . '.php';

            if (is_file($file)) {
                require $file;
            }
        });
    }
}
