<?php

require __DIR__ . '/../../app/bootstrap.php';

$controller = new App\Controllers\CartController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Jalankan penambahan barang via API Backend
    $controller->addFromForm();
} else {
    // Tampilkan halaman keranjang
    $controller->index();
}