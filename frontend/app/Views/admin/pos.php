<?php
/** @var array $products */
/** @var string $receiptNo */
?>
        <div class="gk-admin-page-head">
          <div>
            <h1>Kasir POS &amp; Scan QR</h1>
            <p>Transaksi walk-in dan validasi tiket self pick-up mahasiswa.</p>
          </div>
        </div>

        <div class="gk-admin-card gk-admin-scan-bar">
          <div style="flex:1;min-width:220px;">
            <strong>Validasi Cepat Tiket QR Self Pick-Up</strong>
            <div class="sub" style="font-size:12px;color:#6b7280;">Masukkan nomor tiket / scan QR pesanan online</div>
          </div>
          <input type="text" id="gk-qr-input" class="gk-admin-input" placeholder="Contoh: ABC-123-XYZ atau A-014">
          <button type="button" class="gk-admin-btn" id="gk-scan-qr">
            <i class="fas fa-qrcode"></i> Scan / Verifikasi QR Pick-Up
          </button>
        </div>

        <div class="gk-admin-pos">
          <div>
            <div class="gk-admin-toolbar">
              <div class="gk-admin-pills">
                <button type="button" class="gk-admin-pill is-active" data-pos-category="Semua">Semua</button>
                <button type="button" class="gk-admin-pill" data-pos-category="Makanan">Makanan</button>
                <button type="button" class="gk-admin-pill" data-pos-category="Minuman">Minuman</button>
                <button type="button" class="gk-admin-pill" data-pos-category="Snack">Snack</button>
              </div>
              <input type="search" id="gk-pos-search" class="gk-admin-input" placeholder="Cari menu...">
            </div>

            <div class="gk-admin-product-grid">
              <?php foreach ($products as $p): ?>
              <?php $disabled = (int) $p['stok'] <= 0; ?>
              <article
                class="gk-admin-product-card<?= $disabled ? ' is-disabled' : '' ?>"
                data-kategori="<?= gk_h($p['kategori']) ?>"
                data-nama="<?= gk_h($p['nama']) ?>">
                <img src="<?= gk_h($p['gambar']) ?>" alt="<?= gk_h($p['nama']) ?>">
                <div class="gk-admin-product-body">
                  <strong><?= gk_h($p['nama']) ?></strong>
                  <span class="meta"><?= gk_h($p['kategori']) ?> · Stok: <?= (int) $p['stok'] ?></span>
                  <span class="price"><?= gk_h(gk_format_rupiah($p['harga'])) ?></span>
                  <button
                    type="button"
                    class="gk-admin-btn gk-admin-btn-outline gk-admin-btn-sm"
                    data-add-pos
                    data-id="<?= (int) $p['id'] ?>"
                    data-nama="<?= gk_h($p['nama']) ?>"
                    data-harga="<?= (int) $p['harga'] ?>"
                    <?= $disabled ? 'disabled' : '' ?>>
                    + Tambah ke Nota POS
                  </button>
                </div>
              </article>
              <?php endforeach; ?>
            </div>
          </div>

          <aside class="gk-admin-receipt">
            <h2>Nota Kasir POS</h2>
            <div class="sub">No. <?= gk_h($receiptNo) ?></div>
            <ul class="gk-admin-cart-list" id="gk-pos-cart"></ul>
            <div id="gk-pos-empty" class="sub">Belum ada item di nota.</div>

            <div class="gk-admin-pay-toggle">
              <button type="button" class="is-active" data-pay="tunai">Tunai (Cash)</button>
              <button type="button" data-pay="qris">QRIS Statis</button>
            </div>

            <div id="gk-cash-fields">
              <label for="gk-uang-diterima" style="font-size:13px;font-weight:600;">Uang Diterima</label>
              <input type="number" id="gk-uang-diterima" class="gk-admin-input" placeholder="0" min="0" step="500">
              <div class="gk-admin-quick-cash">
                <button type="button" data-cash-quick="pas">Uang Pas</button>
                <button type="button" data-cash-quick="20000">Rp20.000</button>
                <button type="button" data-cash-quick="50000">Rp50.000</button>
              </div>
              <div class="gk-admin-change">Kembalian: <span id="gk-pos-change">Rp0</span></div>
            </div>

            <div class="gk-admin-bill-total">Total: <span id="gk-pos-total">Rp0</span></div>
            <button type="button" class="gk-admin-btn gk-admin-btn-block" id="gk-pos-save">
              Simpan Transaksi POS &amp; Cetak Nota
            </button>
          </aside>
        </div>

        <div class="modal fade" id="gk-qr-modal" tabindex="-1" role="dialog" aria-labelledby="gk-qr-title" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="gk-qr-title">Hasil Pemindai Tiket QR</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body">
                <div class="gk-admin-status is-open" style="margin-bottom:12px;">
                  <strong>Tiket QR Valid</strong>
                  <span>Antrean A-014 · Farhan Ramadhan · Ambil 09:30-10:00 · QRIS</span>
                </div>
                <div class="gk-admin-order-items">
                  <div>1x Oreo Soft Cake — Rp3.000</div>
                  <div>2x Air Mineral — Rp6.000</div>
                  <div style="margin-top:8px;font-weight:700;">Total Transaksi: Rp11.000</div>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="gk-admin-btn gk-admin-btn-ghost" data-dismiss="modal">Batal</button>
                <button type="button" class="gk-admin-btn" id="gk-qr-handover">Serahkan / Selesai</button>
              </div>
            </div>
          </div>
        </div>
