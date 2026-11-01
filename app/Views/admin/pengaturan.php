<?php
/** @var array $stand */
?>
        <div class="gk-admin-page-head">
          <div>
            <h1>Pengaturan Stand</h1>
            <p>Profil usaha, jam operasional, dan status buka/tutup (preview UI).</p>
          </div>
        </div>

        <div class="gk-admin-card" style="max-width:720px;">
          <form id="form-pengaturan">
            <div class="gk-admin-form-grid">
              <div class="gk-admin-field" style="grid-column:1 / -1;">
                <label for="stand-nama">Nama usaha</label>
                <input type="text" id="stand-nama" class="gk-admin-input" name="nama_usaha" value="<?= gk_h($stand['nama_usaha']) ?>">
              </div>
              <div class="gk-admin-field" style="grid-column:1 / -1;">
                <label for="stand-lokasi">Lokasi</label>
                <input type="text" id="stand-lokasi" class="gk-admin-input" name="lokasi" value="<?= gk_h($stand['lokasi']) ?>">
              </div>
              <div class="gk-admin-field">
                <label for="stand-buka">Jam buka</label>
                <input type="time" id="stand-buka" class="gk-admin-input" name="jam_buka" value="<?= gk_h($stand['jam_buka']) ?>">
              </div>
              <div class="gk-admin-field">
                <label for="stand-tutup">Jam tutup</label>
                <input type="time" id="stand-tutup" class="gk-admin-input" name="jam_tutup" value="<?= gk_h($stand['jam_tutup']) ?>">
              </div>
              <div class="gk-admin-field">
                <label for="stand-hp">No. HP</label>
                <input type="text" id="stand-hp" class="gk-admin-input" name="no_hp" value="<?= gk_h($stand['no_hp']) ?>">
              </div>
              <div class="gk-admin-field">
                <label>Status kantin</label>
                <label class="gk-admin-switch">
                  <input type="checkbox" name="status_buka" <?= !empty($stand['status_buka']) ? 'checked' : '' ?>>
                  <span>Buka untuk pesanan</span>
                </label>
              </div>
            </div>
            <div style="margin-top:18px;">
              <button type="submit" class="gk-admin-btn">Simpan Pengaturan</button>
            </div>
          </form>
        </div>
