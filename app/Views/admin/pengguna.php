<?php
/** @var array $users */
/** @var array $roles */
/** @var array $statuses */
/** @var int|null $currentUserId */
/** @var array|null $old */
$statusChip = [
    'aktif' => 'siap_diambil',
    'pending' => 'menunggu_pembayaran',
    'ditolak' => 'dibatalkan',
    'nonaktif' => 'dibatalkan',
];
?>
        <div class="gk-admin-page-head">
          <div>
            <h1>Manajemen Pengguna</h1>
            <p>Tambah, ubah, dan hapus akun pengguna beserta role-nya.</p>
          </div>
          <button type="button" class="gk-admin-btn" id="btn-tambah-pengguna">
            <i class="fas fa-plus"></i> Tambah Pengguna
          </button>
        </div>

        <div class="gk-admin-toolbar">
          <select id="filter-pengguna-role" class="gk-admin-select">
            <option value="all">Semua role</option>
            <?php foreach ($roles as $r): ?>
              <option value="<?= gk_h($r['nama_role']) ?>"><?= gk_h($r['nama_role']) ?></option>
            <?php endforeach; ?>
          </select>
          <select id="filter-pengguna-status" class="gk-admin-select">
            <option value="all">Semua status</option>
            <?php foreach ($statuses as $s): ?>
              <option value="<?= gk_h($s) ?>"><?= gk_h($s) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="gk-admin-table-wrap">
          <table id="dt-pengguna" class="table table-striped table-hover w-100">
            <thead>
              <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Role</th>
                <th>Status</th>
                <th>Dibuat</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($users as $u): ?>
              <?php $isSelf = (int) $u['id_user'] === (int) $currentUserId; ?>
              <tr>
                <td><?= (int) $u['id_user'] ?></td>
                <td>
                  <?= gk_h($u['username']) ?>
                  <?php if ($isSelf): ?><small class="text-muted">(Anda)</small><?php endif; ?>
                </td>
                <td><?= gk_h($u['email'] ?? '-') ?></td>
                <td data-search="<?= gk_h(implode(' ', $u['roles'])) ?>">
                  <div class="gk-admin-role-tags">
                    <?php foreach ($u['roles'] as $roleName): ?>
                      <span class="gk-admin-role-tag"><?= gk_h($roleName) ?></span>
                    <?php endforeach; ?>
                  </div>
                </td>
                <td data-search="<?= gk_h($u['status_akun']) ?>">
                  <span class="gk-admin-status-chip is-<?= $statusChip[$u['status_akun']] ?? 'dibatalkan' ?>">
                    <?= gk_h($u['status_akun']) ?>
                  </span>
                </td>
                <td data-order="<?= gk_h($u['created_at']) ?>"><?= gk_h(date('d/m/Y', strtotime($u['created_at']))) ?></td>
                <td>
                  <div class="gk-admin-actions">
                    <button
                      type="button"
                      class="gk-admin-btn gk-admin-btn-ghost gk-admin-btn-sm"
                      data-edit-user
                      data-id="<?= (int) $u['id_user'] ?>"
                      data-username="<?= gk_h($u['username']) ?>"
                      data-email="<?= gk_h($u['email'] ?? '') ?>"
                      data-status="<?= gk_h($u['status_akun']) ?>"
                      data-roles="<?= gk_h(implode(',', $u['role_ids'])) ?>">
                      Edit
                    </button>
                    <?php if (!$isSelf): ?>
                    <form method="post" action="/admin/pengguna.php" data-confirm="Hapus pengguna <?= gk_h($u['username']) ?>?">
                      <?= gk_csrf_field() ?>
                      <input type="hidden" name="action" value="delete">
                      <input type="hidden" name="id_user" value="<?= (int) $u['id_user'] ?>">
                      <button type="submit" class="gk-admin-btn gk-admin-btn-danger gk-admin-btn-sm">Hapus</button>
                    </form>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <div class="modal fade" id="modal-pengguna" tabindex="-1" role="dialog" aria-hidden="true"
          <?php if ($old): ?>
            data-reopen
            data-id="<?= (int) ($old['id_user'] ?? 0) ?>"
            data-username="<?= gk_h($old['username'] ?? '') ?>"
            data-email="<?= gk_h($old['email'] ?? '') ?>"
            data-status="<?= gk_h($old['status_akun'] ?? 'aktif') ?>"
            data-roles="<?= gk_h(implode(',', $old['roles'] ?? [])) ?>"
          <?php endif; ?>>
          <div class="modal-dialog" role="document">
            <form class="modal-content" method="post" action="/admin/pengguna.php" id="form-pengguna">
              <?= gk_csrf_field() ?>
              <input type="hidden" name="action" value="save">
              <input type="hidden" name="id_user" id="form-pengguna-id" value="0">
              <div class="modal-header">
                <h5 class="modal-title" id="modal-pengguna-title">Tambah Pengguna</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Tutup"><span>&times;</span></button>
              </div>
              <div class="modal-body">
                <div class="gk-admin-form-grid">
                  <div class="gk-admin-field">
                    <label for="form-pengguna-username">Username</label>
                    <input type="text" name="username" id="form-pengguna-username" class="gk-admin-input" required minlength="3" maxlength="50" pattern="[a-zA-Z0-9._\-]+">
                  </div>
                  <div class="gk-admin-field">
                    <label for="form-pengguna-email">Email <small class="text-muted">(opsional)</small></label>
                    <input type="email" name="email" id="form-pengguna-email" class="gk-admin-input" maxlength="100">
                  </div>
                  <div class="gk-admin-field">
                    <label for="form-pengguna-password">Password</label>
                    <input type="password" name="password" id="form-pengguna-password" class="gk-admin-input" minlength="8" autocomplete="new-password">
                    <small class="text-muted" id="form-pengguna-password-hint">Minimal 8 karakter.</small>
                  </div>
                  <div class="gk-admin-field">
                    <label for="form-pengguna-status">Status akun</label>
                    <select name="status_akun" id="form-pengguna-status" class="gk-admin-select">
                      <?php foreach ($statuses as $s): ?>
                        <option value="<?= gk_h($s) ?>"><?= gk_h($s) ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <div class="gk-admin-field" style="grid-column:1 / -1;">
                    <label>Role</label>
                    <div class="gk-admin-checklist">
                      <?php foreach ($roles as $r): ?>
                        <label>
                          <input type="checkbox" name="roles[]" value="<?= (int) $r['id_role'] ?>" data-role-checkbox>
                          <span>
                            <?= gk_h($r['nama_role']) ?>
                            <small><?= gk_h($r['deskripsi'] ?? '') ?></small>
                          </span>
                        </label>
                      <?php endforeach; ?>
                    </div>
                  </div>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="gk-admin-btn gk-admin-btn-ghost" data-dismiss="modal">Batal</button>
                <button type="submit" class="gk-admin-btn">Simpan</button>
              </div>
            </form>
          </div>
        </div>
