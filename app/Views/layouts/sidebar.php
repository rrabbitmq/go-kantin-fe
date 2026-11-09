<?php
$activeNav = $activeNav ?? '';
$currentUser = $currentUser ?? ['name' => 'Tamu', 'student_id' => '-'];
$cartCount = (int) ($cartCount ?? 0);
$avatar = gk_avatar_letter($currentUser['name'] ?? 'T');
?>
    <aside class="gk-sidebar" aria-label="Menu utama">
      <a href="/user/beranda.php" class="gk-sidebar-brand">
        <img src="/img/ui/icons/menu.svg" alt="Go Kantin">
        <span>Go Kantin</span>
      </a>
      <ul class="gk-sidebar-nav">
        <li>
          <a href="/user/beranda.php" class="<?= $activeNav === 'beranda' ? 'gk-active' : '' ?>">
            <i class="fas fa-home"></i><span>Beranda</span>
          </a>
        </li>
        <li>
          <a href="/user/menu.php" class="<?= $activeNav === 'menu' ? 'gk-active' : '' ?>">
            <i class="fas fa-utensils"></i><span>Menu Kantin</span>
          </a>
        </li>
        <li>
          <a href="/user/cart.php" class="<?= $activeNav === 'cart' ? 'gk-active' : '' ?>">
            <i class="fas fa-shopping-bag"></i><span>Keranjang</span>
            <?php if ($cartCount > 0): ?>
              <span class="gk-nav-badge"><?= (int) $cartCount ?></span>
            <?php endif; ?>
          </a>
        </li>
        <li>
          <a href="/user/status-pesanan.php" class="<?= $activeNav === 'status' ? 'gk-active' : '' ?>">
            <i class="fas fa-clock"></i><span>Pantau Pesananmu</span>
          </a>
        </li>
        <li>
          <a href="/user/profil.php" class="<?= $activeNav === 'profil' ? 'gk-active' : '' ?>">
            <i class="fas fa-user-cog"></i><span>Profil &amp; Pengaturan</span>
          </a>
        </li>
        <li>
          <a href="/auth/logout.php"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
        </li>
      </ul>
      <div class="gk-sidebar-footer">
        <a href="/user/profil.php" class="gk-user-chip">
          <div class="gk-user-avatar"><?= gk_h($avatar) ?></div>
          <div class="gk-user-meta">
            <strong><?= gk_h($currentUser['name'] ?? 'Tamu') ?></strong>
            <span>ID <?= gk_h($currentUser['student_id'] ?? '-') ?></span>
          </div>
          <i class="fas fa-chevron-right" style="color:#6F6F87;font-size:12px;"></i>
        </a>
      </div>
    </aside>
    <div class="gk-sidebar-backdrop" aria-hidden="true"></div>
