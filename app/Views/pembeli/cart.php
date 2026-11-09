        <section class="sb-p-90-90">
          <div class="container">
            <h2 class="gk-section-title">Keranjang</h2>
            <div class="sb-cart-table">
              <div class="sb-cart-table-header d-none d-lg-block">
                <div class="row">
                  <div class="col-lg-6">Produk</div>
                  <div class="col-lg-3">Jumlah</div>
                  <div class="col-lg-1">Harga</div>
                  <div class="col-lg-1">Total</div>
                  <div class="col-lg-1"></div>
                </div>
              </div>
 
              <?php if (!$cartItems): ?>
              <p class="sb-text">Keranjang masih kosong. Yuk pilih menu dulu.</p>
              <?php endif; ?>
 
              <?php foreach ($cartItems as $item): ?>
              <div class="sb-cart-item" data-cart-id="<?= (int) $item['id'] ?>" data-product-id="<?= (int) $item['product_id'] ?>">
                <div class="row align-items-center">
                  <div class="col-12 col-lg-6">
                    <a class="sb-product" href="/user/detail-product.php?id=<?= (int) $item['product_id'] ?>">
                      <div class="sb-cover-frame">
                        <img src="<?= gk_h($item['image_path']) ?>" alt="<?= gk_h($item['name']) ?>">
                      </div>
                      <div class="sb-prod-description">
                        <h4 class="sb-mb-10"><?= gk_h($item['name']) ?></h4>
                        <p class="sb-text sb-text-sm"><?= gk_h($item['description']) ?></p>
                      </div>
                    </a>
                  </div>
                  <div class="col-12 col-sm-6 col-lg-3">
                    <div class="sb-input-number-frame">
                      <div class="sb-input-number-btn sb-sub">-</div>
                      <input type="number" value="<?= (int) $item['quantity'] ?>" min="1" max="<?= (int) max(1, min($item['stock'] ?? 10, 99)) ?>">
                      <div class="sb-input-number-btn sb-add">+</div>
                    </div>
                  </div>
                  <div class="col-6 col-sm-3 col-lg-1">
                    <div class="sb-price-1"><span>Harga: </span><?= gk_h(gk_format_rupiah($item['price'], true)) ?></div>
                  </div>
                  <div class="col-6 col-sm-3 col-lg-1">
                    <div class="sb-price-2"><span>Total: </span><?= gk_h(gk_format_rupiah($item['line_total'], true)) ?></div>
                  </div>
                  <div class="col-1">
                    <a href="#." class="sb-remove" aria-label="Hapus">+</a>
                  </div>
                </div>
              </div>
              <?php endforeach; ?>
 
              <div class="row justify-content-end">
                <div class="col-12 col-lg-6">
                  <div class="sb-cart-total">
                    <div class="sb-sum">
                      <div class="row">
                        <div class="col-7 col-sm-8">
                          <div class="sb-total-title">Subtotal:</div>
                        </div>
                        <div class="col-5 col-sm-4">
                          <div class="sb-price-1 text-right"><?= gk_h(gk_format_rupiah($subtotal, true)) ?></div>
                        </div>
                      </div>
                    </div>
                    <div class="sb-sum">
                      <div class="row">
                        <div class="col-7 col-sm-8">
                          <div class="sb-total-title">Biaya layanan:</div>
                        </div>
                        <div class="col-5 col-sm-4">
                          <div class="sb-price-1 text-right"><?= gk_h(gk_format_rupiah($shipping, true)) ?></div>
                        </div>
                      </div>
                    </div>
                    <div class="sb-realy-sum">
                      <div class="row">
                        <div class="col-7 col-sm-8">
                          <div class="sb-total-title">Total:</div>
                        </div>
                        <div class="col-5 col-sm-4">
                          <div class="sb-price-2 text-right"><?= gk_h(gk_format_rupiah($total, true)) ?></div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="sb-cart-btns-frame text-right">
                    <a href="/user/menu.php" class="sb-btn sb-btn-2 sb-btn-gray">
                      <span class="sb-icon">
                        <img src="/img/ui/icons/arrow.svg" alt="icon">
                      </span>
                      <span>Lanjut belanja</span>
                    </a>
                    <?php if ($cartItems): ?>
                    <a href="/user/checkout.php" class="sb-btn sb-m-0">
                      <span class="sb-icon">
                        <img src="/img/ui/icons/arrow.svg" alt="icon">
                      </span>
                      <span>Checkout</span>
                    </a>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>
 
        <script>
        (function () {
          var timers = {};
 
          function post(data) {
            return fetch('/user/cart-action.php', {
              method: 'POST',
              headers: { 'X-Requested-With': 'fetch' },
              body: new URLSearchParams(data)
            }).then(function (r) { return r.json(); });
          }
 
          function done(res) {
            if (!res.ok) { alert(res.message || 'Gagal memperbarui keranjang.'); }
            window.location.reload();
          }
 
          function syncQuantity(item) {
            var id = item.getAttribute('data-cart-id');
            var input = item.querySelector('input[type="number"]');
            var qty = parseInt(input.value, 10);
            if (!qty || qty < 1) { return; }
            clearTimeout(timers[id]);
            timers[id] = setTimeout(function () {
              post({ aksi: 'update', id: id, jumlah: qty }).then(done).catch(function () { window.location.reload(); });
            }, 500);
          }
 
          document.addEventListener('click', function (e) {
            var item = e.target.closest('.sb-cart-item');
            if (!item) { return; }
 
            if (e.target.closest('.sb-remove')) {
              e.preventDefault();
              post({ aksi: 'remove', id: item.getAttribute('data-cart-id') }).then(done).catch(function () { window.location.reload(); });
              return;
            }
 
            if (e.target.closest('.sb-input-number-btn')) {
              setTimeout(function () { syncQuantity(item); }, 0);
            }
          });
 
          document.addEventListener('change', function (e) {
            var item = e.target.closest && e.target.closest('.sb-cart-item');
            if (item && e.target.matches('input[type="number"]')) { syncQuantity(item); }
          });
        })();
        </script>