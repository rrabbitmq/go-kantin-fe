<?php

require __DIR__ . '/app/Core/Autoloader.php';
App\Core\Autoloader::register(__DIR__ . '/app');

$classes = [
    'App\Core\Database',
    'App\Core\HttpException',
    'App\Models\KategoriProduk',
    'App\Models\Produk',
    'App\Models\Keranjang',
    'App\Models\Transaksi',
    'App\Services\CheckoutService',
];

foreach ($classes as $class) {
    $file = __DIR__ . '/app/' . str_replace('\\', '/', substr($class, 4)) . '.php';
    $adaFile = is_file($file);
    $adaClass = class_exists($class);

    echo str_pad($class, 32)
        . str_pad($adaFile ? 'file ada' : 'FILE HILANG', 14)
        . ($adaClass ? 'class OK' : 'CLASS TIDAK DITEMUKAN')
        . PHP_EOL;
}