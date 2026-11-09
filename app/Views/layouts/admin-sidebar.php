<?php
$activeNav = $activeNav ?? '';
$currentUser = $currentUser ?? ['name' => 'Tamu'];
$standMeta = $standMeta ?? [];
$queueBadge = (int) ($queueBadge ?? 0);
$avatar = gk_avatar_letter($currentUser['name'] ?? 'T');
$statusBuka = !empty($standMeta['status_buka']);
?>
    <aside class="gk-admin-sidebar" aria-label="Menu admin stand">
      <div class="gk-admin-brand">
        <!-- <img src="/img/ui/logo.svg" alt="Go Kantin"> -->
        <div>
          <strong>Go Kantin POS</strong>
          <span><?= \App\Core\Auth::hasRole('super_admin') ? 'Panel Super Admin' : 'Panel Admin / Kasir Terpadu' ?></span>
        </div>
      </div>

      <p class="gk-admin-nav-label">Menu Pengelola</p>
      <ul class="gk-admin-nav">
        <li>
          <a href="/admin/dashboard.php" class="<?= $activeNav === 'dashboard' ? 'is-active' : '' ?>">
            <i class="fas fa-chart-pie"></i><span>Dashboard Analitik</span>
          </a>
        </li>
        <li>
          <a href="/admin/pos.php" class="<?= $activeNav === 'pos' ? 'is-active' : '' ?>">
            <i class="fas fa-cash-register"></i><span>Kasir POS &amp; Scan QR</span>
          </a>
        </li>
        <li>
          <a href="/admin/antrian.php" class="<?= $activeNav === 'antrian' ? 'is-active' : '' ?>">
            <i class="fas fa-clipboard-list"></i><span>Antrean Pesanan</span>
            <?php if ($queueBadge > 0): ?>
              <em class="gk-admin-badge"><?= $queueBadge ?></em>
            <?php endif; ?>
          </a>
        </li>
        <li>
          <a href="/admin/menu.php" class="<?= $activeNav === 'menu' ? 'is-active' : '' ?>">
            <i class="fas fa-utensils"></i><span>Manajemen Menu &amp; Stok</span>
          </a>
        </li>
        <li>
          <a href="/admin/mutasi-stok.php" class="<?= $activeNav === 'mutasi' ? 'is-active' : '' ?>">
            <i class="fas fa-exchange-alt"></i><span>Mutasi Stok</span>
          </a>
        </li>
        <li>
          <a href="/admin/laporan.php" class="<?= $activeNav === 'laporan' ? 'is-active' : '' ?>">
            <i class="fas fa-file-invoice-dollar"></i><span>Laporan Transaksi</span>
          </a>
        </li>
        <li>
          <a href="/admin/pengaturan.php" class="<?= $activeNav === 'pengaturan' ? 'is-active' : '' ?>">
            <i class="fas fa-cog"></i><span>Pengaturan Stand</span>
          </a>
        </li>
        <li>
          <a href="/admin/kasir.php" class="<?= $activeNav === 'kasir' ? 'is-active' : '' ?>">
            <i class="fas fa-user-friends"></i><span>Akun Kasir Stand</span>
          </a>
        </li>
      </ul>

      <?php if (\App\Core\Auth::hasRole('super_admin')): ?>
      <p class="gk-admin-nav-label">Super Admin</p>
      <ul class="gk-admin-nav">
        <li>
          <a href="/admin/pengguna.php" class="<?= $activeNav === 'pengguna' ? 'is-active' : '' ?>">
            <i class="fas fa-users-cog"></i><span>Manajemen Pengguna</span>
          </a>
        </li>
        <li>
          <a href="/admin/hak-akses.php" class="<?= $activeNav === 'hak-akses' ? 'is-active' : '' ?>">
            <i class="fas fa-user-shield"></i><span>Hak Akses</span>
          </a>
        </li>
      </ul>
      <?php endif; ?>

      <p class="gk-admin-nav-label">Akun</p>
      <ul class="gk-admin-nav">
        <li>
          <a href="/admin/profil.php" class="<?= $activeNav === 'profil' ? 'is-active' : '' ?>">
            <i class="fas fa-user-cog"></i><span>Profil Saya</span>
          </a>
        </li>
      </ul>

      <div class="gk-admin-sidebar-footer">
        <div class="gk-admin-status <?= $statusBuka ? 'is-open' : 'is-closed' ?>">
          <strong>Status Kantin: <?= $statusBuka ? 'Buka' : 'Tutup' ?></strong>
          <span>Jam operasional: <?= gk_h($standMeta['jam'] ?? '08.00 - 15.00 WIB') ?></span>
        </div>
        <a href="/admin/profil.php" class="gk-admin-userchip" title="Profil saya">
          <div class="gk-admin-avatar"><?= gk_h($avatar) ?></div>
          <div>
            <strong><?= gk_h($currentUser['name'] ?? 'Tamu') ?></strong>
            <span><?= gk_h($standMeta['role_label'] ?? 'Kasir Stand') ?></span>
          </div>
        </a>
        <a href="/auth/logout.php" class="gk-admin-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
      </div>
    </aside>
    <div class="gk-admin-backdrop" data-admin-toggle hidden></div>
