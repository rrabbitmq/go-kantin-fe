<?php
/** @var array $rows */
$statusLabels = [
    'menunggu_pembayaran' => 'Menunggu Pembayaran',
    'dibayar' => 'Dibayar',
    'diproses' => 'Diproses',
    'siap_diambil' => 'Siap Ambil',
    'selesai' => 'Selesai',
    'dibatalkan' => 'Dibatalkan',
];
?>
        <div class="gk-admin-page-head">
          <div>
            <h1>Laporan Transaksi</h1>
            <p>Riwayat pesanan online dan walk-in POS. Data preview untuk desain frontend.</p>
          </div>
        </div>

        <div class="gk-admin-toolbar">
          <select id="filter-laporan-status" class="gk-admin-select">
            <option value="all">Semua status</option>
            <?php foreach ($statusLabels as $key => $label): ?>
            <option value="<?= gk_h($key) ?>"><?= gk_h($label) ?></option>
            <?php endforeach; ?>
          </select>
          <select id="filter-laporan-sumber" class="gk-admin-select">
            <option value="all">Semua sumber</option>
            <option value="online">online</option>
            <option value="pos">pos</option>
          </select>
        </div>

        <div class="gk-admin-table-wrap">
          <table id="dt-laporan" class="table table-striped table-hover w-100">
            <thead>
              <tr>
                <th>Kode</th>
                <th>Waktu</th>
                <th>Pembeli</th>
                <th>Slot Ambil</th>
                <th>Sumber</th>
                <th>Metode</th>
                <th>Total</th>
                <th>Status</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($rows as $row): ?>
              <tr>
                <td><?= gk_h($row['kode']) ?></td>
                <td><?= gk_h($row['waktu']) ?></td>
                <td><?= gk_h($row['pembeli']) ?></td>
                <td><?= gk_h($row['slot']) ?></td>
                <td><?= gk_h($row['sumber']) ?></td>
                <td><?= gk_h($row['metode']) ?></td>
                <td data-order="<?= (int) $row['total'] ?>"><?= gk_h(gk_format_rupiah($row['total'])) ?></td>
                <td data-search="<?= gk_h($row['status']) ?>">
                  <span class="gk-admin-status-chip is-<?= gk_h($row['status']) ?>">
                    <?= gk_h($statusLabels[$row['status']] ?? $row['status']) ?>
                  </span>
                </td>
                <td>
                  <button type="button" class="gk-admin-btn gk-admin-btn-ghost gk-admin-btn-sm" data-preview-save="Preview: detail <?= gk_h($row['kode']) ?>">
                    Detail
                  </button>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
