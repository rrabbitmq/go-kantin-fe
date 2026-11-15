<?php

require __DIR__ . '/../../app/bootstrap.php';

$controller = new App\Controllers\AdminController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? 'simpan';

    if ($aksi === 'nonaktif') {
        $controller->nonaktifkanMenu();
    } elseif ($aksi === 'stok') {
        $controller->ubahStok();
    } else {
        $controller->simpanMenu();
    }
} else {
    $controller->menu();
}