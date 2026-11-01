        <section class="sb-p-90-60">
          <div class="container">
            <div class="row">
              <?php foreach ($categories as $category): ?>
              <div class="col-6 col-lg-4">
                <a href="/user/menu.php?kategori=<?= (int) $category['id'] ?>" class="sb-categorie-card sb-categorie-card-2 sb-mb-30">
                  <div class="sb-card-body">
                    <div class="sb-category-icon">
                      <img src="<?= gk_h($category['icon_path']) ?>" alt="<?= gk_h($category['name']) ?>">
                    </div>
                    <div class="sb-card-descr">
                      <h3 class="sb-mb-10"><?= gk_h($category['name']) ?></h3>
                      <p class="sb-text"><?= gk_h($category['description']) ?></p>
                    </div>
                  </div>
                </a>
              </div>
              <?php endforeach; ?>
            </div>
          </div>
        </section>

        <section class="sb-short-menu sb-p-0-90">
          <div class="container">
            <div class="sb-group-title sb-mb-30">
              <div class="sb-left sb-mb-30">
                <h2 class="sb-cate-title sb-mb-30">Menu Best Seller</h2>
                <p class="sb-text">Rekomendasi menu yang paling banyak disukai dan dibeli bulan ini</p>
              </div>
              <div class="sb-right sb-mb-30">
                <div class="sb-slider-nav">
                  <div class="sb-prev-btn sb-short-menu-prev"><i class="fas fa-arrow-left"></i></div>
                  <div class="sb-next-btn sb-short-menu-next"><i class="fas fa-arrow-right"></i></div>
                </div>
                <a href="/user/menu.php" class="sb-btn">
                  <span class="sb-icon">
                    <img src="/img/ui/icons/arrow.svg" alt="icon">
                  </span>
                  <span>Lihat semua</span>
                </a>
              </div>
            </div>
            <div class="swiper-container sb-short-menu-slider-4i">
              <div class="swiper-wrapper">
                <?php foreach ($bestsellers as $product): ?>
                <div class="swiper-slide">
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
                </div>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
        </section>
