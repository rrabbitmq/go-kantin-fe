<?php

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/repository.php';

$config = require __DIR__ . '/config.php';
$demoUserId = (int) ($config['demo_user_id'] ?? 1);

$currentUser = gk_get_user($demoUserId);
$cartCount = gk_get_cart_count($demoUserId);

require __DIR__ . '/head.php';
?>
  <div class="sb-app gk-app">
    <div class="sb-click-effect"></div>
    <div class="sb-load"></div>
<?php
require __DIR__ . '/sidebar.php';
?>
    <div class="gk-main">
<?php
require __DIR__ . '/topbar.php';
?>
      <div id="sb-dynamic-content" class="sb-transition-fade gk-content">
