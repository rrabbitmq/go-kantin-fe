<?php

/**
 * @var array       $currentUser
 * @var string      $query
 * @var int         $menuCount
 * @var array       $categories
 * @var int         $cartCount
 * @var array|null  $activeOrder
 * @var array       $cartItems
 * @var float       $cartTotal
 * @var array       $queueDensity
 * @var array       $carousels
 */
use App\Core\View;

$queueDensity = $queueDensity ?? [
    'aktif' => 0,
    'kapasitas' => 20,
    'persen' => 0,
    'label' => 'Sepi',
    'slot_label' => '-',
];
$carousels = $carousels ?? [];
$queueTone = gk_queue_density_tone((string) $queueDensity['label']);
$queueBusy = in_array($queueTone, ['ramai', 'penuh'], true);
?>
<div class="gk-home-greeting">
  <h1>Halo, <?= gk_h($currentUser['name'] ?? 'Tamu') ?></h1>
  <p>Pesan makan di kantin, pantau antrian, lalu ambil saat siap.</p>
</div>

<div class="gk-stats">
  <a href="/user/menu.php" class="gk-stat-card">
    <span class="gk-stat-icon"><i class="fas fa-utensils"></i></span>
    <span class="gk-stat-body">
      <strong><?= (int) $menuCount ?></strong>
      <span>Menu tersedia</span>
    </span>
  </a>
  <a href="/user/cart.php" class="gk-stat-card">
    <span class="gk-stat-icon"><i class="fas fa-shopping-bag"></i></span>
    <span class="gk-stat-body">
      <strong><?= (int) $cartCount ?></strong>
      <span>Item di keranjang</span>
    </span>
  </a>
  <a href="/user/status-pesanan.php" class="gk-stat-card">
    <span class="gk-stat-icon"><i class="fas fa-receipt"></i></span>
    <span class="gk-stat-body">
      <strong><?= $activeOrder ? '1' : '0' ?></strong>
      <span>Pesanan berjalan</span>
    </span>
  </a>
  <a href="/user/queue.php" class="gk-stat-card<?= $queueBusy ? ' gk-stat-card-busy' : '' ?> gk-density-card is-<?= gk_h($queueTone) ?>">
    <span class="gk-stat-badge"><?= gk_h((string) $queueDensity['label']) ?></span>
    <span class="gk-stat-icon"><i class="far fa-clock"></i></span>
    <span class="gk-stat-body">
      <strong><?= (int) $queueDensity['aktif'] ?> / <?= (int) $queueDensity['kapasitas'] ?></strong>
      <span>Antrian · <?= gk_h((string) $queueDensity['slot_label']) ?></span>
    </span>
  </a>
</div>

<div class="gk-home-bottom">
  <div class="gk-panel">
    <div class="gk-panel-head">
      <h2>Pesanan berjalan</h2>
      <?php if ($activeOrder): ?>
        <span class="gk-status"><?= gk_h(gk_order_status_label((string) $activeOrder['status'])) ?></span>
      <?php endif; ?>
    </div>
    <?php if (!$activeOrder): ?>
      <p class="gk-order-meta">Belum ada pesanan yang sedang diproses.</p>
      <a href="/user/menu.php" class="gk-btn-primary">Pesan sekarang</a>
    <?php else: ?>
      <p class="gk-order-meta">#<?= gk_h($activeOrder['order_code']) ?> · <?= gk_h($activeOrder['pickup_label'] ?? 'Ambil di kantin') ?></p>
      <div class="gk-eta">
        <div>
          <span>Total pesanan</span>
          <strong><?= gk_h(gk_format_rupiah($activeOrder['total_amount'])) ?></strong>
        </div>
        <a href="/user/status-pesanan.php?id=<?= (int) $activeOrder['id'] ?>" class="gk-btn-ghost">Lihat status</a>
      </div>
    <?php endif; ?>
  </div>
  <div class="gk-panel">
    <div class="gk-panel-head">
      <h2>Keranjang</h2>
    </div>
    <?php if ($cartItems === []): ?>
      <p class="gk-order-meta">Keranjang masih kosong.</p>
    <?php else: ?>
      <?php foreach ($cartItems as $item): ?>
        <div class="gk-summary-line">
          <span><?= (int) $item['quantity'] ?>× <?= gk_h($item['name']) ?></span>
          <span><?= gk_h(gk_format_rupiah($item['line_total'])) ?></span>
        </div>
      <?php endforeach; ?>
      <div class="gk-summary-total">
        <span>Total</span>
        <span><?= gk_h(gk_format_rupiah($cartTotal)) ?></span>
      </div>
    <?php endif; ?>
    <a href="/user/cart.php" class="gk-btn-primary">Buka keranjang</a>
  </div>
</div>

<?php foreach ($carousels as $carousel): ?>
  <?php
  View::partial('pembeli/partials/menu-carousel', [
      'title' => (string) ($carousel['title'] ?? 'Menu'),
      'subtitle' => (string) ($carousel['subtitle'] ?? ''),
      'products' => $carousel['products'] ?? [],
      'moreHref' => (string) ($carousel['more_href'] ?? '/user/menu.php'),
      'moreLabel' => (string) ($carousel['more_label'] ?? 'Selengkapnya'),
  ]);
  ?>
<?php endforeach; ?>
