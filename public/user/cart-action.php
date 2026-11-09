<?php
 
require __DIR__ . '/../../app/bootstrap.php';
 
use App\Controllers\CartController;
 
(new CartController())->action();


/*
 * Go Kantin - aksi keranjang: tambah, ubah jumlah, hapus.
 * Memakai event delegation di document supaya tetap bekerja
 * setelah halaman berpindah lewat Swup (tanpa reload penuh).
 */
(function () {
  'use strict';
 
  var ENDPOINT = '/user/cart-action.php';
  var timers = {};
 
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
    if (!res.ok) { handleFail(res); }
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
 
  document.addEventListener('click', function (e) {
    // 1. Tombol "Tambah" pada kartu produk (katalog, menu, menu lainnya)
    var addBtn = e.target.closest('.gk-add-btn');
    if (addBtn) {
      e.preventDefault();
      e.stopPropagation();
      var cardId = productIdFromCard(addBtn);
      if (!cardId) { return; }
      post({ aksi: 'add', id: cardId, jumlah: 1 }).then(function (res) {
        if (res.ok) { flashLabel(addBtn, 'Ditambahkan'); } else { handleFail(res); }
      });
      return;
    }
 
    // 2. Tombol "Tambahkan" pada halaman detail produk
    var atc = e.target.closest('.sb-atc');
    if (atc) {
      e.preventDefault();
      e.stopPropagation();
      var pid = new URLSearchParams(window.location.search).get('id');
      var qtyInput = document.querySelector('.sb-buttons-frame input[type="number"]');
      var qty = qtyInput ? (parseInt(qtyInput.value, 10) || 1) : 1;
      if (!pid) { return; }
      post({ aksi: 'add', id: pid, jumlah: qty }).then(function (res) {
        if (res.ok) { window.location.href = '/user/cart.php'; } else { handleFail(res); }
      });
      return;
    }
 
    // 3. Halaman keranjang: hapus item
    var item = e.target.closest('.sb-cart-item[data-cart-id]');
    if (!item) { return; }
 
    if (e.target.closest('.sb-remove')) {
      e.preventDefault();
      post({ aksi: 'remove', id: item.getAttribute('data-cart-id') }).then(reloadAfter);
      return;
    }
 
    // 4. Halaman keranjang: tombol + / - (nilai input diubah dulu oleh template)
    if (e.target.closest('.sb-input-number-btn')) {
      setTimeout(function () { syncQuantity(item); }, 0);
    }
  }, true);
 
  // Mengetik jumlah langsung di input keranjang
  document.addEventListener('change', function (e) {
    var item = e.target.closest && e.target.closest('.sb-cart-item[data-cart-id]');
    if (item && e.target.matches('input[type="number"]')) { syncQuantity(item); }
  });
})();