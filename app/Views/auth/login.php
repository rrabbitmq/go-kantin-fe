<?php
$intent = $intent ?? 'pembeli';
$isPenjual = $intent === 'penjual';
$old = $old ?? [];
?>
<div class="gk-auth-card">
  <a href="/index.php" class="gk-auth-brand">
    <img src="/img/ui/logo.svg" alt="Go Kantin">
    <strong>Go Kantin</strong>
  </a>

  <h1><?= $isPenjual ? 'Login Penjual' : 'Login Pembeli' ?></h1>
  <p class="gk-auth-sub">
    <?= $isPenjual
      ? 'Masuk dengan akun penjual atau super admin.'
      : 'Masuk untuk memesan makanan di kantin.' ?>
  </p>

  <?php if (!empty($error)): ?>
    <div class="gk-auth-alert gk-auth-alert-error"><?= gk_h($error) ?></div>
  <?php endif; ?>
  <?php if (!empty($success)): ?>
    <div class="gk-auth-alert gk-auth-alert-success"><?= gk_h($success) ?></div>
  <?php endif; ?>

  <form method="post" action="<?= $isPenjual ? '/auth/penjual/login.php' : '/auth/login.php' ?>">
    <div class="gk-auth-field">
      <label for="login">Username atau Email</label>
      <input type="text" id="login" name="login" value="<?= gk_h($old['login'] ?? '') ?>" required autofocus>
    </div>
    <div class="gk-auth-field">
      <label for="password">Password</label>
      <input type="password" id="password" name="password" required>
    </div>
    <div class="gk-auth-actions">
      <button type="submit" class="sb-btn">
        <span>Masuk</span>
      </button>
    </div>
  </form>

  <?php if (!$isPenjual): ?>
    <div class="gk-auth-links">
      Belum punya akun? <a href="/auth/register.php">Daftar pembeli</a>
    </div>
    <div class="gk-auth-switch">
      <a class="sb-btn sb-btn-2 sb-btn-gray" href="/auth/penjual/login.php">
        <span>Login sebagai penjual</span>
      </a>
    </div>
  <?php else: ?>
    <div class="gk-auth-links">
      <a href="/auth/login.php">Kembali ke login pembeli</a>
    </div>
  <?php endif; ?>
</div>
