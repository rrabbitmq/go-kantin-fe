<?php
$currentUser = $currentUser ?? ['name' => 'Tamu'];
$activeNav = $activeNav ?? '';
$pageTitle = $pageTitle ?? 'Go Kantin';
$cartCount = (int) ($cartCount ?? 0);
$readyOrders = $readyOrders ?? [];
$jamOperasional = $jamOperasional ?? '09.00 - 15.00 WIB';

$navLabels = [
    'beranda' => 'Beranda',
    'menu' => 'Menu Kantin',
    'cart' => 'Keranjang',
    'status' => 'Pesanan',
    'queue' => 'Antrian',
    'profil' => 'Profil',
];
$crumb = $navLabels[$activeNav] ?? trim(preg_replace('/\s*[-—|].*$/u', '', (string) $pageTitle));
if ($crumb === '' || strcasecmp($crumb, 'Go Kantin') === 0) {
    $crumb = 'Beranda';
}

$readyCount = count($readyOrders);
$notifCount = $readyCount + ($cartCount > 0 ? 1 : 0);
$hasNotif = $notifCount > 0;
?>
      <header class="gk-topbar">
        <div class="gk-topbar-left">
          <button type="button" class="gk-menu-toggle" aria-label="Ciutkan sidebar">
            <i class="fas fa-angle-double-left"></i>
          </button>
        </div>
        <div class="gk-topbar-right">
          <span class="gk-topbar-hours">
            <i class="far fa-clock"></i>
            Jam Operasional: <strong><?= gk_h($jamOperasional) ?></strong>
          </span>
          <div class="gk-topbar-notif">
            <button
              type="button"
              class="gk-topbar-icon<?= $hasNotif ? ' has-notif' : '' ?>"
              id="gk-notif-toggle"
              aria-label="Notifikasi"
              aria-expanded="false"
              aria-controls="gk-notif-panel">
              <i class="far fa-bell"></i>
              <?php if ($hasNotif): ?>
                <em class="gk-dot"></em>
              <?php endif; ?>
            </button>
            <div class="gk-notif-panel" id="gk-notif-panel" hidden>
              <div class="gk-notif-head">Notifikasi</div>

              <div class="gk-notif-section">
                <div class="gk-notif-label">Pesanan siap diambil</div>
                <?php if ($readyOrders === []): ?>
                  <p class="gk-notif-empty">Belum ada pesanan siap diambil.</p>
                <?php else: ?>
                  <ul class="gk-notif-list">
                    <?php foreach ($readyOrders as $order): ?>
                      <li>
                        <a href="/user/status-pesanan.php?id=<?= (int) $order['id'] ?>">
                          <strong>#<?= gk_h((string) ($order['order_code'] ?? $order['id'])) ?></strong>
                          <span>Siap diambil · <?= gk_h(gk_format_rupiah($order['total_amount'] ?? 0)) ?></span>
                        </a>
                      </li>
                    <?php endforeach; ?>
                  </ul>
                <?php endif; ?>
              </div>

              <div class="gk-notif-section">
                <div class="gk-notif-label">Keranjang</div>
                <?php if ($cartCount > 0): ?>
                  <a class="gk-notif-cart" href="/user/cart.php">
                    <i class="fas fa-shopping-bag"></i>
                    <span>
                      <strong><?= $cartCount ?> item</strong>
                      belum di-checkout
                    </span>
                  </a>
                <?php else: ?>
                  <p class="gk-notif-empty">Keranjang masih kosong.</p>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      </header>
