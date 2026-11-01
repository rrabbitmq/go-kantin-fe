<div class="gk-auth-card">
  <a href="/index.php" class="gk-auth-brand">
    <img src="/img/ui/logo.svg" alt="Go Kantin">
    <strong>Go Kantin</strong>
  </a>

  <h1>Area belum tersedia</h1>
  <p class="gk-auth-sub">
    Halo <strong><?= gk_h($username ?? '') ?></strong>.
    Anda masuk sebagai <strong><?= gk_h($primaryRole ?? '') ?></strong>.
    Interface untuk role ini akan menyusul; autentikasi sudah aktif.
  </p>

  <div class="gk-auth-actions">
    <a href="/auth/logout.php" class="sb-btn">
      <span>Logout</span>
    </a>
    <?php if (($primaryRole ?? '') === 'super_admin'): ?>
      <a href="/auth/login.php" class="sb-btn sb-btn-2 sb-btn-gray">
        <span>Ke login pembeli</span>
      </a>
    <?php endif; ?>
  </div>
</div>
