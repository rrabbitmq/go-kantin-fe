/* Go Kantin — Admin stand UI behaviors */
(function ($, window) {
  'use strict';

  function toast(message, type) {
    var $t = $('#gk-admin-toast');
    if (!$t.length) return;
    $t
      .text(message)
      .removeAttr('hidden')
      .toggleClass('is-success', type === 'success')
      .show();
    clearTimeout(window.__gkToastTimer);
    window.__gkToastTimer = setTimeout(function () {
      $t.attr('hidden', true).hide();
    }, 2600);
  }

  window.gkAdminToast = toast;

  function postJson(url, body) {
    return fetch(url, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      credentials: 'same-origin',
      body: JSON.stringify(body || {})
    }).then(function (res) {
      return res.json().then(function (data) {
        return { ok: !!(data && data.ok), status: res.status, data: data };
      }).catch(function () {
        return { ok: false, status: res.status, data: { message: 'Respons tidak valid.' } };
      });
    });
  }

  function formatRp(n) {
    return 'Rp' + Number(n || 0).toLocaleString('id-ID');
  }

  /* Sidebar mobile */
  $(document).on('click', '[data-admin-toggle]', function () {
    $('#gk-admin-app').toggleClass('is-sidebar-open');
  });

  /* ---------- Antrian ---------- */
  function filterOrders() {
    var status = $('.gk-admin-pills [data-filter].is-active').data('filter') || 'all';
    var slot = $('#gk-antrian-slot').val() || '';
    $('.gk-admin-order-card').each(function () {
      var $card = $(this);
      var okStatus = status === 'all' || $card.data('status') === status;
      var okSlot = !slot || $card.data('slot') === slot;
      $card.toggle(okStatus && okSlot);
    });
  }

  $(document).on('click', '.gk-admin-pills [data-filter]', function () {
    $(this).addClass('is-active').siblings().removeClass('is-active');
    filterOrders();
  });

  $(document).on('change', '#gk-antrian-slot', filterOrders);

  $(document).on('click', '[data-order-action]', function () {
    var action = $(this).data('order-action');
    var $card = $(this).closest('.gk-admin-order-card');
    var queue = $card.data('queue');
    toast('Preview: ' + action + ' untuk ' + queue, 'success');
  });

  /* ---------- POS cart ---------- */
  var cart = {};

  function renderCart() {
    var $list = $('#gk-pos-cart');
    var $empty = $('#gk-pos-empty');
    if (!$list.length) return;

    var keys = Object.keys(cart);
    $list.empty();
    if (!keys.length) {
      $empty.show();
      $('#gk-pos-total').text(formatRp(0));
      $('#gk-pos-change').text(formatRp(0));
      return;
    }
    $empty.hide();

    var total = 0;
    keys.forEach(function (id) {
      var item = cart[id];
      total += item.harga * item.qty;
      $list.append(
        '<li data-id="' + id + '">' +
          '<div><strong>' + item.nama + '</strong><div class="meta">' + formatRp(item.harga) + '</div></div>' +
          '<div class="gk-admin-qty">' +
            '<button type="button" data-qty="-1">−</button>' +
            '<span>' + item.qty + '</span>' +
            '<button type="button" data-qty="1">+</button>' +
          '</div>' +
        '</li>'
      );
    });
    $('#gk-pos-total').text(formatRp(total));
    updateChange(total);
  }

  function updateChange(total) {
    total = typeof total === 'number' ? total : Object.keys(cart).reduce(function (s, id) {
      return s + cart[id].harga * cart[id].qty;
    }, 0);
    var received = parseInt(String($('#gk-uang-diterima').val() || '0').replace(/\D/g, ''), 10) || 0;
    var change = Math.max(0, received - total);
    $('#gk-pos-change').text(formatRp(change));
  }

  $(document).on('click', '[data-add-pos]', function () {
    var $btn = $(this);
    if ($btn.prop('disabled')) return;
    var id = String($btn.data('id'));
    var nama = $btn.data('nama');
    var harga = parseInt($btn.data('harga'), 10) || 0;
    if (!cart[id]) {
      cart[id] = { nama: nama, harga: harga, qty: 0 };
    }
    cart[id].qty += 1;
    renderCart();
    toast(nama + ' ditambah ke nota', 'success');
  });

  $(document).on('click', '#gk-pos-cart [data-qty]', function () {
    var id = String($(this).closest('li').data('id'));
    var delta = parseInt($(this).data('qty'), 10);
    if (!cart[id]) return;
    cart[id].qty += delta;
    if (cart[id].qty <= 0) delete cart[id];
    renderCart();
  });

  $(document).on('input', '#gk-uang-diterima', function () {
    updateChange();
  });

  $(document).on('click', '[data-cash-quick]', function () {
    var val = $(this).data('cash-quick');
    var total = Object.keys(cart).reduce(function (s, id) {
      return s + cart[id].harga * cart[id].qty;
    }, 0);
    if (val === 'pas') {
      $('#gk-uang-diterima').val(total);
    } else {
      $('#gk-uang-diterima').val(val);
    }
    updateChange(total);
  });

  $(document).on('click', '.gk-admin-pay-toggle [data-pay]', function () {
    $(this).addClass('is-active').siblings().removeClass('is-active');
    var pay = $(this).data('pay');
    $('#gk-cash-fields').toggle(pay === 'tunai');
  });

  $(document).on('click', '[data-pos-category]', function () {
    var cat = $(this).data('pos-category');
    $(this).addClass('is-active').siblings().removeClass('is-active');
    $('.gk-admin-product-card').each(function () {
      var $card = $(this);
      var match = cat === 'Semua' || $card.data('kategori') === cat;
      var q = ($('#gk-pos-search').val() || '').toLowerCase();
      var nameOk = !q || String($card.data('nama')).toLowerCase().indexOf(q) !== -1;
      $card.toggle(match && nameOk);
    });
  });

  $(document).on('input', '#gk-pos-search', function () {
    $('.gk-admin-pills [data-pos-category].is-active').trigger('click');
  });

  var qrTicketId = null;

  $(document).on('click', '#gk-pos-save', function () {
    var keys = Object.keys(cart);
    if (!keys.length) {
      toast('Nota masih kosong');
      return;
    }
    var metode = $('.gk-admin-pay-toggle [data-pay].is-active').data('pay') || 'tunai';
    var items = keys.map(function (id) {
      return { id_varian: parseInt(id, 10), qty: cart[id].qty };
    });
    var $btn = $(this).prop('disabled', true);
    postJson('/admin/pos.php', { aksi: 'jual', metode: metode, items: items })
      .then(function (res) {
        if (!res.ok) {
          toast((res.data && res.data.message) || 'Transaksi POS gagal');
          return;
        }
        var kode = res.data && res.data.data && res.data.data.kode;
        toast(kode ? ('Transaksi ' + kode + ' disimpan') : 'Transaksi POS disimpan', 'success');
        cart = {};
        renderCart();
        $('#gk-uang-diterima').val('');
        setTimeout(function () { window.location.reload(); }, 900);
      })
      .catch(function () { toast('Gagal menghubungi server'); })
      .finally(function () { $btn.prop('disabled', false); });
  });

  $(document).on('click', '#gk-scan-qr', function () {
    var code = ($('#gk-qr-input').val() || '').trim();
    if (!code) {
      toast('Masukkan nomor tiket / kode QR');
      return;
    }
    qrTicketId = null;
    $('#gk-qr-heading').text('Memuat tiket…');
    $('#gk-qr-meta').text(code);
    $('#gk-qr-items').empty();
    $('#gk-qr-handover').attr('hidden', true);
    $('#gk-qr-modal').modal('show');

    postJson('/admin/pos.php', { aksi: 'tiket', kode: code })
      .then(function (res) {
        if (!res.ok || !res.data || !res.data.data) {
          $('#gk-qr-heading').text('Tiket tidak ditemukan');
          $('#gk-qr-meta').text((res.data && res.data.message) || 'Periksa kode lalu coba lagi');
          return;
        }
        var t = res.data.data;
        qrTicketId = t.id;
        $('#gk-qr-heading').text(t.bisa_selesai ? 'Tiket QR Valid' : 'Tiket: ' + t.status);
        $('#gk-qr-meta').text(t.kode + ' · ' + t.pembeli + ' · ' + String(t.metode || '').toUpperCase());
        var html = (t.items || []).map(function (it) {
          return '<div>' + it.jumlah + 'x ' + it.nama + ' — ' + formatRp(it.sub_total) + '</div>';
        }).join('');
        html += '<div style="margin-top:8px;font-weight:700;">Total Transaksi: ' + formatRp(t.total) + '</div>';
        $('#gk-qr-items').html(html);
        if (t.bisa_selesai) {
          $('#gk-qr-handover').removeAttr('hidden');
        }
      })
      .catch(function () {
        $('#gk-qr-heading').text('Gagal memuat tiket');
        $('#gk-qr-meta').text('Server tidak merespons');
      });
  });

  $(document).on('click', '#gk-qr-handover', function () {
    if (!qrTicketId) return;
    var $btn = $(this).prop('disabled', true);
    postJson('/admin/pos.php', { aksi: 'selesai', id: qrTicketId })
      .then(function (res) {
        if (!res.ok) {
          toast((res.data && res.data.message) || 'Gagal menyelesaikan pesanan');
          return;
        }
        $('#gk-qr-modal').modal('hide');
        toast((res.data && res.data.message) || 'Pesanan selesai', 'success');
        $('#gk-qr-input').val('');
        qrTicketId = null;
      })
      .catch(function () { toast('Gagal menghubungi server'); })
      .finally(function () { $btn.prop('disabled', false); });
  });

  /* ---------- Menu / Kasir / Laporan modals ---------- */
  $(document).on('click', '[data-preview-save]', function () {
    var msg = $(this).data('preview-save') || 'Perubahan disimpan';
    $('.modal').modal('hide');
    toast(msg, 'success');
  });

  $(document).on('click', '[data-edit-product]', function () {
    var $btn = $(this);
    $('#modal-produk-title').text('Edit Menu');
    $('#form-produk-nama').val($btn.data('nama'));
    $('#form-produk-kategori').val($btn.data('kategori'));
    $('#form-produk-harga').val($btn.data('harga'));
    $('#form-produk-stok').val($btn.data('stok'));
    $('#form-produk-status').val($btn.data('status'));
    $('#modal-produk').modal('show');
  });

  $(document).on('click', '#btn-tambah-produk', function () {
    $('#modal-produk-title').text('Tambah Menu');
    $('#form-produk-nama, #form-produk-harga, #form-produk-stok').val('');
    $('#form-produk-kategori').val('Makanan');
    $('#form-produk-status').val('aktif');
    $('#modal-produk').modal('show');
  });

  $(document).on('click', '[data-adjust-stock]', function () {
    $('#form-stok-nama').val($(this).data('nama'));
    $('#form-stok-jumlah').val(1);
    $('#form-stok-jenis').val('masuk');
    $('#modal-stok').modal('show');
  });

  $(document).on('change', '#filter-produk-kategori, #filter-produk-status', function () {
    var table = $('#dt-produk').DataTable();
    var kat = $('#filter-produk-kategori').val();
    var st = $('#filter-produk-status').val();
    table.column(2).search(kat === 'all' ? '' : '^' + kat + '$', true, false);
    table.column(6).search(st === 'all' ? '' : st, true, false);
    table.draw();
  });

  $(document).on('change', '#filter-laporan-status, #filter-laporan-sumber', function () {
    var table = $('#dt-laporan').DataTable();
    var st = $('#filter-laporan-status').val();
    var sumber = $('#filter-laporan-sumber').val();
    table.column(7).search(st === 'all' ? '' : st, true, false);
    table.column(4).search(sumber === 'all' ? '' : sumber, true, false);
    table.draw();
  });

  /* ---------- Superadmin: pengguna & hak akses ---------- */
  function openUserModal(data) {
    var isEdit = Number(data.id) > 0;
    var roles = String(data.roles || '').split(',').filter(Boolean);
    $('#modal-pengguna-title').text(isEdit ? 'Edit Pengguna' : 'Tambah Pengguna');
    $('#form-pengguna-id').val(data.id || 0);
    $('#form-pengguna-username').val(data.username || '');
    $('#form-pengguna-email').val(data.email || '');
    $('#form-pengguna-password').val('').prop('required', !isEdit);
    $('#form-pengguna-password-hint').text(isEdit ? 'Kosongkan jika tidak diubah.' : 'Minimal 8 karakter.');
    $('#form-pengguna-status').val(data.status || 'aktif');
    $('[data-role-checkbox]').each(function () {
      $(this).prop('checked', roles.indexOf(String(this.value)) !== -1);
    });
    $('#modal-pengguna').modal('show');
  }

  $(document).on('click', '#btn-tambah-pengguna', function () {
    openUserModal({ id: 0, status: 'aktif' });
  });

  $(document).on('click', '[data-edit-user]', function () {
    openUserModal($(this).data());
  });

  $(document).on('submit', '#form-pengguna', function (e) {
    if (!$('[data-role-checkbox]:checked').length) {
      e.preventDefault();
      toast('Pilih minimal satu role');
    }
  });

  $(document).on('submit', 'form[data-confirm]', function (e) {
    if (!window.confirm($(this).data('confirm'))) {
      e.preventDefault();
    }
  });

  $(document).on('change', '#filter-pengguna-role, #filter-pengguna-status', function () {
    var table = $('#dt-pengguna').DataTable();
    var role = $('#filter-pengguna-role').val();
    var st = $('#filter-pengguna-status').val();
    table.column(3).search(role === 'all' ? '' : '(^|\\s)' + role + '(\\s|$)', true, false);
    table.column(4).search(st === 'all' ? '' : '^' + st + '$', true, false);
    table.draw();
  });

  $(function () {
    renderCart();
    if ($('#dt-produk').length) {
      window.gkDataTable('#dt-produk', { order: [[1, 'asc']] });
    }
    if ($('#dt-mutasi').length) {
      window.gkDataTable('#dt-mutasi');
    }
    if ($('#dt-laporan').length) {
      window.gkDataTable('#dt-laporan');
    }
    if ($('#dt-pengguna').length) {
      window.gkDataTable('#dt-pengguna', {
        order: [[0, 'asc']],
        columnDefs: [{ orderable: false, targets: -1 }]
      });
    }
    var $reopen = $('#modal-pengguna[data-reopen]');
    if ($reopen.length) {
      openUserModal($reopen.data());
    }
  });
})(jQuery, window);
