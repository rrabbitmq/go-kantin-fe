<?php

use App\Core\Auth;
use App\Models\Keranjang;
use App\Core\View;

$currentUser = $currentUser ?? [
    'id' => 0,
    'name' => Auth::user()['username'] ?? 'Tamu',
    'student_id' => Auth::id() ? ('USR-' . Auth::id()) : '-',
];
$cartCount = $cartCount ?? (Auth::id() ? Keranjang::countForUser((int) Auth::id()) : 0);

View::partial('layouts/head', compact('pageTitle'));
?>
  <div class="sb-app gk-app">
    <div class="sb-click-effect"></div>
    <div class="sb-load"></div>
<?php View::partial('layouts/sidebar', compact('activeNav', 'currentUser', 'cartCount')); ?>
    <div class="gk-main">
<?php View::partial('layouts/topbar', compact('currentUser')); ?>
      <div id="sb-dynamic-content" class="sb-transition-fade gk-content">
        <?php require $contentView; ?>
      </div>
<?php View::partial('layouts/footer'); ?>
