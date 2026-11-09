<?php
$currentUser = $currentUser ?? ['name' => 'Tamu'];
$avatar = gk_avatar_letter($currentUser['name'] ?? 'T');
?>
      <header class="gk-topbar">
        <div class="gk-topbar-left">
          <button type="button" class="gk-menu-toggle" aria-label="Buka menu">
            <i class="fas fa-bars"></i>
          </button>
        </div>
        <div class="gk-topbar-right">
          <a href="/user/riwayat-pesanan.php" class="gk-topbar-link">Riwayat Pesanan</a>
          <a href="/user/queue.php" class="gk-topbar-link">Antrian</a>
          <a href="/user/profil.php" class="gk-topbar-user" title="Profil &amp; Pengaturan">
            <div class="gk-user-avatar"><?= gk_h($avatar) ?></div>
            <span><?= gk_h($currentUser['name'] ?? 'Tamu') ?></span>
          </a>
        </div>
      </header>
