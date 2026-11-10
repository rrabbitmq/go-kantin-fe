<?php
/** @var array $orders */
$statusLabels = [
    'dibayar' => 'Diterima',
    'diproses' => 'Dibuat',
    'siap_diambil' => 'Siap Ambil',
    'selesai' => 'Selesai',
    'dibatalkan' => 'Dibatalkan',
    'menunggu_pembayaran' => 'Menunggu',
];
$nextAction = [
    'dibayar' => ['label' => 'Mulai Siapkan Makanan', 'action' => 'Mulai proses'],
    'diproses' => ['label' => 'Tandai Siap Ambil', 'action' => 'Tandai siap ambil'],
    'siap_diambil' => ['label' => 'Serahkan & Selesai', 'action' => 'Serahkan & selesai'],
];
$slots = array_values(array_unique(array_map(static fn ($o) => $o['slot'], $orders)));
?>
        <div class="gk-admin-page-head">
          <div>
            <h1>Manajemen Antrean Digital &amp; Pesanan Masuk</h1>
            <p>Kelola pesanan mahasiswa, verifikasi QRIS, dan maju status sampai siap diambil.</p>
          </div>
        </div>

        <div class="gk-admin-toolbar">
          <div class="gk-admin-pills" role="tablist">
            <button type="button" class="gk-admin-pill is-active" data-filter="all">Semua</button>
            <button type="button" class="gk-admin-pill" data-filter="dibayar">Diterima</button>
            <button type="button" class="gk-admin-pill" data-filter="diproses">Dibuat</button>
            <button type="button" class="gk-admin-pill" data-filter="siap_diambil">Siap Ambil</button>
          </div>
          <select id="gk-antrian-slot" class="gk-admin-select" aria-label="Filter slot">
            <option value="">Semua slot jam</option>
            <?php foreach ($slots as $slot): ?>
            <option value="<?= gk_h($slot) ?>"><?= gk_h($slot) ?> WIB</option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="gk-admin-order-list">
          <?php foreach ($orders as $order): ?>
          <?php
            $status = $order['status'];
            $action = $nextAction[$status] ?? null;
          ?>
          <article
            class="gk-admin-order-card"
            data-status="<?= gk_h($status) ?>"
            data-slot="<?= gk_h($order['slot']) ?>"
            data-queue="<?= gk_h($order['queue']) ?>">
            <div>
              <div class="gk-admin-order-top">
                <span class="gk-admin-queue"><?= gk_h($order['queue']) ?></span>
                <strong><?= gk_h($order['kode']) ?></strong>
                <span class="gk-admin-status-chip is-<?= gk_h($status) ?>">
                  <?= gk_h($statusLabels[$status] ?? $status) ?>
                </span>
                <span>Ambil: <?= gk_h($order['slot']) ?> WIB</span>
                <span><?= gk_h($order['pembayar']) ?></span>
              </div>
              <div class="gk-admin-order-meta">
                <span><strong><?= gk_h($order['pembeli']) ?></strong> · <?= gk_h($order['nim']) ?></span>
                <span>Dipesan pukul <?= gk_h($order['waktu']) ?> WIB</span>
              </div>
              <div class="gk-admin-order-items">
                <?php foreach ($order['items'] as $item): ?>
                  <div><?= (int) $item['qty'] ?>x <?= gk_h($item['nama']) ?> (<?= gk_h(gk_format_rupiah($item['harga'])) ?>)</div>
                <?php endforeach; ?>
                <?php if ($order['catatan'] !== ''): ?>
                  <div class="gk-admin-order-note">Catatan: <?= gk_h($order['catatan']) ?></div>
                <?php endif; ?>
              </div>
            </div>
            <div class="gk-admin-order-actions">
              <div class="gk-admin-order-total"><?= gk_h(gk_format_rupiah($order['total'])) ?></div>
              <?php if ($order['metode'] === 'qris'): ?>
              <button type="button" class="gk-admin-btn gk-admin-btn-outline gk-admin-btn-sm" data-order-action="Cek bukti QRIS">
                Cek Bukti QRIS
              </button>
              <?php endif; ?>
              <?php if ($action): ?>
              <button type="button" class="gk-admin-btn" data-order-action="<?= gk_h($action['action']) ?>">
                <?= gk_h($action['label']) ?>
              </button>
              <?php endif; ?>
            </div>
          </article>
          <?php endforeach; ?>
        </div>
