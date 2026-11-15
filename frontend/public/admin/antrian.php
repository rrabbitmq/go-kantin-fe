<?php

require __DIR__ . '/../../app/bootstrap.php';

$controller = new App\Controllers\AdminController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller->ubahStatus();
} else {
    $controller->antrian();
}