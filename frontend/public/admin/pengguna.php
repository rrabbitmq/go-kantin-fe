<?php

require __DIR__ . '/../../app/bootstrap.php';

use App\Controllers\SuperAdminController;

$controller = new SuperAdminController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller->handleUsers();
} else {
    $controller->users();
}
