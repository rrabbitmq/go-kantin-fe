<?php
/** @var array $roles */
/** @var array $permissions */
/** @var array<int, int[]> $permissionMap */
/** @var array $builtInRoles */
?>
        <div class="gk-admin-page-head">
          <div>
            <h1>Hak Akses</h1>
            <p>Atur permission untuk setiap role. Perubahan berlaku saat pengguna login berikutnya.</p>
          </div>
          <button type="button" class="gk-admin-btn" data-toggle="modal" data-target="#modal-role">
            <i class="fas fa-plus"></i> Tambah Role
          </button>
        </div>

        <?php foreach ($roles as $r): ?>
        <?php
        $idRole = (int) $r['id_role'];
        $assigned = $permissionMap[$idRole] ?? [];
        $isBuiltIn = in_array($r['nama_role'], $builtInRoles, true);
        $userCount = (int) $r['jumlah_user'];
        ?>
        <section class="gk-admin-card gk-admin-role-card" id="role-<?= $idRole ?>">
          <div class="gk-admin-role-card-head">
            <div>
              <h2>
                <span class="gk-admin-role-tag"><?= gk_h($r['nama_role']) ?></span>
                <?php if ($isBuiltIn): ?><small class="text-muted">bawaan</small><?php endif; ?>
              </h2>
              <p>
                <?= gk_h($r['deskripsi'] ?: 'Tanpa deskripsi') ?>
                &middot; <?= $userCount ?> pengguna
                &middot; <?= count($assigned) ?>/<?= count($permissions) ?> permission
              </p>
            </div>
            <?php if (!$isBuiltIn): ?>
            <form method="post" action="/admin/hak-akses.php" data-confirm="Hapus role <?= gk_h($r['nama_role']) ?>?">
              <?= gk_csrf_field() ?>
              <input type="hidden" name="action" value="delete_role">
              <input type="hidden" name="id_role" value="<?= $idRole ?>">
              <button type="submit" class="gk-admin-btn gk-admin-btn-danger gk-admin-btn-sm" <?= $userCount > 0 ? 'disabled title="Masih dipakai pengguna"' : '' ?>>
                Hapus Role
              </button>
            </form>
            <?php endif; ?>
          </div>

          <form method="post" action="/admin/hak-akses.php">
            <?= gk_csrf_field() ?>
            <input type="hidden" name="action" value="permissions">
            <input type="hidden" name="id_role" value="<?= $idRole ?>">
            <div class="gk-admin-checklist">
              <?php foreach ($permissions as $p): ?>
                <?php $idPerm = (int) $p['id_permission']; ?>
                <label>
                  <input type="checkbox" name="permissions[]" value="<?= $idPerm ?>" <?= in_array($idPerm, $assigned, true) ? 'checked' : '' ?>>
                  <span>
                    <?= gk_h($p['nama_permission']) ?>
                    <small><?= gk_h($p['deskripsi'] ?? '') ?></small>
                  </span>
                </label>
              <?php endforeach; ?>
            </div>
            <div class="gk-admin-role-card-foot">
              <button type="submit" class="gk-admin-btn gk-admin-btn-sm">Simpan Hak Akses</button>
            </div>
          </form>
        </section>
        <?php endforeach; ?>

        <div class="modal fade" id="modal-role" tabindex="-1" role="dialog" aria-hidden="true">
          <div class="modal-dialog" role="document">
            <form class="modal-content" method="post" action="/admin/hak-akses.php">
              <?= gk_csrf_field() ?>
              <input type="hidden" name="action" value="create_role">
              <div class="modal-header">
                <h5 class="modal-title">Tambah Role</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Tutup"><span>&times;</span></button>
              </div>
              <div class="modal-body">
                <div class="gk-admin-field" style="margin-bottom:12px;">
                  <label for="form-role-nama">Nama role</label>
                  <input type="text" name="nama_role" id="form-role-nama" class="gk-admin-input" required minlength="3" maxlength="50" pattern="[a-z0-9_]+" placeholder="contoh: kasir">
                  <small class="text-muted">Huruf kecil, angka, dan underscore.</small>
                </div>
                <div class="gk-admin-field">
                  <label for="form-role-deskripsi">Deskripsi</label>
                  <input type="text" name="deskripsi" id="form-role-deskripsi" class="gk-admin-input" maxlength="255">
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="gk-admin-btn gk-admin-btn-ghost" data-dismiss="modal">Batal</button>
                <button type="submit" class="gk-admin-btn">Simpan</button>
              </div>
            </form>
          </div>
        </div>
