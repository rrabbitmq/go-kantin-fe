        <section class="gk-page-head">
          <h1>Riwayat Pesanan</h1>
          <p>Lihat pesanan aktif dan riwayat pengambilan kamu di kantin.</p>
        </section>

        <div class="gk-history-filters">
          <button type="button" class="gk-chip gk-active">Semua</button>
          <button type="button" class="gk-chip">Diproses</button>
          <button type="button" class="gk-chip">Siap Ambil</button>
          <button type="button" class="gk-chip">Selesai</button>
        </div>

        <div class="gk-history-list">
          <?php foreach ($orders as $order): ?>
          <?php
            $status = $order['status'];
            $btnClass = $status === 'done' ? 'gk-btn-ghost' : 'gk-btn-primary';
            $btnLabel = $status === 'done' ? 'Lihat Detail' : 'Lihat Status';
          ?>
          <article class="gk-panel gk-history-card" data-order-id="<?= (int) $order['id'] ?>">
            <div class="gk-history-card-top">
              <div>
                <span class="gk-label">Nomor Pesanan</span>
                <strong class="gk-order-id gk-order-id-sm">#<?= gk_h($order['order_code']) ?></strong>
              </div>
              <span class="gk-badge <?= gk_h(gk_order_badge_class($status)) ?>"><?= gk_h(gk_order_status_label($status)) ?></span>
            </div>
            <div class="gk-history-card-meta">
              <span><i class="far fa-calendar"></i> <?= gk_h(gk_format_order_datetime($order['created_at'])) ?></span>
              <span><i class="fas fa-utensils"></i> <?= (int) $order['menu_count'] ?> menu • <?= (int) $order['item_count'] ?> item</span>
            </div>
            <div class="gk-history-thumbs">
              <?php foreach ($order['thumbs'] as $thumb): ?>
              <img src="<?= gk_h($thumb) ?>" alt="">
              <?php endforeach; ?>
            </div>
            <div class="gk-history-card-foot">
              <div>
                <span class="gk-label">Total Pembayaran</span>
                <strong class="gk-history-total"><?= gk_h(gk_format_rupiah($order['total_amount'])) ?></strong>
              </div>
              <a href="/user/status-pesanan.php?id=<?= (int) $order['id'] ?>" class="<?= $btnClass ?> gk-btn-inline"><?= gk_h($btnLabel) ?></a>
            </div>
          </article>
          <?php endforeach; ?>
        </div>
