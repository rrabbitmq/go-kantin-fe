<?php

require __DIR__ . '/../../app/bootstrap.php';

use App\Controllers\AuthController;

$controller = new AuthController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller->login('pembeli');
} else {
    $controller->showLogin('pembeli');
}
