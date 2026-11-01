<?php
$pageTitle = 'Antrian Kantin — Go Kantin';
$activeNav = 'beranda';
require __DIR__ . '/../includes/layout-start.php';

$slots = gk_get_queue_slots();
$currentSlot = gk_get_current_queue_slot();
$queueThreshold = 30;
$currentCount = $currentSlot ? (int) $currentSlot['queue_count'] : 0;
$currentLabel = gk_queue_label($currentCount, $queueThreshold);
?>
        <section class="gk-page-head">
          <h1>Antrian Kantin</h1>
          <p>Pantau kepadatan antrian per slot pengambilan hari ini.</p>
        </section>

        <div class="gk-panel gk-order-hero">
          <div class="gk-order-hero-left">
            <span class="gk-label">Slot saat ini</span>
            <strong class="gk-order-id"><?= gk_h($currentSlot['slot_label'] ?? '-') ?></strong>
            <span class="gk-badge gk-badge-warn"><?= gk_h($currentLabel) ?></span>
          </div>
          <div class="gk-order-eta-box">
            <div class="gk-order-eta-icon"><i class="fas fa-users"></i></div>
            <div>
              <span class="gk-label">Jumlah antrian</span>
              <strong><?= $currentCount ?> orang</strong>
            </div>
          </div>
        </div>

        <div class="gk-panel">
          <div class="gk-panel-head">
            <h2>Slot pengambilan</h2>
          </div>
          <div class="gk-slot-grid">
            <?php foreach ($slots as $slot): ?>
            <?php
              $count = (int) $slot['queue_count'];
              $label = gk_queue_label($count, $queueThreshold);
              $isBusy = $count > $queueThreshold;
            ?>
            <div class="gk-slot-inner"<?= $isBusy ? ' style="border-color:rgba(220,80,60,0.28)"' : '' ?> data-slot-id="<?= (int) $slot['id'] ?>">
              <span class="gk-slot-time"><?= gk_h($slot['slot_label']) ?></span>
              <span class="gk-slot-meta"<?= $isBusy ? ' style="color:#c0392b;font-weight:600"' : '' ?>>
                <?= $count ?> antrian · <?= gk_h($label) ?>
              </span>
            </div>
            <?php endforeach; ?>
          </div>
          <p style="margin:18px 0 0;font-size:13px;color:var(--gk-muted)">
            Antrian di atas <?= $queueThreshold ?> orang dianggap ramai. Pertimbangkan jadwalkan pesanan di checkout.
          </p>
          <div style="margin-top:16px;display:flex;gap:10px;flex-wrap:wrap">
            <a href="/user/checkout.php" class="gk-btn-primary gk-btn-inline">Ke Checkout</a>
            <a href="/user/menu.php" class="gk-btn-ghost gk-btn-inline">Lihat Menu</a>
          </div>
        </div>
<?php require __DIR__ . '/../includes/layout-end.php'; ?>
