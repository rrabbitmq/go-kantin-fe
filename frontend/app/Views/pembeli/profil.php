<?php
/**
 * @var array       $account
 * @var array       $summary
 * @var array       $old
 * @var string|null $flashSuccess
 * @var string|null $flashError
 */
$old = $old ?: [];
$username = $old['username'] ?? ($account['username'] ?? '');
$email = $old['email'] ?? ($account['email'] ?? '');
$statusAkun = $account['status_akun'] ?? 'aktif';
$joinedAt = !empty($account['created_at']) ? gk_format_date($account['created_at']) : '-';
$lastOrder = !empty($summary['pesanan_terakhir']) ? gk_format_order_datetime($summary['pesanan_terakhir']) : 'Belum ada pesanan';
?>
        <section class="gk-page-head">
          <h1>Profil &amp; Pengaturan</h1>
          <p>Kelola data akun dan keamanan login kamu.</p>
        </section>

        <?php if (!empty($flashSuccess)): ?>
          <div class="gk-profile-alert gk-profile-alert-success" role="status"><?= gk_h($flashSuccess) ?></div>
        <?php endif; ?>
        <?php if (!empty($flashError)): ?>
          <div class="gk-profile-alert gk-profile-alert-error" role="alert"><?= gk_h($flashError) ?></div>
        <?php endif; ?>

        <section class="gk-panel gk-profile-hero">
          <div class="gk-user-avatar gk-profile-avatar"><?= gk_h(gk_avatar_letter($account['username'] ?? '')) ?></div>
          <div class="gk-profile-identity">
            <strong><?= gk_h($account['username'] ?? '') ?></strong>
            <span><?= gk_h(($account['email'] ?? '') !== '' ? $account['email'] : 'Email belum diisi') ?></span>
          </div>
          <span class="gk-badge <?= $statusAkun === 'aktif' ? 'gk-badge-done' : 'gk-badge-warn' ?>">
            Akun <?= gk_h($statusAkun) ?>
          </span>
        </section>

        <section class="gk-profile-stats">
          <div class="gk-panel gk-profile-stat">
            <span class="gk-label">Bergabung sejak</span>
            <strong><?= gk_h($joinedAt) ?></strong>
          </div>
          <div class="gk-panel gk-profile-stat">
            <span class="gk-label">Jumlah pesanan</span>
            <strong><?= (int) $summary['jumlah_pesanan'] ?> pesanan</strong>
            <small>Terakhir: <?= gk_h($lastOrder) ?></small>
          </div>
          <div class="gk-panel gk-profile-stat">
            <span class="gk-label">Total belanja</span>
            <strong><?= gk_h(gk_format_rupiah($summary['total_belanja'])) ?></strong>
            <a href="/user/riwayat-pesanan.php">Lihat riwayat pesanan <i class="fas fa-chevron-right"></i></a>
          </div>
        </section>

        <div class="gk-profile-forms">
          <section class="gk-panel">
            <div class="gk-panel-head">
              <h2>Data Akun</h2>
            </div>
            <form method="post" action="/user/profil.php" class="gk-profile-form">
              <?= gk_csrf_field() ?>
              <input type="hidden" name="action" value="account">
              <div class="gk-profile-field">
                <label for="profil-username">Username</label>
                <input type="text" id="profil-username" name="username" value="<?= gk_h($username) ?>"
                       required minlength="3" maxlength="50" pattern="[a-zA-Z0-9._\-]+" autocomplete="username">
                <small>Huruf, angka, titik, underscore, atau strip.</small>
              </div>
              <div class="gk-profile-field">
                <label for="profil-email">Email <span>(opsional)</span></label>
                <input type="email" id="profil-email" name="email" value="<?= gk_h($email) ?>" maxlength="100" autocomplete="email">
              </div>
              <button type="submit" class="gk-btn-primary">Simpan Data Akun</button>
            </form>
          </section>

          <section class="gk-panel">
            <div class="gk-panel-head">
              <h2>Ganti Password</h2>
            </div>
            <form method="post" action="/user/profil.php" class="gk-profile-form">
              <?= gk_csrf_field() ?>
              <input type="hidden" name="action" value="password">
              <div class="gk-profile-field">
                <label for="profil-current-password">Password saat ini</label>
                <input type="password" id="profil-current-password" name="current_password" required autocomplete="current-password">
              </div>
              <div class="gk-profile-field">
                <label for="profil-new-password">Password baru</label>
                <input type="password" id="profil-new-password" name="new_password" required minlength="8" autocomplete="new-password">
                <small>Minimal 8 karakter.</small>
              </div>
              <div class="gk-profile-field">
                <label for="profil-new-password-confirm">Konfirmasi password baru</label>
                <input type="password" id="profil-new-password-confirm" name="new_password_confirm" required minlength="8" autocomplete="new-password">
              </div>
              <button type="submit" class="gk-btn-primary">Ganti Password</button>
            </form>
          </section>
        </div>
