<?php
/**
 * @var array  $products
 * @var array  $categories
 * @var int    $activeCategoryId
 * @var string $query
 * @var int    $page
 * @var int    $totalPages
 * @var int    $totalProducts
 * @var int    $allCount
 * @var array  $categoryCounts
 */

$activeCategoryId = (int) ($activeCategoryId ?? 0);
$query = (string) ($query ?? '');
$page = max(1, (int) ($page ?? 1));
$totalPages = max(1, (int) ($totalPages ?? 1));
$totalProducts = (int) ($totalProducts ?? 0);
$allCount = (int) ($allCount ?? 0);
$categoryCounts = $categoryCounts ?? [];
$categories = $categories ?? [];
$products = $products ?? [];

$categoryIcons = [
    'makanan' => 'fas fa-hamburger',
    'minuman' => 'fas fa-coffee',
    'snack' => 'fas fa-cookie',
];

$buildMenuUrl = static function (array $params = []) use ($activeCategoryId, $query): string {
    $base = [
        'kategori' => $activeCategoryId > 0 ? $activeCategoryId : null,
        'q' => $query !== '' ? $query : null,
    ];
    $merged = array_filter(
        array_merge($base, $params),
        static fn($value): bool => $value !== null && $value !== '' && $value !== 0
    );

    return '/user/menu.php' . ($merged !== [] ? '?' . http_build_query($merged) : '');
};
?>
<section class="sb-menu-section sb-p-90-60">
  <form class="gk-search" action="/user/menu.php" method="get">
    <?php if ($activeCategoryId > 0): ?>
      <input type="hidden" name="kategori" value="<?= $activeCategoryId ?>">
    <?php endif; ?>
    <i class="fas fa-search"></i>
    <input type="search" name="q" value="<?= gk_h($query) ?>" placeholder="Cari menu atau kategori">
  </form>
  <div class="container">
    <div class="gk-stats gk-menu-tabs">
      <a href="<?= gk_h($buildMenuUrl(['kategori' => null, 'page' => null])) ?>"
         class="gk-stat-card<?= $activeCategoryId === 0 ? ' is-active' : '' ?>">
        <span class="gk-stat-icon"><i class="fas fa-utensils"></i></span>
        <span class="gk-stat-body">
          <strong><?= $allCount ?></strong>
          <span>Semua menu</span>
        </span>
      </a>
      <?php foreach ($categories as $category): ?>
        <?php
        $catId = (int) $category['id'];
        $slug = (string) ($category['slug'] ?? '');
        $icon = $categoryIcons[$slug] ?? 'fas fa-utensils';
        $count = (int) ($categoryCounts[$catId] ?? 0);
        $isActive = $activeCategoryId === $catId;
        ?>
        <a href="<?= gk_h($buildMenuUrl(['kategori' => $catId, 'page' => null])) ?>"
           class="gk-stat-card<?= $isActive ? ' is-active' : '' ?>">
          <span class="gk-stat-icon"><i class="<?= gk_h($icon) ?>"></i></span>
          <span class="gk-stat-body">
            <strong><?= $count ?></strong>
            <span><?= gk_h((string) $category['name']) ?></span>
          </span>
        </a>
      <?php endforeach; ?>
    </div>

    <?php if ($products === []): ?>
      <div class="gk-panel" style="margin-bottom: 24px;">
        <p style="margin:0;">
          <?= $query !== ''
            ? 'Tidak ada menu yang cocok dengan “' . gk_h($query) . '”.'
            : 'Belum ada menu di kategori ini.' ?>
        </p>
      </div>
    <?php else: ?>
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
    <?php endif; ?>

    <?php if ($totalPages > 1): ?>
      <ul class="sb-pagination gk-pagination">
        <?php if ($page > 1): ?>
          <li>
            <a href="<?= gk_h($buildMenuUrl(['page' => $page - 1])) ?>" aria-label="Halaman sebelumnya">
              <i class="fas fa-arrow-left"></i>
            </a>
          </li>
        <?php endif; ?>
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
          <li class="<?= $i === $page ? 'sb-active' : '' ?>">
            <a href="<?= gk_h($buildMenuUrl(['page' => $i])) ?>"><?= $i ?></a>
          </li>
        <?php endfor; ?>
        <?php if ($page < $totalPages): ?>
          <li>
            <a href="<?= gk_h($buildMenuUrl(['page' => $page + 1])) ?>" aria-label="Halaman berikutnya">
              <i class="fas fa-arrow-right"></i>
            </a>
          </li>
        <?php endif; ?>
      </ul>
      <p class="gk-menu-page-meta">Menampilkan <?= count($products) ?> dari <?= $totalProducts ?> menu</p>
    <?php endif; ?>
  </div>
</section>
