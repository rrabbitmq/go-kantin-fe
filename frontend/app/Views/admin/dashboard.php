<?php
/** @var array $stats */
/** @var array $lowStock */
/** @var array $categories */
/** @var array $weeklyRevenue */
/** @var array $bestsellers */
$maxRevenue = max(array_column($weeklyRevenue, 'value') ?: [1]);
?>
        <div class="gk-admin-page-head">
          <div>
            <h1>Dashboard Admin — Stand Kantin JTI</h1>
            <p>Ringkasan pendapatan, transaksi, katalog, dan peringatan stok hari ini.</p>
          </div>
          <a href="/admin/pos.php" class="gk-admin-btn"><i class="fas fa-plus"></i> Transaksi Kasir POS Baru</a>
        </div>

        <div class="gk-admin-stats">
          <div class="gk-admin-card">
            <span class="gk-admin-stat-label">Total Pendapatan (Hari Ini)</span>
            <span class="gk-admin-stat-value"><?= gk_h(gk_format_rupiah($stats['pendapatan'])) ?></span>
            <span class="gk-admin-stat-meta">Online + walk-in</span>
          </div>
          <div class="gk-admin-card">
            <span class="gk-admin-stat-label">Total Transaksi Selesai</span>
            <span class="gk-admin-stat-value"><?= (int) $stats['transaksi'] ?> Pesanan</span>
            <span class="gk-admin-stat-meta">QRIS <?= (int) $stats['qris'] ?> · Tunai <?= (int) $stats['tunai'] ?></span>
          </div>
          <div class="gk-admin-card">
            <span class="gk-admin-stat-label">Total Katalog Menu</span>
            <span class="gk-admin-stat-value"><?= (int) $stats['menu_aktif'] ?> Menu Aktif</span>
            <span class="gk-admin-stat-meta">Makanan <?= (int) $stats['makanan'] ?> · Minuman <?= (int) $stats['minuman'] ?> · Snack <?= (int) $stats['snack'] ?></span>
          </div>
          <div class="gk-admin-card is-danger">
            <span class="gk-admin-stat-label">Peringatan Stok Menipis</span>
            <span class="gk-admin-stat-value"><?= (int) $stats['stok_menipis'] ?> Menu</span>
            <ul class="gk-admin-lowlist">
              <?php foreach ($lowStock as $item): ?>
              <li>
                <span><?= gk_h($item['nama']) ?></span>
                <strong>Stok <?= (int) $item['stok'] ?></strong>
              </li>
              <?php endforeach; ?>
            </ul>
            <a href="/admin/menu.php" class="gk-admin-btn gk-admin-btn-outline gk-admin-btn-sm" style="margin-top:10px;">Kelola stok</a>
          </div>
        </div>

        <div class="gk-admin-grid-3">
          <?php foreach ($categories as $cat): ?>
          <div class="gk-admin-card">
            <h3><?= gk_h($cat['nama']) ?></h3>
            <div class="gk-admin-cat-row"><span>Terjual hari ini</span><strong><?= (int) $cat['terjual'] ?></strong></div>
            <div class="gk-admin-cat-row"><span>Sisa stok</span><strong><?= (int) $cat['sisa'] ?></strong></div>
            <div class="gk-admin-cat-row"><span>Sub-omzet</span><strong><?= gk_h(gk_format_rupiah($cat['omzet'])) ?></strong></div>
          </div>
          <?php endforeach; ?>
        </div>

        <div class="gk-admin-grid-2">
          <div class="gk-admin-card">
            <h2>Pendapatan Mingguan</h2>
            <div class="gk-admin-bars">
              <?php foreach ($weeklyRevenue as $day): ?>
              <?php $pct = max(8, (int) round(($day['value'] / $maxRevenue) * 100)); ?>
              <div class="gk-admin-bar">
                <div class="gk-admin-bar-fill" style="height: <?= $pct ?>%;" title="<?= gk_h(gk_format_rupiah($day['value'])) ?>"></div>
                <span><?= gk_h($day['label']) ?></span>
              </div>
              <?php endforeach; ?>
            </div>
          </div>
          <div class="gk-admin-card">
            <h2>Top 5 Menu</h2>
            <ol class="gk-admin-rank">
              <?php foreach ($bestsellers as $item): ?>
              <li>
                <span class="gk-admin-rank-num"><?= (int) $item['rank'] ?></span>
                <div>
                  <strong><?= gk_h($item['nama']) ?></strong>
                  <span><?= gk_h(gk_format_rupiah($item['harga'])) ?> · Stok <?= (int) $item['stok'] ?></span>
                </div>
                <em><?= (int) $item['terjual'] ?> terjual</em>
              </li>
              <?php endforeach; ?>
            </ol>
          </div>
        </div>
