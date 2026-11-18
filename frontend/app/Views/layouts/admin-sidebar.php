<?php
$activeNav = $activeNav ?? '';
$currentUser = $currentUser ?? ['name' => 'Tamu'];
$standMeta = $standMeta ?? [];
$queueBadge = (int) ($queueBadge ?? 0);
$avatar = gk_avatar_letter($currentUser['name'] ?? 'T');
$statusBuka = !empty($standMeta['status_buka']);
$isSuper = \App\Core\Auth::hasRole('super_admin') && !\App\Core\Auth::hasRole('penjual');
$brandHref = $isSuper ? '/admin/pengguna.php' : '/admin/dashboard.php';
?>
    <aside class="gk-sidebar" aria-label="Menu admin stand">
      <a href="<?= gk_h($brandHref) ?>" class="gk-sidebar-brand">
        <img src="/img/ui/icons/menu.svg" alt="Go Kantin">
        <span>Go Kantin</span>
      </a>
<?php if ($isSuper): ?>
      <ul class="gk-sidebar-nav">
        <li>
          <a href="/admin/pengguna.php" class="<?= $activeNav === 'pengguna' ? 'gk-active' : '' ?>">
            <i class="fas fa-chart-pie"></i><span>Manajemen Pengguna</span>
          </a>
        </li>
        <li>
          <a href="/admin/hak-akses.php" class="<?= $activeNav === 'hak-akses' ? 'gk-active' : '' ?>">
            <i class="fas fa-user-shield"></i><span>Hak Akses</span>
          </a>
        </li>
        <li>
          <a href="/admin/profil.php" class="<?= $activeNav === 'profil' ? 'gk-active' : '' ?>">
            <i class="fas fa-user-cog"></i><span>Profil Admin</span>
          </a>
        </li>
        <li>
          <a href="/auth/logout.php"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
        </li>
      </ul>
<?php else: ?>
      <ul class="gk-sidebar-nav">
        <li>
          <a href="/admin/dashboard.php" class="<?= $activeNav === 'dashboard' ? 'gk-active' : '' ?>">
            <i class="fas fa-chart-pie"></i><span>Dashboard Analitik</span>
          </a>
        </li>
        <li>
          <a href="/admin/pos.php" class="<?= $activeNav === 'pos' ? 'gk-active' : '' ?>">
            <i class="fas fa-cash-register"></i><span>Kasir POS &amp; Scan QR</span>
          </a>
        </li>
        <li>
          <a href="/admin/antrian.php" class="<?= $activeNav === 'antrian' ? 'gk-active' : '' ?>">
            <i class="fas fa-clipboard-list"></i><span>Antrean Pesanan</span>
            <?php if ($queueBadge > 0): ?>
              <span class="gk-nav-badge"><?= $queueBadge ?></span>
            <?php endif; ?>
          </a>
        </li>
        <li>
          <a href="/admin/menu.php" class="<?= $activeNav === 'menu' ? 'gk-active' : '' ?>">
            <i class="fas fa-utensils"></i><span>Manajemen Menu &amp; Stok</span>
          </a>
        </li>
        <li>
          <a href="/admin/mutasi-stok.php" class="<?= $activeNav === 'mutasi' ? 'gk-active' : '' ?>">
            <i class="fas fa-exchange-alt"></i><span>Mutasi Stok</span>
          </a>
        </li>
        <li>
          <a href="/admin/laporan.php" class="<?= $activeNav === 'laporan' ? 'gk-active' : '' ?>">
            <i class="fas fa-file-invoice-dollar"></i><span>Laporan Transaksi</span>
          </a>
        </li>
        <li>
          <a href="/admin/pengaturan.php" class="<?= $activeNav === 'pengaturan' ? 'gk-active' : '' ?>">
            <i class="fas fa-cog"></i><span>Pengaturan Stand</span>
          </a>
        </li>
        <li>
          <a href="/admin/profil.php" class="<?= $activeNav === 'profil' ? 'gk-active' : '' ?>">
            <i class="fas fa-user-cog"></i><span>Profil Akun</span>
          </a>
        </li>
        <li>
          <a href="/auth/logout.php"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
        </li>
      </ul>
<?php endif; ?>

      <div class="gk-sidebar-footer">
<?php if (!$isSuper): ?>
        <div class="gk-loc-card gk-admin-status <?= $statusBuka ? 'is-open' : 'is-closed' ?>">
          <i class="fas <?= $statusBuka ? 'fa-store' : 'fa-store-slash' ?>"></i>
          <div>
            <strong>Status: <?= $statusBuka ? 'Buka' : 'Tutup' ?></strong>
            <span><?= gk_h($standMeta['jam'] ?? '08.00 - 15.00 WIB') ?></span>
          </div>
        </div>
<?php endif; ?>
        <a href="/admin/profil.php" class="gk-user-chip">
          <div class="gk-user-avatar"><?= gk_h($avatar) ?></div>
          <div class="gk-user-meta">
            <strong><?= gk_h($currentUser['name'] ?? 'Tamu') ?></strong>
            <span><?= $isSuper ? 'Super Admin' : gk_h($standMeta['role_label'] ?? 'Admin Stand') ?></span>
          </div>
          <i class="fas fa-chevron-right" style="color:#6F6F87;font-size:12px;"></i>
        </a>
      </div>
    </aside>
    <div class="gk-sidebar-backdrop" aria-hidden="true"></div>
