<?php

use App\Core\Auth;
use App\Models\Keranjang;
use App\Models\Transaksi;
use App\Core\View;
$contentView = $contentView ?? '';

$currentUser = $currentUser ?? [
    'id' => 0,
    'name' => Auth::user()['username'] ?? 'Tamu',
    'student_id' => Auth::id() ? ('USR-' . Auth::id()) : '-',
];
$userId = (int) (Auth::id() ?: 0);
$cartCount = $cartCount ?? ($userId > 0 ? Keranjang::countForUser($userId) : 0);
$activeNav = $activeNav ?? '';
$pageTitle = $pageTitle ?? 'Go Kantin';
$jamOperasional = $jamOperasional ?? '09.00 - 15.00 WIB';

$readyOrders = [];
if ($userId > 0) {
    foreach (Transaksi::forUser($userId) as $order) {
        if (($order['status'] ?? '') === 'ready') {
            $readyOrders[] = $order;
        }
    }
}

View::partial('layouts/head', compact('pageTitle'));
?>
  <div class="sb-app gk-app">
    <div class="sb-click-effect"></div>
    <div class="sb-load"></div>
<?php View::partial('layouts/sidebar', compact('activeNav', 'currentUser', 'cartCount')); ?>
    <div class="gk-main">
<?php View::partial('layouts/topbar', compact(
    'currentUser',
    'activeNav',
    'pageTitle',
    'cartCount',
    'readyOrders',
    'jamOperasional'
)); ?>
      <div id="sb-dynamic-content" class="sb-transition-fade gk-content">
        <?php $flashOk = gk_flash('success'); $flashErr = gk_flash('error'); ?>
        <?php if ($flashOk): ?>
        <div class="gk-flash gk-flash-success" role="status" style="margin:16px 0;padding:12px 16px;border-radius:12px;background:#e8f7ee;color:#1b6b3a;font-weight:500"><?= gk_h($flashOk) ?></div>
        <?php endif; ?>
        <?php if ($flashErr): ?>
        <div class="gk-flash gk-flash-error" role="alert" style="margin:16px 0;padding:12px 16px;border-radius:12px;background:#fdeaea;color:#a12a2a;font-weight:500"><?= gk_h($flashErr) ?></div>
        <?php endif; ?>
        <?php require $contentView; ?>
      </div>
<?php View::partial('layouts/footer'); ?>
