<?php
$jamOperasional = $jamOperasional ?? ($standMeta['jam'] ?? '08.00 - 15.00 WIB');
?>
      <header class="gk-topbar">
        <div class="gk-topbar-left">
          <button type="button" class="gk-menu-toggle" aria-label="Ciutkan sidebar">
            <i class="fas fa-angle-double-left"></i>
          </button>
        </div>
        <div class="gk-topbar-right">
          <span class="gk-topbar-hours">
            <i class="far fa-clock"></i>
            Jam Operasional: <strong><?= gk_h($jamOperasional) ?></strong>
          </span>
          <div class="gk-topbar-notif">
            <button
              type="button"
              class="gk-topbar-icon"
              aria-label="Notifikasi">
              <i class="far fa-bell"></i>
            </button>
          </div>
        </div>
      </header>
