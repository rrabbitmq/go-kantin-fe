<?php
$old = $old ?? [];
$kategori = $kategori ?? [];
?>
<style>
  .gk-auth-field select,
  .gk-auth-field textarea {
    width: 100%;
    border: 1px solid #e4dfd3;
    border-radius: 10px;
    padding: 12px 14px;
    font-size: 1rem;
    background: #fff;
    font-family: inherit;
  }
  .gk-auth-field textarea { min-height: 84px; resize: vertical; }
</style>
<div class="gk-auth-card">
  <a href="/index.php" class="gk-auth-brand">
    <img src="/img/ui/logo.svg" alt="Go Kantin">
    <strong>Go Kantin</strong>
  </a>

  <h1>Daftar Penjual</h1>
  <p class="gk-auth-sub">Daftarkan stand kamu. Akun baru perlu disetujui admin sebelum bisa dipakai login.</p>

  <?php if (!empty($error)): ?>
    <div class="gk-auth-alert gk-auth-alert-error"><?= gk_h($error) ?></div>
  <?php endif; ?>

  <?php if (!$kategori): ?>
    <div class="gk-auth-alert gk-auth-alert-error">Daftar kategori usaha belum dapat dimuat. Pastikan backend berjalan, lalu muat ulang halaman.</div>
  <?php endif; ?>

  <form method="post" action="/auth/penjual/register-penjual.php">
    <div class="gk-auth-field">
      <label for="username">Username</label>
      <input type="text" id="username" name="username" value="<?= gk_h($old['username'] ?? '') ?>" required autofocus>
    </div>
    <div class="gk-auth-field">
      <label for="nama_usaha">Nama Usaha / Stand</label>
      <input type="text" id="nama_usaha" name="nama_usaha" maxlength="100" value="<?= gk_h($old['nama_usaha'] ?? '') ?>" required>
    </div>
    <div class="gk-auth-field">
      <label for="id_kategori_penjual">Kategori Usaha</label>
      <select id="id_kategori_penjual" name="id_kategori_penjual" required>
        <option value="">Pilih kategori</option>
        <?php foreach ($kategori as $k): ?>
        <option value="<?= (int) $k['id'] ?>" <?= (int) ($old['id_kategori_penjual'] ?? 0) === (int) $k['id'] ? 'selected' : '' ?>>
          <?= gk_h($k['name']) ?>
        </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="gk-auth-field">
      <label for="no_hp">Nomor HP</label>
      <input type="tel" id="no_hp" name="no_hp" placeholder="08xxxxxxxxxx" value="<?= gk_h($old['no_hp'] ?? '') ?>" required>
    </div>
    <div class="gk-auth-field">
      <label for="email">Email (opsional)</label>
      <input type="email" id="email" name="email" value="<?= gk_h($old['email'] ?? '') ?>">
    </div>
    <div class="gk-auth-field">
      <label for="alamat">Alamat / Lokasi Stand (opsional)</label>
      <textarea id="alamat" name="alamat"><?= gk_h($old['alamat'] ?? '') ?></textarea>
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
        <span>Daftar sebagai penjual</span>
      </button>
    </div>
  </form>

  <div class="gk-auth-links">
    Sudah punya akun penjual? <a href="/auth/penjual/login.php">Login</a>
  </div>
</div>