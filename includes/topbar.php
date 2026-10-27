<?php
$currentUser = $currentUser ?? ['name' => 'Farhan'];
$avatar = gk_avatar_letter($currentUser['name'] ?? 'F');
?>
      <header class="gk-topbar">
        <div class="gk-topbar-left">
          <button type="button" class="gk-menu-toggle" aria-label="Buka menu">
            <i class="fas fa-bars"></i>
          </button>
        </div>
        <div class="gk-topbar-right">
          <a href="/user/riwayat-pesanan.php" class="gk-topbar-link">Riwayat Pesanan</a>
          <a href="#" class="gk-topbar-icon" aria-label="Notifikasi">
            <i class="far fa-bell"></i>
            <span class="gk-dot"></span>
          </a>
          <a href="#" class="gk-topbar-user">
            <div class="gk-user-avatar"><?= gk_h($avatar) ?></div>
            <span><?= gk_h($currentUser['name'] ?? 'Farhan') ?></span>
          </a>
        </div>
      </header>
