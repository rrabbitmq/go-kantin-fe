/*
 * Go Kantin - aksi keranjang: tambah, ubah jumlah, hapus, modal varian.
 * Memakai event delegation di document supaya tetap bekerja
 * setelah halaman berpindah lewat Swup (tanpa reload penuh).
 */
(function () {
  'use strict';

  var ENDPOINT = '/user/cart-action.php';
  var timers = {};
  var modalState = {
    productId: 0,
    name: '',
    image: '',
    basePrice: 0,
    variants: [],
    selectedId: 0,
    qty: 1,
  };

  function post(data) {
    return fetch(ENDPOINT, {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'X-Requested-With': 'fetch' },
      body: new URLSearchParams(data)
    }).then(function (r) {
      return r.json().then(function (j) {
        j.status = r.status;
        return j;
      }, function () {
        return { ok: false, status: r.status, message: 'Respons server tidak valid.' };
      });
    }).catch(function () {
      return { ok: false, status: 0, message: 'Tidak dapat menghubungi server.' };
    });
  }

  function handleFail(res) {
    if (res.status === 401) {
      window.location.href = '/auth/login.php';
      return;
    }
    window.alert(res.message || 'Terjadi kesalahan. Coba lagi.');
  }

  function reloadAfter(res) {
    if (!res.ok) { handleFail(res); return; }
    window.location.reload();
  }

  function productIdFromCard(el) {
    var link = el.closest('a[href*="detail-product.php"]');
    if (!link) { return null; }
    return new URL(link.getAttribute('href'), window.location.origin).searchParams.get('id');
  }

  function flashLabel(btn, text) {
    var label = btn.querySelector('span');
    if (!label) { return; }
    var old = label.textContent;
    label.textContent = text;
    setTimeout(function () { label.textContent = old; }, 1500);
  }

  function syncQuantity(item) {
    var id = item.getAttribute('data-cart-id');
    var input = item.querySelector('input[type="number"]');
    var qty = parseInt(input.value, 10);
    if (!qty || qty < 1) { return; }
    clearTimeout(timers[id]);
    timers[id] = setTimeout(function () {
      post({ aksi: 'update', id: id, jumlah: qty }).then(reloadAfter);
    }, 500);
  }

  function formatRupiah(amount) {
    var n = Math.round(Number(amount) || 0);
    return 'Rp' + n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
  }

  function selectedVariant() {
    for (var i = 0; i < modalState.variants.length; i++) {
      if (modalState.variants[i].id === modalState.selectedId) {
        return modalState.variants[i];
      }
    }
    return null;
  }

  function ensureModalMounted() {
    return document.getElementById('gk-variant-modal');
  }

  function openVariantModal(product) {
    var modal = ensureModalMounted();
    if (!modal || !product) { return; }

    var variants = Array.isArray(product.variants) ? product.variants.slice() : [];
    if (!variants.length) {
      variants = [{
        id: 0,
        name: 'Reguler',
        price: Number(product.price) || 0,
        stock: 0,
      }];
    }

    modalState.productId = Number(product.id) || 0;
    modalState.name = product.name || 'Menu';
    modalState.image = product.image || '';
    modalState.basePrice = Number(product.price) || 0;
    modalState.variants = variants;
    modalState.qty = 1;

    var firstAvailable = null;
    for (var i = 0; i < variants.length; i++) {
      if ((variants[i].stock || 0) > 0) {
        firstAvailable = variants[i];
        break;
      }
    }
    modalState.selectedId = firstAvailable ? firstAvailable.id : 0;

    var thumb = document.getElementById('gk-variant-thumb');
    var title = document.getElementById('gk-variant-modal-title');
    var basePrice = document.getElementById('gk-variant-base-price');
    if (thumb) {
      thumb.src = modalState.image;
      thumb.alt = modalState.name;
    }
    if (title) {
      title.textContent = 'Pilih Varian • ' + modalState.name;
    }
    if (basePrice) {
      basePrice.textContent = formatRupiah(modalState.basePrice);
    }

    renderVariantList();
    syncQtyUi();
    syncSubmitUi();

    modal.hidden = false;
    modal.setAttribute('aria-hidden', 'false');
    document.body.classList.add('gk-variant-modal-open');
  }

  function closeVariantModal() {
    var modal = ensureModalMounted();
    if (!modal) { return; }
    modal.hidden = true;
    modal.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('gk-variant-modal-open');
  }

  function renderVariantList() {
    var list = document.getElementById('gk-variant-list');
    if (!list) { return; }

    list.innerHTML = '';
    modalState.variants.forEach(function (variant) {
      var out = (variant.stock || 0) <= 0;
      var selected = !out && variant.id === modalState.selectedId;
      var btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'gk-variant-option'
        + (selected ? ' is-selected' : '')
        + (out ? ' is-disabled' : '');
      btn.disabled = out;
      btn.setAttribute('data-variant-id', String(variant.id));

      var mark = document.createElement('span');
      mark.className = 'gk-variant-mark';
      mark.innerHTML = selected ? '<i class="fas fa-check"></i>' : '';

      var name = document.createElement('span');
      name.className = 'gk-variant-name';
      name.textContent = variant.name;

      var stock = document.createElement('span');
      stock.className = 'gk-variant-stock' + (out ? ' is-empty' : '');
      stock.innerHTML = out
        ? '<i class="fas fa-box"></i> Stok habis'
        : '<i class="fas fa-box"></i> Stok: ' + variant.stock;

      var price = document.createElement('span');
      price.className = 'gk-variant-price';
      price.textContent = formatRupiah(variant.price);

      btn.appendChild(mark);
      btn.appendChild(name);
      btn.appendChild(stock);
      btn.appendChild(price);
      list.appendChild(btn);
    });
  }

  function syncQtyUi() {
    var variant = selectedVariant();
    var max = variant && variant.stock > 0 ? variant.stock : 1;
    if (modalState.qty > max) { modalState.qty = max; }
    if (modalState.qty < 1) { modalState.qty = 1; }

    var input = document.getElementById('gk-variant-qty-input');
    var label = document.getElementById('gk-variant-qty-label');
    var minus = document.getElementById('gk-variant-qty-minus');
    var plus = document.getElementById('gk-variant-qty-plus');

    if (input) {
      input.value = String(modalState.qty);
      input.max = String(max);
    }
    if (label) {
      label.textContent = variant && variant.stock > 0
        ? 'Jumlah (Maks. ' + variant.stock + ')'
        : 'Jumlah';
    }
    if (minus) { minus.disabled = !variant || modalState.qty <= 1; }
    if (plus) { plus.disabled = !variant || modalState.qty >= max || variant.stock <= 0; }
  }

  function syncSubmitUi() {
    var variant = selectedVariant();
    var submit = document.getElementById('gk-variant-submit');
    var label = document.getElementById('gk-variant-submit-label');
    var ok = !!(variant && variant.stock > 0 && variant.id > 0);
    if (submit) { submit.disabled = !ok; }
    if (label) {
      if (ok) {
        label.textContent = 'Tambahkan ke Keranjang • '
          + formatRupiah(variant.price * modalState.qty);
      } else {
        label.textContent = 'Tambahkan ke Keranjang';
      }
    }
  }

  function updateNavBadge(jumlah) {
    if (jumlah == null) { return; }
    var link = document.querySelector('.gk-sidebar-nav a[href="/user/cart.php"]');
    if (!link) { return; }
    var badge = link.querySelector('.gk-nav-badge');
    if (jumlah > 0) {
      if (!badge) {
        badge = document.createElement('span');
        badge.className = 'gk-nav-badge';
        link.appendChild(badge);
      }
      badge.textContent = String(jumlah);
    } else if (badge) {
      badge.remove();
    }
  }

  document.addEventListener('click', function (e) {
    var openBtn = e.target.closest('[data-open-variant-modal]');
    if (openBtn) {
      e.preventDefault();
      e.stopPropagation();
      if (openBtn.disabled) { return; }
      var raw = openBtn.getAttribute('data-product') || '{}';
      try {
        openVariantModal(JSON.parse(raw));
      } catch (err) {
        window.alert('Data produk tidak valid.');
      }
      return;
    }

    if (e.target.closest('[data-variant-close]')) {
      e.preventDefault();
      closeVariantModal();
      return;
    }

    var option = e.target.closest('.gk-variant-option');
    if (option && !option.disabled) {
      e.preventDefault();
      modalState.selectedId = parseInt(option.getAttribute('data-variant-id'), 10) || 0;
      modalState.qty = 1;
      renderVariantList();
      syncQtyUi();
      syncSubmitUi();
      return;
    }

    if (e.target.closest('#gk-variant-qty-minus')) {
      e.preventDefault();
      modalState.qty = Math.max(1, modalState.qty - 1);
      syncQtyUi();
      syncSubmitUi();
      return;
    }

    if (e.target.closest('#gk-variant-qty-plus')) {
      e.preventDefault();
      var current = selectedVariant();
      var max = current && current.stock > 0 ? current.stock : 1;
      modalState.qty = Math.min(max, modalState.qty + 1);
      syncQtyUi();
      syncSubmitUi();
      return;
    }

    if (e.target.closest('#gk-variant-submit')) {
      e.preventDefault();
      var chosen = selectedVariant();
      if (!chosen || chosen.stock <= 0 || chosen.id < 1) { return; }
      var submitBtn = document.getElementById('gk-variant-submit');
      if (submitBtn) { submitBtn.disabled = true; }
      post({
        aksi: 'add',
        id: String(modalState.productId || 0),
        id_varian_produk: String(chosen.id),
        jumlah: String(modalState.qty),
      }).then(function (res) {
        if (!res.ok) {
          if (submitBtn) { submitBtn.disabled = false; }
          handleFail(res);
          return;
        }
        updateNavBadge(res.jumlah);
        closeVariantModal();
        window.location.href = '/user/cart.php';
      });
      return;
    }

    // Tombol "Tambah" pada kartu produk → ke detail (biar pilih varian)
    var addBtn = e.target.closest('.gk-add-btn');
    if (addBtn) {
      e.preventDefault();
      e.stopPropagation();
      var cardId = productIdFromCard(addBtn);
      if (!cardId) { return; }
      window.location.href = '/user/detail-product.php?id=' + encodeURIComponent(cardId);
      return;
    }

    var item = e.target.closest('.sb-cart-item[data-cart-id]');
    if (!item) { return; }

    if (e.target.closest('.sb-remove')) {
      e.preventDefault();
      post({ aksi: 'remove', id: item.getAttribute('data-cart-id') }).then(reloadAfter);
      return;
    }

    if (e.target.closest('.sb-input-number-btn')) {
      setTimeout(function () { syncQuantity(item); }, 0);
    }
  }, true);

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') { closeVariantModal(); }
  });

  document.addEventListener('change', function (e) {
    var item = e.target.closest && e.target.closest('.sb-cart-item[data-cart-id]');
    if (item && e.target.matches('input[type="number"]')) { syncQuantity(item); }
  });

  document.addEventListener('swup:contentReplaced', function () {
    closeVariantModal();
  });
})();
