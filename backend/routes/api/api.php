<?php
use App\Controllers\AuthController;
use App\Controllers\CartController;

/** @var \App\Core\Router $router */
$router->post('/api/login',        [AuthController::class, 'login']);
$router->get('/api/cart',          [CartController::class, 'index']);
$router->post('/api/cart',         [CartController::class, 'add']);
$router->put('/api/cart/{id}',     [CartController::class, 'update']);
$router->delete('/api/cart/{id}',  [CartController::class, 'remove']);