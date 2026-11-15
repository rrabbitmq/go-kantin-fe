<?php
/** @var array $orders */
$statusLabels = [
    'dibayar' => 'Diterima',
    'diproses' => 'Dibuat',
    'siap_diambil' => 'Siap Ambil',
    'selesai' => 'Selesai',
    'dibatalkan' => 'Dibatalkan',
    'menunggu_pembayaran' => 'Menunggu Konfirmasi',
];
$nextAction = [
    'menunggu_pembayaran' => ['label' => 'Konfirmasi Pesanan', 'aksi' => 'konfirmasi'],
    'dibayar' => ['label' => 'Mulai Siapkan Makanan', 'aksi' => 'proses'],
    'diproses' => ['label' => 'Tandai Siap Ambil', 'aksi' => 'siap'],
    'siap_diambil' => ['label' => 'Serahkan & Selesai', 'aksi' => 'selesai'],
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
            <button type="button" class="gk-admin-pill" data-filter="menunggu_pembayaran">Menunggu</button>
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
          <?php if (!$orders): ?>
          <p class="gk-admin-empty">Belum ada pesanan masuk.</p>
          <?php endif; ?>
          <?php foreach ($orders as $order): ?>
          <?php
            $status = $order['status'];
            $action = $nextAction[$status] ?? null;
            $metodeBayar = $order['metode'] ?? 'tunai';
            if ($action && $status === 'menunggu_pembayaran') {
              $action['label'] = $metodeBayar === 'qris' ? 'Verifikasi Pembayaran QRIS' : 'Terima Pesanan';
            }
            if ($action && $status === 'siap_diambil' && $metodeBayar !== 'qris') {
              $action['label'] = 'Terima Tunai & selesai';
            }
          ?>
          <article
            class="gk-admin-order-card"
            data-status="<?= gk_h($status) ?>"
            data-slot="<?= gk_h($order['slot']) ?>"
            data-queue="<?= gk_h($order['queue']) ?>"
            data-order-id="<?= (int) ($order['id'] ?? 0) ?>">
            <div>
              <div class="gk-admin-order-top">
                <span class="gk-admin-queue"><?= gk_h($order['queue']) ?></span>
                <strong><?= gk_h($order['kode']) ?></strong>
                <span class="gk-admin-status-chip is-<?= gk_h($status) ?>">
                  <?= gk_h($statusLabels[$status] ?? $status) ?>
                </span>
                <span>Ambil: <?= gk_h($order['slot']) ?><?= preg_match('/\d/', $order['slot']) ? ' WIB' : '' ?></span>
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
              <?php if ($action): ?>
              <form method="post" action="/admin/antrian.php">
                <input type="hidden" name="id" value="<?= (int) ($order['id'] ?? 0) ?>">
                <input type="hidden" name="aksi" value="<?= gk_h($action['aksi']) ?>">
                <button type="submit" class="gk-admin-btn"><?= gk_h($action['label']) ?></button>
              </form>
              <?php endif; ?>
              <?php if (in_array($status, ['menunggu_pembayaran', 'dibayar', 'diproses'], true)): ?>
              <form method="post" action="/admin/antrian.php" onsubmit="return confirm('Batalkan pesanan ini? Stok akan dikembalikan.');">
                <input type="hidden" name="id" value="<?= (int) ($order['id'] ?? 0) ?>">
                <input type="hidden" name="aksi" value="batal">
                <button type="submit" class="gk-admin-btn gk-admin-btn-outline gk-admin-btn-sm">Batalkan</button>
              </form>
              <?php endif; ?>
            </div>
          </article>
          <?php endforeach; ?>
        </div>
