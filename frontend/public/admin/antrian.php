<?php

require __DIR__ . '/../../app/bootstrap.php';

$conroller = new App\Controllers\AdminController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller->ubahStatus();
} else {
    $conroller->antrian();
}
