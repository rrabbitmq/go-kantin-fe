<?php
/**
 * Carousel menu reusable.
 *
 * @var string $title
 * @var string $subtitle
 * @var array  $products
 * @var string $moreHref
 * @var string $moreLabel
 */
$title = $title ?? 'Menu';
$subtitle = $subtitle ?? '';
$products = $products ?? [];
$moreHref = $moreHref ?? '/user/menu.php';
$moreLabel = $moreLabel ?? 'Selengkapnya';
?>
<section class="sb-short-menu gk-menu-carousel">
  <div class="sb-group-title gk-menu-carousel-head">
    <div class="sb-left">
      <h2 class="sb-cate-title"><?= gk_h($title) ?></h2>
      <?php if ($subtitle !== ''): ?>
        <p class="sb-text"><?= gk_h($subtitle) ?></p>
      <?php endif; ?>
    </div>
    <div class="sb-right">
      <div class="sb-slider-nav">
        <button type="button" class="sb-prev-btn gk-menu-carousel-prev" aria-label="Sebelumnya">
          <i class="fas fa-arrow-left"></i>
        </button>
        <button type="button" class="sb-next-btn gk-menu-carousel-next" aria-label="Berikutnya">
          <i class="fas fa-arrow-right"></i>
        </button>
      </div>
      <a href="<?= gk_h($moreHref) ?>" class="sb-btn">
        <span class="sb-icon">
          <img src="/img/ui/icons/arrow.svg" alt="">
        </span>
        <span><?= gk_h($moreLabel) ?></span>
      </a>
    </div>
  </div>
  <?php if ($products === []): ?>
    <div class="gk-panel gk-menu-carousel-empty">
      <p>Belum ada menu di bagian ini.</p>
    </div>
  <?php else: ?>
    <div class="swiper-container gk-menu-carousel-slider">
      <div class="swiper-wrapper">
        <?php foreach ($products as $product): ?>
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
  <?php endif; ?>
</section>
