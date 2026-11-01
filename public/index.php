<?php

require __DIR__ . '/../app/bootstrap.php';

use App\Core\Auth;

if (Auth::check()) {
    header('Location: ' . Auth::redirectPathForRoles());
    exit;
}

header('Location: /auth/login.php');
exit;
