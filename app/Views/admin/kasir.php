<?php
/** @var array $cashiers */
?>
        <div class="gk-admin-page-head">
          <div>
            <h1>Akun Kasir Stand</h1>
            <p>Daftar akun yang dapat mengakses POS dan antrian stand ini.</p>
          </div>
          <button type="button" class="gk-admin-btn" data-toggle="modal" data-target="#modal-kasir">
            <i class="fas fa-plus"></i> Tambah Kasir
          </button>
        </div>

        <div class="gk-admin-table-wrap">
          <table id="dt-kasir" class="table table-striped table-hover w-100">
            <thead>
              <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Nama</th>
                <th>Peran</th>
                <th>Status</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($cashiers as $c): ?>
              <tr>
                <td><?= (int) $c['id'] ?></td>
                <td><?= gk_h($c['username']) ?></td>
                <td><?= gk_h($c['nama']) ?></td>
                <td><?= gk_h($c['peran']) ?></td>
                <td>
                  <span class="gk-admin-status-chip is-<?= $c['status'] === 'aktif' ? 'siap_diambil' : 'dibatalkan' ?>">
                    <?= gk_h($c['status']) ?>
                  </span>
                </td>
                <td>
                  <div class="gk-admin-actions">
                    <button type="button" class="gk-admin-btn gk-admin-btn-ghost gk-admin-btn-sm" data-preview-save="Preview: reset password <?= gk_h($c['username']) ?>">
                      Reset
                    </button>
                    <button type="button" class="gk-admin-btn gk-admin-btn-outline gk-admin-btn-sm" data-preview-save="Preview: ubah status <?= gk_h($c['username']) ?>">
                      <?= $c['status'] === 'aktif' ? 'Nonaktifkan' : 'Aktifkan' ?>
                    </button>
                  </div>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <div class="modal fade" id="modal-kasir" tabindex="-1" role="dialog" aria-hidden="true">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title">Tambah Kasir</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Tutup"><span>&times;</span></button>
              </div>
              <div class="modal-body">
                <div class="gk-admin-form-grid">
                  <div class="gk-admin-field">
                    <label>Username</label>
                    <input type="text" class="gk-admin-input" placeholder="username">
                  </div>
                  <div class="gk-admin-field">
                    <label>Nama tampilan</label>
                    <input type="text" class="gk-admin-input" placeholder="Nama kasir">
                  </div>
                  <div class="gk-admin-field" style="grid-column:1 / -1;">
                    <label>Password sementara</label>
                    <input type="password" class="gk-admin-input" placeholder="••••••••">
                  </div>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="gk-admin-btn gk-admin-btn-ghost" data-dismiss="modal">Batal</button>
                <button type="button" class="gk-admin-btn" data-preview-save="Preview: kasir ditambahkan">Simpan</button>
              </div>
            </div>
          </div>
        </div>
