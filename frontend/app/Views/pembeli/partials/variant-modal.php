<div class="gk-variant-modal" id="gk-variant-modal" hidden aria-hidden="true">
  <div class="gk-variant-modal-backdrop" data-variant-close></div>
  <div class="gk-variant-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="gk-variant-modal-title">
    <button type="button" class="gk-variant-modal-close" data-variant-close aria-label="Tutup">
      <i class="fas fa-times"></i>
    </button>
    <div class="gk-variant-modal-head">
      <img id="gk-variant-thumb" src="" alt="">
      <div>
        <h3 id="gk-variant-modal-title">Pilih Varian</h3>
        <p id="gk-variant-base-price" class="gk-variant-base-price"></p>
      </div>
    </div>
    <div class="gk-variant-list" id="gk-variant-list"></div>
    <div class="gk-variant-modal-foot">
      <div class="gk-variant-qty-row">
        <span id="gk-variant-qty-label">Jumlah</span>
        <div class="gk-variant-qty">
          <button type="button" class="gk-variant-qty-btn" id="gk-variant-qty-minus" aria-label="Kurangi">−</button>
          <input type="number" id="gk-variant-qty-input" value="1" min="1" max="1" readonly>
          <button type="button" class="gk-variant-qty-btn is-accent" id="gk-variant-qty-plus" aria-label="Tambah">+</button>
        </div>
      </div>
      <button type="button" class="gk-variant-submit" id="gk-variant-submit" disabled>
        <i class="fas fa-shopping-bag"></i>
        <span id="gk-variant-submit-label">Tambahkan ke Keranjang</span>
      </button>
    </div>
  </div>
</div>
