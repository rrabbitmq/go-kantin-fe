<?php
$pageTitle = 'Checkout - Go Kantin';
$activeNav = 'cart';
require __DIR__ . '/../includes/layout-start.php';

$cartItems = gk_get_cart_items($demoUserId);
$slots = gk_get_queue_slots();
$currentSlot = gk_get_current_queue_slot();
$queueThreshold = 30;
$currentQueue = $currentSlot ? (int) $currentSlot['queue_count'] : 0;

$subtotal = 0;
foreach ($cartItems as $item) {
    $subtotal += (float) $item['line_total'];
}
$shipping = $cartItems ? 5000 : 0;
$total = $subtotal + $shipping;
?>
        <section class="sb-p-90-90">
          <div class="container">
            <div class="row">
              <div class="col-12 col-lg-8">
                <form class="sb-checkout-form" id="gk-checkout-form">
                  <div class="gk-queue-tip" id="gk-queue-tip" hidden
                       data-queue-threshold="<?= $queueThreshold ?>"
                       data-current-queue="<?= $currentQueue ?>">
                    <div class="gk-queue-tip-icon"><i class="fas fa-exclamation-circle"></i></div>
                    <div class="gk-queue-tip-body">
                      <strong>Antrian kantin lagi rame nih</strong>
                      <p>Sekarang ada <span data-gk-queue-count><?= $currentQueue ?></span> orang di antrian
                        (slot <span data-gk-queue-slot><?= gk_h($currentSlot['slot_label'] ?? '-') ?></span>). Mau jadwalkan aja?</p>
                    </div>
                    <button type="button" class="gk-btn-primary gk-btn-inline" id="gk-queue-tip-cta">Jadwalkan</button>
                  </div>

                  <div class="sb-mb-30">
                    <h3 class="sb-mb-30">Waktu pengambilan</h3>
                    <div class="gk-order-timing">
                      <label class="gk-timing-card">
                        <input type="radio" name="order_timing" value="now" checked>
                        <span class="gk-timing-card-inner">
                          <i class="fas fa-bolt"></i>
                          <span class="gk-timing-title">Buat pesanan untuk sekarang</span>
                          <span class="gk-timing-desc">Ambil di slot antrian saat ini</span>
                        </span>
                      </label>
                      <label class="gk-timing-card">
                        <input type="radio" name="order_timing" value="schedule" id="gk-timing-schedule">
                        <span class="gk-timing-card-inner">
                          <i class="far fa-calendar-alt"></i>
                          <span class="gk-timing-title">Jadwalkan pesanan</span>
                          <span class="gk-timing-desc">Pilih jam pengambilan biar lebih tenang</span>
                        </span>
                      </label>
                    </div>

                    <div class="gk-schedule-panel" id="gk-schedule-panel" hidden>
                      <p class="gk-schedule-hint">Pilih slot pengambilan (30 menit). Status mengikuti antrian di beranda.</p>
                      <div class="gk-slot-grid" role="radiogroup" aria-label="Slot pengambilan">
                        <?php foreach ($slots as $slot): ?>
                        <?php
                          $count = (int) $slot['queue_count'];
                          $label = gk_queue_label($count, $queueThreshold);
                          $busyClass = $count > $queueThreshold ? ' gk-slot-busy' : '';
                        ?>
                        <label class="gk-slot<?= $busyClass ?>">
                          <input type="radio"
                                 name="pickup_slot"
                                 value="<?= gk_h($slot['slot_value']) ?>"
                                 data-queue="<?= $count ?>"
                                 data-slot-id="<?= (int) $slot['id'] ?>"
                                 <?= !empty($slot['is_current']) ? 'data-current="true"' : '' ?>>
                          <span class="gk-slot-inner">
                            <span class="gk-slot-time"><?= gk_h($slot['slot_label']) ?></span>
                            <span class="gk-slot-meta"><span class="gk-slot-count"><?= $count ?></span> antrian · <?= gk_h($label) ?></span>
                          </span>
                        </label>
                        <?php endforeach; ?>
                      </div>
                    </div>
                  </div>

                  <div class="sb-mb-30">
                    <h3>Informasi tambahan</h3>
                  </div>
                  <div class="sb-group-input">
                    <textarea name="note"></textarea>
                    <span class="sb-bar"></span>
                    <label>Catatan pesanan</label>
                  </div>
                  <div class="sb-mb-30">
                    <h3 class="sb-mb-30">Metode pembayaran</h3>
                    <ul>
                      <li class="sb-radio">
                        <input type="radio" id="option-1" name="selector" checked>
                        <label for="option-1">Qris & Transfer</label>
                        <div class="sb-check"></div>
                      </li>
                      <li class="sb-radio">
                        <input type="radio" id="option-3" name="selector">
                        <label for="option-3">Bayar di tempat</label>
                        <div class="sb-check"></div>
                      </li>
                    </ul>
                  </div>
                  <button type="submit" class="sb-btn sb-m-0">
                    <span class="sb-icon">
                      <img src="/img/ui/icons/arrow.svg" alt="icon">
                    </span>
                    <span id="gk-submit-label">Buat pesanan sekarang</span>
                  </button>
                </form>
              </div>

              <div class="col-12 col-lg-4">
                <div class="sb-pad-type-2">
                  <div class="sb-co-cart-frame">
                    <div class="sb-cart-table">
                      <div class="sb-cart-table-header d-none d-lg-block">
                        <div class="row">
                          <div class="col-8">Produk</div>
                          <div class="col-4 text-right">Total</div>
                        </div>
                      </div>

                      <?php foreach ($cartItems as $item): ?>
                      <div class="sb-cart-item">
                        <div class="row align-items-center">
                          <div class="col gk-co-product">
                            <a class="sb-product" href="/user/detail-product.php?id=<?= (int) $item['product_id'] ?>">
                              <div class="sb-cover-frame">
                                <img src="<?= gk_h($item['image_path']) ?>" alt="<?= gk_h($item['name']) ?>">
                              </div>
                              <div class="sb-prod-description">
                                <h4 class="sb-mb-10"><?= gk_h($item['name']) ?></h4>
                                <p class="sb-text sb-text-sm">x<?= (int) $item['quantity'] ?></p>
                              </div>
                            </a>
                          </div>
                          <div class="col-auto gk-co-price">
                            <div class="sb-price-2"><?= gk_h(gk_format_rupiah($item['line_total'], true)) ?></div>
                          </div>
                        </div>
                      </div>
                      <?php endforeach; ?>

                      <div class="sb-cart-total sb-cart-total-2">
                        <div class="sb-sum">
                          <div class="row">
                            <div class="col-6">
                              <div class="sb-total-title">Subtotal:</div>
                            </div>
                            <div class="col-6">
                              <div class="sb-price-1 text-right"><?= gk_h(gk_format_rupiah($subtotal, true)) ?></div>
                            </div>
                          </div>
                        </div>
                        <div class="sb-realy-sum">
                          <div class="row">
                            <div class="col-6">
                              <div class="sb-total-title">Total:</div>
                            </div>
                            <div class="col-6">
                              <div class="sb-price-2 text-right"><?= gk_h(gk_format_rupiah($total, true)) ?></div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>
<?php require __DIR__ . '/../includes/layout-end.php'; ?>
