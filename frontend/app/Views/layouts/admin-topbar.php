<?php
$currentUser = $currentUser ?? ['name' => 'Tamu'];
$standMeta = $standMeta ?? [];
$avatar = gk_avatar_letter($currentUser['name'] ?? 'T');
?>
      <header class="gk-admin-topbar">
        <div class="gk-admin-topbar-left">
          <button type="button" class="gk-admin-menu-btn" data-admin-toggle aria-label="Buka menu">
            <i class="fas fa-bars"></i>
          </button>
          <div>
            <strong><?= gk_h($standMeta['lokasi'] ?? 'Kantin JTI • Lantai 8') ?></strong>
            <span>Real-Time POS &amp; Panel Admin Stand</span>
          </div>
        </div>
        <div class="gk-admin-topbar-right">
          <span class="gk-admin-hours">
            <i class="far fa-clock"></i>
            Jam Operasional: <?= gk_h($standMeta['jam'] ?? '08.00 - 15.00 WIB') ?>
          </span>
          <button type="button" class="gk-admin-icon-btn" aria-label="Notifikasi">
            <i class="far fa-bell"></i>
            <em></em>
          </button>
          <div class="gk-admin-profile">
            <div class="gk-admin-avatar"><?= gk_h($avatar) ?></div>
            <span><?= gk_h($currentUser['name'] ?? 'Tamu') ?></span>
          </div>
        </div>
      </header>
