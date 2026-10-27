<?php
$pageTitle = 'Status Pesanan — Go Kantin';
$activeNav = 'status';
require __DIR__ . '/../includes/layout-start.php';

$orderId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$order = $orderId > 0 ? gk_get_order($orderId) : gk_get_order(1);
$items = $order ? gk_get_order_items((int) $order['id']) : [];

$statusSteps = [
    'received' => ['title' => 'Diterima', 'desc' => 'Pesanan diterima', 'icon' => 'fas fa-check'],
    'processing' => ['title' => 'Dibuat', 'desc' => 'Sedang diproses', 'icon' => 'fas fa-utensils'],
    'ready' => ['title' => 'Siap Ambil', 'desc' => 'Menunggu', 'icon' => 'fas fa-shopping-bag'],
    'done' => ['title' => 'Selesai', 'desc' => 'Menunggu', 'icon' => 'fas fa-check'],
];

$statusOrder = ['received', 'processing', 'ready', 'done'];
$currentStatus = $order['status'] ?? 'processing';
if ($currentStatus === 'ready') {
    $currentIndex = 2;
} elseif ($currentStatus === 'done') {
    $currentIndex = 3;
} else {
    $currentIndex = 1;
}
?>
        <section class="gk-page-head">
          <h1>Status Pesanan</h1>
          <p>Pantau proses pesanan dan informasi pengambilan kamu.</p>
        </section>

        <?php if (!$order): ?>
        <div class="gk-panel">
          <h2>Pesanan tidak ditemukan</h2>
          <p>ID pesanan tidak valid.</p>
          <a href="/user/riwayat-pesanan.php" class="gk-btn-primary gk-btn-inline">Lihat Riwayat</a>
        </div>
        <?php else: ?>
        <div class="gk-panel gk-order-hero">
          <div class="gk-order-hero-left">
            <span class="gk-label">Nomor Pesanan</span>
            <strong class="gk-order-id">#<?= gk_h($order['order_code']) ?></strong>
            <span class="gk-badge <?= gk_h(gk_order_badge_class($order['status'])) ?>"><?= gk_h(gk_order_status_label($order['status'])) ?></span>
          </div>
          <div class="gk-order-eta-box">
            <div class="gk-order-eta-icon"><i class="far fa-clock"></i></div>
            <div>
              <span class="gk-label">Estimasi Siap Ambil</span>
              <strong>
                <?php if ($order['status'] === 'done'): ?>
                  Selesai
                <?php elseif ($order['status'] === 'ready'): ?>
                  Siap diambil
                <?php else: ?>
                  <?= (int) ($order['eta_minutes'] ?? 15) ?> Menit lagi
                <?php endif; ?>
              </strong>
            </div>
          </div>
        </div>

        <div class="gk-panel gk-stepper-card">
          <h2>Proses Pembuatan</h2>
          <ol class="gk-stepper">
            <?php foreach ($statusOrder as $i => $key): ?>
            <?php
              $step = $statusSteps[$key];
              $class = '';
              if ($i < $currentIndex) {
                  $class = 'gk-done';
                  $step['desc'] = $key === 'received' ? 'Pesanan diterima' : 'Selesai';
              } elseif ($i === $currentIndex) {
                  $class = 'gk-current';
                  if ($key === 'processing') {
                      $step['desc'] = 'Sedang diproses';
                  } elseif ($key === 'ready') {
                      $step['desc'] = 'Siap diambil';
                  } elseif ($key === 'done') {
                      $step['desc'] = 'Pesanan selesai';
                  }
              }
            ?>
            <li class="gk-step <?= $class ?>">
              <span class="gk-step-dot"><i class="<?= gk_h($step['icon']) ?>"></i></span>
              <span class="gk-step-title"><?= gk_h($step['title']) ?></span>
              <span class="gk-step-desc"><?= gk_h($step['desc']) ?></span>
            </li>
            <?php endforeach; ?>
          </ol>
        </div>

        <div class="gk-status-grid">
          <div class="gk-status-main">
            <div class="gk-panel">
              <div class="gk-panel-head">
                <h2>Detail Item</h2>
              </div>
              <div class="gk-order-items">
                <?php foreach ($items as $item): ?>
                <div class="gk-order-item">
                  <div class="gk-order-item-thumb">
                    <img src="<?= gk_h($item['image_path']) ?>" alt="<?= gk_h($item['product_name']) ?>">
                  </div>
                  <div class="gk-order-item-body">
                    <strong><?= gk_h($item['product_name']) ?></strong>
                    <span>
                      <?= (int) $item['quantity'] ?> item
                      <?php if (!empty($item['note'])): ?>
                        • <?= gk_h($item['note']) ?>
                      <?php endif; ?>
                    </span>
                  </div>
                  <div class="gk-order-item-price"><?= gk_h(gk_format_rupiah($item['unit_price'] * $item['quantity'])) ?></div>
                </div>
                <?php endforeach; ?>
              </div>
            </div>

            <div class="gk-panel">
              <div class="gk-panel-head">
                <h2>Metode Pengambilan</h2>
              </div>
              <div class="gk-pickup-row">
                <div class="gk-pickup-icon"><i class="fas fa-store"></i></div>
                <div class="gk-pickup-meta">
                  <strong><?= gk_h($order['pickup_label']) ?></strong>
                  <span><?= gk_h($order['pickup_location']) ?></span>
                </div>
                <span class="gk-badge gk-badge-accent">Pengambilan</span>
              </div>
            </div>
          </div>

          <aside class="gk-panel gk-status-summary">
            <div class="gk-panel-head">
              <h2>Ringkasan Pesanan</h2>
            </div>
            <div class="gk-summary-line">
              <span><?= (int) $order['menu_count'] ?> menu • <?= (int) $order['item_count'] ?> item</span>
              <span></span>
            </div>
            <div class="gk-summary-line">
              <span>Subtotal</span>
              <span><?= gk_h(gk_format_rupiah($order['total_amount'])) ?></span>
            </div>
            <div class="gk-summary-total gk-summary-total-accent">
              <span>Total Pembayaran</span>
              <span><?= gk_h(gk_format_rupiah($order['total_amount'])) ?></span>
            </div>
            <a href="/user/riwayat-pesanan.php" class="gk-btn-primary">Lihat Riwayat Pesanan</a>
            <div class="gk-summary-foot">
              <i class="fas fa-map-marker-alt"></i>
              <span><?= gk_h($order['pickup_location']) ?></span>
            </div>
          </aside>
        </div>
        <?php endif; ?>
<?php require __DIR__ . '/../includes/layout-end.php'; ?>
