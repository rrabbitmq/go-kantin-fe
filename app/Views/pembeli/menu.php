        <section class="sb-menu-section sb-p-90-60">
          <div class="container">
            <div class="sb-filter mb-30">
              <a href="#." data-filter="*" class="sb-filter-link sb-active">Semua</a>
              <a href="#." data-filter=".main-dishes" class="sb-filter-link">Makanan</a>
              <a href="#." data-filter=".drinks" class="sb-filter-link">Minuman</a>
              <a href="#." data-filter=".desserts, .starters" class="sb-filter-link">Snack</a>
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
