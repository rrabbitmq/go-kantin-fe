<?php
/** @var array $products */
?>
        <div class="gk-admin-page-head">
          <div>
            <h1>Manajemen Menu &amp; Stok</h1>
            <p>Kelola katalog produk, harga, stok, dan status tampil di POS / pembeli.</p>
          </div>
          <button type="button" class="gk-admin-btn" id="btn-tambah-produk">
            <i class="fas fa-plus"></i> Tambah Menu
          </button>
        </div>

        <div class="gk-admin-toolbar">
          <select id="filter-produk-kategori" class="gk-admin-select">
            <option value="all">Semua kategori</option>
            <option value="Makanan">Makanan</option>
            <option value="Minuman">Minuman</option>
            <option value="Snack">Snack</option>
          </select>
          <select id="filter-produk-status" class="gk-admin-select">
            <option value="all">Semua status</option>
            <option value="aktif">Aktif</option>
            <option value="habis">Habis</option>
            <option value="nonaktif">Nonaktif</option>
          </select>
        </div>

        <div class="gk-admin-table-wrap">
          <table id="dt-produk" class="table table-striped table-hover w-100">
            <thead>
              <tr>
                <th>Foto</th>
                <th>Nama</th>
                <th>Kategori</th>
                <th>Varian</th>
                <th>Harga</th>
                <th>Stok</th>
                <th>Status</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($products as $p): ?>
              <tr>
                <td>
                  <img class="gk-admin-thumb" src="<?= gk_h($p['gambar']) ?>" alt="">
                </td>
                <td><?= gk_h($p['nama']) ?></td>
                <td><?= gk_h($p['kategori']) ?></td>
                <td><?= (int) $p['varian'] ?></td>
                <td data-order="<?= (int) $p['harga'] ?>"><?= gk_h(gk_format_rupiah($p['harga'])) ?></td>
                <td data-order="<?= (int) $p['stok'] ?>"><?= (int) $p['stok'] ?></td>
                <td data-search="<?= gk_h($p['status']) ?>">
                  <span class="gk-admin-status-chip is-<?= $p['status'] === 'aktif' ? 'siap_diambil' : ($p['status'] === 'habis' ? 'menunggu_pembayaran' : 'dibatalkan') ?>">
                    <?= gk_h($p['status']) ?>
                  </span>
                </td>
                <td>
                  <div class="gk-admin-actions">
                    <button
                      type="button"
                      class="gk-admin-btn gk-admin-btn-ghost gk-admin-btn-sm"
                      data-edit-product
                      data-id="<?= (int) $p['id'] ?>"
                      data-nama="<?= gk_h($p['nama']) ?>"
                      data-kategori="<?= gk_h($p['kategori']) ?>"
                      data-harga="<?= (int) $p['harga'] ?>"
                      data-stok="<?= (int) $p['stok'] ?>"
                      data-status="<?= gk_h($p['status']) ?>">
                      Edit
                    </button>
                    <button
                      type="button"
                      class="gk-admin-btn gk-admin-btn-outline gk-admin-btn-sm"
                      data-adjust-stock
                      data-nama="<?= gk_h($p['nama']) ?>">
                      Stok
                    </button>
                  </div>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <div class="modal fade" id="modal-produk" tabindex="-1" role="dialog" aria-hidden="true">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="modal-produk-title">Tambah Menu</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Tutup"><span>&times;</span></button>
              </div>
              <div class="modal-body">
                <div class="gk-admin-form-grid">
                  <div class="gk-admin-field" style="grid-column:1 / -1;">
                    <label for="form-produk-nama">Nama produk</label>
                    <input type="text" id="form-produk-nama" class="gk-admin-input">
                  </div>
                  <div class="gk-admin-field">
                    <label for="form-produk-kategori">Kategori</label>
                    <select id="form-produk-kategori" class="gk-admin-select">
                      <option>Makanan</option>
                      <option>Minuman</option>
                      <option>Snack</option>
                    </select>
                  </div>
                  <div class="gk-admin-field">
                    <label for="form-produk-status">Status</label>
                    <select id="form-produk-status" class="gk-admin-select">
                      <option value="aktif">aktif</option>
                      <option value="habis">habis</option>
                      <option value="nonaktif">nonaktif</option>
                    </select>
                  </div>
                  <div class="gk-admin-field">
                    <label for="form-produk-harga">Harga</label>
                    <input type="number" id="form-produk-harga" class="gk-admin-input" min="0">
                  </div>
                  <div class="gk-admin-field">
                    <label for="form-produk-stok">Stok</label>
                    <input type="number" id="form-produk-stok" class="gk-admin-input" min="0">
                  </div>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="gk-admin-btn gk-admin-btn-ghost" data-dismiss="modal">Batal</button>
                <button type="button" class="gk-admin-btn" data-preview-save="Preview: menu disimpan">Simpan</button>
              </div>
            </div>
          </div>
        </div>

        <div class="modal fade" id="modal-stok" tabindex="-1" role="dialog" aria-hidden="true">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title">Sesuaikan Stok</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Tutup"><span>&times;</span></button>
              </div>
              <div class="modal-body">
                <div class="gk-admin-field" style="margin-bottom:12px;">
                  <label for="form-stok-nama">Produk</label>
                  <input type="text" id="form-stok-nama" class="gk-admin-input" readonly>
                </div>
                <div class="gk-admin-form-grid">
                  <div class="gk-admin-field">
                    <label for="form-stok-jenis">Jenis</label>
                    <select id="form-stok-jenis" class="gk-admin-select">
                      <option value="masuk">masuk</option>
                      <option value="keluar">keluar</option>
                      <option value="penyesuaian">penyesuaian</option>
                    </select>
                  </div>
                  <div class="gk-admin-field">
                    <label for="form-stok-jumlah">Jumlah</label>
                    <input type="number" id="form-stok-jumlah" class="gk-admin-input" min="1" value="1">
                  </div>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="gk-admin-btn gk-admin-btn-ghost" data-dismiss="modal">Batal</button>
                <button type="button" class="gk-admin-btn" data-preview-save="Preview: stok disesuaikan">Terapkan</button>
              </div>
            </div>
          </div>
        </div>
