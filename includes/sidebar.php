<?php
$activeNav = $activeNav ?? '';
$currentUser = $currentUser ?? ['name' => 'Farhan', 'student_id' => '20241001'];
$cartCount = (int) ($cartCount ?? 0);
$avatar = gk_avatar_letter($currentUser['name'] ?? 'F');
?>
    <aside class="gk-sidebar" aria-label="Menu utama">
      <a href="/index.html" class="gk-sidebar-brand">
        <img src="/img/ui/icons/menu.svg" alt="Go Kantin">
        <span>Go Kantin</span>
      </a>
      <ul class="gk-sidebar-nav">
        <li>
          <a href="/index.html" class="<?= $activeNav === 'beranda' ? 'gk-active' : '' ?>">
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
          <a href="#"><i class="fas fa-user"></i><span>Profil</span></a>
        </li>
        <li>
          <a href="/user/status-pesanan.php" class="<?= $activeNav === 'status' ? 'gk-active' : '' ?>">
            <i class="fas fa-clock"></i><span>Pantau Pesananmu</span>
          </a>
        </li>
      </ul>
      <div class="gk-sidebar-footer">
        <a href="#" class="gk-user-chip">
          <div class="gk-user-avatar"><?= gk_h($avatar) ?></div>
          <div class="gk-user-meta">
            <strong><?= gk_h($currentUser['name'] ?? 'Farhan') ?></strong>
            <span>ID <?= gk_h($currentUser['student_id'] ?? '20241001') ?></span>
          </div>
          <i class="fas fa-chevron-right" style="color:#6F6F87;font-size:12px;"></i>
        </a>
      </div>
    </aside>
    <div class="gk-sidebar-backdrop" aria-hidden="true"></div>
