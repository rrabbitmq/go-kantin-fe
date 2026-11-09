<?php
 
namespace App\Core;
 
/** Autoloader sederhana: App\Models\Produk -> app/Models/Produk.php */
final class Autoloader
{
    public static function register(string $appPath): void
    {
        spl_autoload_register(static function (string $class) use ($appPath): void {
            $prefix = 'App\\';
            if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
                return;
            }
 
            $relative = str_replace('\\', '/', substr($class, strlen($prefix)));
            $file = rtrim($appPath, '/\\') . '/' . $relative . '.php';
 
            if (is_file($file)) {
                require $file;
            }
        });
    }
}