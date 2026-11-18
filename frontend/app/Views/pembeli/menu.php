        <section class="sb-menu-section sb-p-90-60">
          <form class="gk-search" action="/user/beranda.php" method="get">
            <i class="fas fa-search"></i>
            <input type="search" name="q" value="<?= gk_h($query) ?>" placeholder="Cari menu atau kategori">
          </form>
          <div class="container">
            <div class="gk-stats">
              <a href="/user/menu.php" class="gk-stat-card">
                <span class="gk-stat-icon"><i class="fas fa-utensils"></i></span>
                <span class="gk-stat-body">
                  <strong><?= (int) $menuCount ?></strong>
                  <span>Menu tersedia</span>
                </span>
              </a>
              <a href="/user/cart.php" class="gk-stat-card">
                <span class="gk-stat-icon"><i class="fas fa-hamburger"></i></span>
                <span class="gk-stat-body">
                  <strong><?= (int) $cartCount ?></strong>
                  <span>Makanan</span>
                </span>
              </a>
              <a href="/user/status-pesanan.php" class="gk-stat-card">
                <span class="gk-stat-icon"><i class="fas fa-coffee"></i></span>
                <span class="gk-stat-body">
                  <strong><?= $activeOrder ? '1' : '0' ?></strong>
                  <span>Minuman</span>
                </span>
              </a>
              <a href="/user/queue.php" class="gk-stat-card<?= $queueBusy ? ' gk-stat-card-busy' : '' ?> gk-density-card is-<?= gk_h($queueTone) ?>">
                <span class="gk-stat-icon"><i class="fas fa-cookie"></i></span>
                <span class="gk-stat-body">
                  <strong>Snack</strong>
                </span>
              </a>
            </div>
            <div class="sb-masonry-grid">
              <div class="sb-grid-sizer"></div>
              <?php foreach ($products as $product): ?>
                <div class="sb-grid-item sb-item-25 <?= gk_h($product['filter_class']) ?>">
                  <a href="/user/detail-product.php?id=<?= (int) $product['id'] ?>" class="gk-product-card sb-mb-30">
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
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </section>