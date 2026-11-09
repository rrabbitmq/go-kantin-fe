<?php
/**
 * @var array      $account
 * @var string[]   $roles
 * @var bool       $isSuperAdmin
 * @var array|null $systemStats
 * @var array      $old
 */
$old = $old ?: [];
$username = $old['username'] ?? ($account['username'] ?? '');
$email = $old['email'] ?? ($account['email'] ?? '');
$statusAkun = $account['status_akun'] ?? 'aktif';
$joinedAt = !empty($account['created_at']) ? gk_format_date($account['created_at']) : '-';
?>
        <div class="gk-admin-page-head">
          <div>
            <h1>Profil &amp; Pengaturan Akun</h1>
            <p>Kelola data akun dan keamanan login <?= $isSuperAdmin ? 'super admin' : 'admin stand' ?> kamu.</p>
          </div>
        </div>

        <section class="gk-admin-card gk-admin-account-hero">
          <div class="gk-admin-avatar gk-admin-account-avatar"><?= gk_h(gk_avatar_letter($account['username'] ?? '')) ?></div>
          <div class="gk-admin-account-identity">
            <strong><?= gk_h($account['username'] ?? '') ?></strong>
            <span><?= gk_h(($account['email'] ?? '') !== '' ? $account['email'] : 'Email belum diisi') ?></span>
            <div class="gk-admin-role-tags">
              <?php foreach ($roles as $roleName): ?>
                <span class="gk-admin-role-tag"><?= gk_h($roleName) ?></span>
              <?php endforeach; ?>
            </div>
          </div>
          <span class="gk-admin-status-chip is-<?= $statusAkun === 'aktif' ? 'siap_diambil' : 'menunggu_pembayaran' ?>">
            Akun <?= gk_h($statusAkun) ?>
          </span>
        </section>

        <div class="gk-admin-stats">
          <div class="gk-admin-card">
            <span class="gk-admin-stat-label">Bergabung sejak</span>
            <span class="gk-admin-stat-value gk-admin-account-stat"><?= gk_h($joinedAt) ?></span>
            <span class="gk-admin-stat-meta">Tanggal akun dibuat</span>
          </div>
          <div class="gk-admin-card">
            <span class="gk-admin-stat-label">Role aktif</span>
            <span class="gk-admin-stat-value gk-admin-account-stat"><?= count($roles) ?> Role</span>
            <span class="gk-admin-stat-meta"><?= gk_h($roles ? implode(', ', $roles) : '-') ?></span>
          </div>
          <?php if ($isSuperAdmin && $systemStats): ?>
            <div class="gk-admin-card">
              <span class="gk-admin-stat-label">Total pengguna</span>
              <span class="gk-admin-stat-value gk-admin-account-stat"><?= (int) $systemStats['users'] ?> Akun</span>
              <a class="gk-admin-stat-meta gk-admin-account-link" href="/admin/pengguna.php">Kelola pengguna <i class="fas fa-chevron-right"></i></a>
            </div>
            <div class="gk-admin-card">
              <span class="gk-admin-stat-label">Total role</span>
              <span class="gk-admin-stat-value gk-admin-account-stat"><?= (int) $systemStats['roles'] ?> Role</span>
              <a class="gk-admin-stat-meta gk-admin-account-link" href="/admin/hak-akses.php">Kelola hak akses <i class="fas fa-chevron-right"></i></a>
            </div>
          <?php else: ?>
            <div class="gk-admin-card">
              <span class="gk-admin-stat-label">Status akun</span>
              <span class="gk-admin-stat-value gk-admin-account-stat"><?= gk_h(ucfirst($statusAkun)) ?></span>
              <span class="gk-admin-stat-meta">Akun dapat mengakses panel admin</span>
            </div>
            <div class="gk-admin-card">
              <span class="gk-admin-stat-label">Data usaha</span>
              <span class="gk-admin-stat-value gk-admin-account-stat">Stand</span>
              <a class="gk-admin-stat-meta gk-admin-account-link" href="/admin/pengaturan.php">Buka pengaturan stand <i class="fas fa-chevron-right"></i></a>
            </div>
          <?php endif; ?>
        </div>

        <div class="gk-admin-account-forms">
          <section class="gk-admin-card">
            <h2>Data Akun</h2>
            <form method="post" action="/admin/profil.php">
              <?= gk_csrf_field() ?>
              <input type="hidden" name="action" value="account">
              <div class="gk-admin-account-fields">
                <div class="gk-admin-field">
                  <label for="profil-username">Username</label>
                  <input type="text" id="profil-username" class="gk-admin-input" name="username" value="<?= gk_h($username) ?>"
                         required minlength="3" maxlength="50" pattern="[a-zA-Z0-9._\-]+" autocomplete="username">
                  <small class="gk-admin-account-hint">Huruf, angka, titik, underscore, atau strip.</small>
                </div>
                <div class="gk-admin-field">
                  <label for="profil-email">Email <span class="gk-admin-account-hint">(opsional)</span></label>
                  <input type="email" id="profil-email" class="gk-admin-input" name="email" value="<?= gk_h($email) ?>" maxlength="100" autocomplete="email">
                </div>
              </div>
              <button type="submit" class="gk-admin-btn">Simpan Data Akun</button>
            </form>
          </section>

          <section class="gk-admin-card">
            <h2>Ganti Password</h2>
            <form method="post" action="/admin/profil.php">
              <?= gk_csrf_field() ?>
              <input type="hidden" name="action" value="password">
              <div class="gk-admin-account-fields">
                <div class="gk-admin-field">
                  <label for="profil-current-password">Password saat ini</label>
                  <input type="password" id="profil-current-password" class="gk-admin-input" name="current_password" required autocomplete="current-password">
                </div>
                <div class="gk-admin-field">
                  <label for="profil-new-password">Password baru</label>
                  <input type="password" id="profil-new-password" class="gk-admin-input" name="new_password" required minlength="8" autocomplete="new-password">
                  <small class="gk-admin-account-hint">Minimal 8 karakter.</small>
                </div>
                <div class="gk-admin-field">
                  <label for="profil-new-password-confirm">Konfirmasi password baru</label>
                  <input type="password" id="profil-new-password-confirm" class="gk-admin-input" name="new_password_confirm" required minlength="8" autocomplete="new-password">
                </div>
              </div>
              <button type="submit" class="gk-admin-btn">Ganti Password</button>
            </form>
          </section>
        </div>
