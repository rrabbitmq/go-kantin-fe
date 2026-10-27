<?php
$pageTitle = 'Detail Produk - Go Kantin';
$activeNav = 'menu';
require __DIR__ . '/../includes/layout-start.php';

$productId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$product = $productId > 0 ? gk_get_product($productId) : null;
$related = $product ? gk_get_related_products((int) $product['id'], 6) : [];
?>
        <?php if (!$product): ?>
        <section class="sb-p-90-90">
          <div class="container">
            <div class="gk-panel">
              <h2>Produk tidak ditemukan</h2>
              <p>ID produk tidak valid atau belum tersedia.</p>
              <a href="/user/menu.php" class="gk-btn-primary gk-btn-inline">Kembali ke Menu</a>
            </div>
          </div>
        </section>
        <?php else: ?>
        <section class="sb-p-90-0">
          <div class="container">
            <div class="row align-items-center">
              <div class="col-lg-6">
                <div class="sb-gallery-item sb-gallery-square sb-mb-90">
                  <img src="<?= gk_h($product['image_path']) ?>" alt="<?= gk_h($product['name']) ?>">
                  <?php if (!empty($product['is_vegan'])): ?>
                  <div class="sb-badge sb-vegan"><i class="fas fa-leaf"></i> Nabati</div>
                  <?php endif; ?>
                  <a data-fancybox="menu" data-no-swup href="<?= gk_h($product['image_path']) ?>"
                    class="sb-btn sb-btn-2 sb-btn-icon sb-btn-gray sb-zoom">
                    <span class="sb-icon">
                      <img src="/img/ui/icons/zoom.html" alt="icon">
                    </span>
                  </a>
                </div>
              </div>
              <div class="col-lg-6">
                <div class="sb-product-description sb-mb-90">
                  <div class="sb-price-frame sb-mb-30">
                    <h3><?= gk_h($product['name']) ?></h3>
                    <div class="sb-price"><sub>Rp</sub> <?= gk_h(number_format((float) $product['price'], 0, ',', '.')) ?></div>
                  </div>
                  <ul class="sb-stars sb-mb-25">
                    <li><i class="fas fa-star"></i></li>
                    <li><i class="fas fa-star"></i></li>
                    <li><i class="fas fa-star"></i></li>
                    <li><i class="fas fa-star"></i></li>
                    <li><i class="fas fa-star"></i></li>
                    <li><span>(<?= (int) $product['rating_count'] ?> penilaian)</span></li>
                  </ul>
                  <p class="sb-text sb-mb-30"><?= gk_ingredient_spans($product['ingredients'] ?: $product['description']) ?>.</p>
                  <div class="row">
                    <div class="col-lg-4">
                      <div class="sb-features-item sb-features-item-sm sb-mb-30">
                        <div class="sb-number">01</div>
                        <div class="sb-feature-text">
                          <h4 class="sb-mb-15">Pilih Menu</h4>
                          <p class="sb-text sb-text-sm">Pilih menu dan jumlah sesuai keinginanmu</p>
                        </div>
                      </div>
                    </div>
                    <div class="col-lg-4">
                      <div class="sb-features-item sb-features-item-sm sb-mb-30">
                        <div class="sb-number">02</div>
                        <div class="sb-feature-text">
                          <h4 class="sb-mb-15">Metode Pembayaran</h4>
                          <p class="sb-text sb-text-sm">Cek keranjang dan pilih metode pembayaran</p>
                        </div>
                      </div>
                    </div>
                    <div class="col-lg-4">
                      <div class="sb-features-item sb-features-item-sm sb-mb-30">
                        <div class="sb-number">03</div>
                        <div class="sb-feature-text">
                          <h4 class="sb-mb-15">Pesanan Siap</h4>
                          <p class="sb-text sb-text-sm">Lihat status pesanan, ambil jika sudah siap!</p>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="sb-buttons-frame">
                    <div class="sb-input-number-frame">
                      <div class="sb-input-number-btn sb-sub">-</div>
                      <input type="number" value="1" min="1" max="10">
                      <div class="sb-input-number-btn sb-add">+</div>
                    </div>
                    <a href="/user/cart.php" class="sb-btn sb-atc">
                      <span class="sb-icon">
                        <img src="/img/ui/icons/cart.svg" alt="icon">
                      </span>
                      <span class="sb-add-to-cart-text">Tambahkan</span>
                      <span class="sb-added-text">Ditambahkan</span>
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>

        <section class="sb-short-menu sb-p-0-90">
          <div class="container">
            <div class="sb-group-title sb-mb-30">
              <div class="sb-left sb-mb-30">
                <h2 class="sb-cate-title sb-mb-30">Menu Lainnya</h2>
                <p class="sb-text">Rekomendasi menu lain untukmu</p>
              </div>
              <div class="sb-right sb-mb-30">
                <div class="sb-slider-nav">
                  <div class="sb-prev-btn sb-short-menu-prev"><i class="fas fa-arrow-left"></i></div>
                  <div class="sb-next-btn sb-short-menu-next"><i class="fas fa-arrow-right"></i></div>
                </div>
              </div>
            </div>
            <div class="swiper-container sb-short-menu-slider-4i">
              <div class="swiper-wrapper">
                <?php foreach ($related as $item): ?>
                <div class="swiper-slide">
                  <a href="/user/detail-product.php?id=<?= (int) $item['id'] ?>" class="gk-product-card">
                    <div class="gk-thumb"><img src="<?= gk_h($item['image_path']) ?>" alt="<?= gk_h($item['name']) ?>"></div>
                    <div class="gk-body">
                      <h3><?= gk_h($item['name']) ?></h3>
                      <div class="gk-cat"><?= gk_h($item['category_name']) ?></div>
                      <div class="gk-row">
                        <div class="gk-price"><?= gk_h(gk_format_rupiah($item['price'])) ?></div>
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
        <?php endif; ?>
<?php require __DIR__ . '/../includes/layout-end.php'; ?>
