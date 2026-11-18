<?php
/**
 * @var array|null $product
 * @var array      $related
 * @var int        $cartCount
 */

use App\Core\View;

$product = $product ?? null;
$related = $related ?? [];
$cartCount = (int) ($cartCount ?? 0);
$variants = is_array($product['variants'] ?? null) ? $product['variants'] : [];
$stockTotal = (int) ($product['stock_total'] ?? 0);
$modalPayload = $product ? [
    'id' => (int) $product['id'],
    'name' => (string) $product['name'],
    'image' => (string) $product['image_path'],
    'price' => (float) $product['price'],
    'variants' => $variants,
] : null;
$priceInt = $product ? (int) round((float) $product['price']) : 0;
$priceFmt = number_format($priceInt, 0, ',', '.');
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
<section class="sb-p-90-0 gk-product-detail">
  <div class="container">
    <div class="gk-product-detail-layout">
      <div class="gk-product-media">
        <img src="<?= gk_h($product['image_path']) ?>" alt="<?= gk_h($product['name']) ?>">
        <?php if (!empty($product['is_vegan'])): ?>
          <div class="gk-product-badge"><i class="fas fa-leaf"></i> Nabati</div>
        <?php endif; ?>
      </div>
      <div class="gk-product-info">
        <div class="gk-product-price-frame">
          <h1><?= gk_h($product['name']) ?></h1>
          <div class="gk-product-price"><sub>Rp</sub> <?= gk_h($priceFmt) ?></div>
        </div>

        <p class="gk-product-desc">
          <?= !empty($product['description'])
            ? gk_h((string) $product['description'])
            : 'Tidak ada deskripsi.' ?>
        </p>
        <div class="gk-product-actions">
          <p class="gk-stock-text<?= $stockTotal <= 0 ? ' is-empty' : '' ?>">
            <i class="fas fa-box"></i>
            <?php if ($stockTotal > 0): ?>
              Stok tersedia: <?= $stockTotal ?>
            <?php else: ?>
              Stok habis
            <?php endif; ?>
          </p>
          <div class="gk-product-actions-right">
            <button
              type="button"
              class="gk-product-cart-btn"
              data-open-variant-modal
              data-product="<?= gk_h(json_encode($modalPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?>"
              <?= $stockTotal <= 0 ? 'disabled' : '' ?>>
              <span class="gk-product-cart-icon">
                <i class="fas fa-plus"></i>
              </span>
              <span>Tambahkan</span>
            </button>
            <a href="/user/cart.php" class="gk-product-cart-link" aria-label="Buka keranjang">
              <i class="fas fa-shopping-cart"></i>
              <?php if ($cartCount > 0): ?>
                <span class="gk-product-cart-badge"><?= $cartCount ?></span>
              <?php endif; ?>
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

<?php View::partial('pembeli/partials/variant-modal'); ?>
<?php endif; ?>
