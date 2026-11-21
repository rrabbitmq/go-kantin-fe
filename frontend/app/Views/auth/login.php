<?php
$intent = $intent ?? 'pembeli';
$isPenjual = $intent === 'penjual';
$old = $old ?? [];
?>
<div class="gk-auth-card" style="position: relative;">
  <!-- Tombol Lonceng Notifikasi di Pojok Kanan Atas -->
  <button type="button" id="btn-notif-login" title="Notifikasi" style="
    position: absolute;
    top: 32px;
    right: 28px;
    width: 42px;
    height: 42px;
    border-radius: 50%;
    border: 1px solid #e4dfd3;
    background: #fff;
    color: #1f1f2e;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(40, 30, 10, 0.05);
    transition: background-color 0.2s ease;
  ">
    <i class="fas fa-bell" style="font-size: 16px;"></i>
  </button>

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
      Belum punya akun penjual? <a href="/auth/penjual/register-penjual.php">Daftar sebagai penjual</a>
    </div>
    <div class="gk-auth-links">
      <a href="/auth/login.php">Kembali ke login pembeli</a>
    </div>
  <?php endif; ?>
</div>

<!-- Pop-up Notifikasi "Silahkan login dulu!" -->
<div id="gk-notif-modal" style="
  display: none;
  position: fixed;
  inset: 0;
  background: rgba(31, 31, 46, 0.45);
  z-index: 9999;
  align-items: center;
  justify-content: center;
  padding: 16px;
">
  <div style="
    background: #fff;
    border-radius: 18px;
    padding: 28px 24px;
    width: 100%;
    max-width: 320px;
    text-align: center;
    box-shadow: 0 18px 50px rgba(40, 30, 10, 0.16);
  ">
    <div style="
      width: 50px;
      height: 50px;
      margin: 0 auto 14px;
      border-radius: 50%;
      background: rgba(245, 195, 50, 0.22);
      color: #1f1f2e;
      display: flex;
      align-items: center;
      justify-content: center;
    ">
      <i class="fas fa-bell" style="font-size: 20px;"></i>
    </div>
    <h3 style="font-size: 1.2rem; margin-bottom: 6px; color: #1f1f2e;">Notifikasi</h3>
    <p style="color: #6f6f87; font-size: 0.95rem; margin-bottom: 22px;">Silahkan login dulu!</p>
    <button type="button" id="btn-close-notif" class="sb-btn" style="width: 100%; justify-content: center;">
      <span>OK</span>
    </button>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    const btnNotif = document.getElementById('btn-notif-login');
    const modalNotif = document.getElementById('gk-notif-modal');
    const btnClose = document.getElementById('btn-close-notif');

    if (btnNotif && modalNotif && btnClose) {
      btnNotif.addEventListener('click', function () {
        modalNotif.style.display = 'flex';
      });

      btnClose.addEventListener('click', function () {
        modalNotif.style.display = 'none';
      });

      modalNotif.addEventListener('click', function (e) {
        if (e.target === modalNotif) {
          modalNotif.style.display = 'none';
        }
      });
    }
  });
</script>