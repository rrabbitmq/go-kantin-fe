        <div class="gk-home-greeting">
          <h1>Halo, <?= gk_h($currentUser['name'] ?? 'Tamu') ?></h1>
          <p>Pesan makan di kantin, pantau antrian, lalu ambil saat siap.</p>
        </div>

        <form class="gk-search" action="/user/beranda.php" method="get">
          <i class="fas fa-search"></i>
          <input type="search" name="q" value="<?= gk_h($query) ?>" placeholder="Cari menu atau kategori">
        </form>

        <div class="gk-stats">
          <a href="/user/menu.php" class="gk-stat-card">
            <span class="gk-stat-icon"><i class="fas fa-utensils"></i></span>
            <span class="gk-stat-body">
              <strong><?= (int) $menuCount ?></strong>
              <span>Menu tersedia</span>
            </span>
          </a>
          <a href="/user/katalog.php" class="gk-stat-card">
            <span class="gk-stat-icon"><i class="fas fa-th-large"></i></span>
            <span class="gk-stat-body">
              <strong><?= count($categories) ?></strong>
              <span>Kategori</span>
            </span>
          </a>
          <a href="/user/cart.php" class="gk-stat-card">
            <span class="gk-stat-icon"><i class="fas fa-shopping-bag"></i></span>
            <span class="gk-stat-body">
              <strong><?= (int) $cartCount ?></strong>
              <span>Item di keranjang</span>
            </span>
          </a>
          <a href="/user/status-pesanan.php" class="gk-stat-card">
            <span class="gk-stat-icon"><i class="far fa-clock"></i></span>
            <span class="gk-stat-body">
              <strong><?= $activeOrder ? '1' : '0' ?></strong>
              <span>Pesanan berjalan</span>
            </span>
          </a>
        </div>

        <div class="gk-chips">
          <a href="/user/menu.php" class="gk-chip gk-active"><i class="fas fa-border-all"></i> Semua</a>
          <?php foreach ($categories as $category): ?>
          <a href="/user/menu.php?kategori=<?= (int) $category['id'] ?>" class="gk-chip">
            <?= gk_h($category['name']) ?>
          </a>
          <?php endforeach; ?>
        </div>

        <h2 class="gk-section-title"><?= $query !== '' ? 'Hasil pencarian' : 'Menu pilihan' ?></h2>
        <?php if ($products === []): ?>
        <div class="gk-panel" style="margin-bottom: 36px;">
          <p style="margin:0;">Tidak ada menu yang cocok dengan “<?= gk_h($query) ?>”.</p>
        </div>
        <?php else: ?>
        <div class="gk-product-grid">
          <?php foreach ($products as $product): ?>
          <a href="/user/detail-product.php?id=<?= (int) $product['id'] ?>" class="gk-product-card">
            <div class="gk-thumb"><img src="<?= gk_h($product['image_path']) ?>" alt="<?= gk_h($product['name']) ?>"></div>
            <div class="gk-body">
              <h3><?= gk_h($product['name']) ?></h3>
              <div class="gk-cat"><?= gk_h($product['category_name']) ?></div>
              <div class="gk-row">
                <div class="gk-price"><?= gk_h(gk_format_rupiah($product['price'])) ?></div>
                <span class="gk-add-btn"><i class="fas fa-plus"></i> <span>Tambah</span></span>
              </div>
            </div>
          </a>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="gk-home-bottom">
          <div class="gk-panel">
            <div class="gk-panel-head">
              <h2>Pesanan berjalan</h2>
              <?php if ($activeOrder): ?>
              <span class="gk-status"><?= gk_h(gk_order_status_label((string) $activeOrder['status'])) ?></span>
              <?php endif; ?>
            </div>
            <?php if (!$activeOrder): ?>
            <p class="gk-order-meta">Belum ada pesanan yang sedang diproses.</p>
            <a href="/user/menu.php" class="gk-btn-primary">Pesan sekarang</a>
            <?php else: ?>
            <p class="gk-order-meta">#<?= gk_h($activeOrder['order_code']) ?> · <?= gk_h($activeOrder['pickup_label'] ?? 'Ambil di kantin') ?></p>
            <div class="gk-eta">
              <div>
                <span>Total pesanan</span>
                <strong><?= gk_h(gk_format_rupiah($activeOrder['total_amount'])) ?></strong>
              </div>
              <a href="/user/status-pesanan.php?id=<?= (int) $activeOrder['id'] ?>" class="gk-btn-ghost">Lihat status</a>
            </div>
            <?php endif; ?>
          </div>
          <div class="gk-panel">
            <div class="gk-panel-head">
              <h2>Keranjang</h2>
            </div>
            <?php if ($cartItems === []): ?>
            <p class="gk-order-meta">Keranjang masih kosong.</p>
            <?php else: ?>
            <?php foreach ($cartItems as $item): ?>
            <div class="gk-summary-line">
              <span><?= (int) $item['quantity'] ?>× <?= gk_h($item['name']) ?></span>
              <span><?= gk_h(gk_format_rupiah($item['line_total'])) ?></span>
            </div>
            <?php endforeach; ?>
            <div class="gk-summary-total">
              <span>Total</span>
              <span><?= gk_h(gk_format_rupiah($cartTotal)) ?></span>
            </div>
            <?php endif; ?>
            <a href="/user/cart.php" class="gk-btn-primary">Buka keranjang</a>
          </div>
        </div>
