<?php $old = $old ?? []; ?>
<div class="gk-auth-card">
  <a href="/index.php" class="gk-auth-brand">
    <img src="/img/ui/logo.svg" alt="Go Kantin">
    <strong>Go Kantin</strong>
  </a>

  <h1>Daftar Pembeli</h1>
  <p class="gk-auth-sub">Buat akun untuk mulai memesan di kantin.</p>

  <?php if (!empty($error)): ?>
    <div class="gk-auth-alert gk-auth-alert-error"><?= gk_h($error) ?></div>
  <?php endif; ?>

  <form method="post" action="/auth/register.php">
    <div class="gk-auth-field">
      <label for="username">Username</label>
      <input type="text" id="username" name="username" value="<?= gk_h($old['username'] ?? '') ?>" required autofocus>
    </div>
    <div class="gk-auth-field">
      <label for="no_hp">Nomor Hp</label>
      <input type="tel" id="no_hp" name="no_hp" placeholder="08xxxxxxxxxx" value="<?= gk_h($old['no_hp'] ?? '') ?>" required>
    </div>
    <div class="gk-auth-field">
      <label for="email">Email (opsional)</label>
      <input type="email" id="email" name="email" value="<?= gk_h($old['email'] ?? '') ?>">
    </div>
    <div class="gk-auth-field">
      <label for="password">Password</label>
      <input type="password" id="password" name="password" minlength="8" required>
    </div>
    <div class="gk-auth-field">
      <label for="password_confirm">Konfirmasi Password</label>
      <input type="password" id="password_confirm" name="password_confirm" minlength="8" required>
    </div>
    <div class="gk-auth-actions">
      <button type="submit" class="sb-btn">
        <span>Daftar</span>
      </button>
    </div>
  </form>

  <div class="gk-auth-links">
    Sudah punya akun? <a href="/auth/login.php">Login</a>
  </div>
</div>
