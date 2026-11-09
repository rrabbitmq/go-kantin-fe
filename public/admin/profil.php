<?php

require __DIR__ . '/../../app/bootstrap.php';

use App\Controllers\ProfileController;

$controller = new ProfileController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller->handleAdmin();
} else {
    $controller->showAdmin();
}
