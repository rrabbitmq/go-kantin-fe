<?php

require __DIR__ . '/../../app/bootstrap.php';

use App\Controllers\CheckoutController;
 
$controller = new CheckoutController();
 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller->store();
} else {
    $controller->index();
}