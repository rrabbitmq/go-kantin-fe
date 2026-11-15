<?php
/** @var array $logs */
?>
        <div class="gk-admin-page-head">
          <div>
            <h1>Mutasi Stok</h1>
            <p>Riwayat perubahan stok: masuk, keluar, penyesuaian, dan pembatalan.</p>
          </div>
          <a href="/admin/menu.php" class="gk-admin-btn gk-admin-btn-outline">Kelola Menu</a>
        </div>

        <div class="gk-admin-table-wrap">
          <table id="dt-mutasi" class="table table-striped table-hover w-100">
            <thead>
              <tr>
                <th>Waktu</th>
                <th>Produk / Varian</th>
                <th>Jenis</th>
                <th>Jumlah</th>
                <th>Sebelum</th>
                <th>Sesudah</th>
                <th>Petugas</th>
                <th>Keterangan</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($logs as $log): ?>
              <tr>
                <td><?= gk_h($log['waktu']) ?></td>
                <td><?= gk_h($log['produk']) ?></td>
                <td>
                  <span class="gk-admin-status-chip is-<?= $log['jenis'] === 'keluar' ? 'menunggu_pembayaran' : ($log['jenis'] === 'pembatalan' ? 'dibatalkan' : 'siap_diambil') ?>">
                    <?= gk_h($log['jenis']) ?>
                  </span>
                </td>
                <td data-order="<?= (int) $log['jumlah'] ?>"><?= (int) $log['jumlah'] ?></td>
                <td><?= (int) $log['sebelum'] ?></td>
                <td><?= (int) $log['sesudah'] ?></td>
                <td><?= gk_h($log['petugas']) ?></td>
                <td><?= gk_h($log['keterangan']) ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
