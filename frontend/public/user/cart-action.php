<?php
 
require __DIR__ . '/../../app/bootstrap.php';
 
use App\Controllers\CartController;
 
(new CartController())->action();