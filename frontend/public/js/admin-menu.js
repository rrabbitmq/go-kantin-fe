/*
 * Go Kantin admin: modal menu multi-varian + stok per varian.
 * Dimuat SETELAH admin.js.
 */
(function ($) {
  'use strict';

  function setPreview(url) {
    var $img = $('#form-produk-preview');
    if (url) {
      $img.attr('src', url).show();
    } else {
      $img.attr('src', '').hide();
    }
  }

  function parseVariants(raw) {
    if (!raw) { return []; }
    try {
      var data = typeof raw === 'string' ? JSON.parse(raw) : raw;
      return Array.isArray(data) ? data : [];
    } catch (e) {
      return [];
    }
  }

  function variantRowHtml(variant, index) {
    variant = variant || {};
    return (
      '<div class="gk-admin-varian-row" data-index="' + index + '">' +
        '<input type="hidden" class="js-varian-id" value="' + (variant.id || 0) + '">' +
        '<div class="gk-admin-field">' +
          '<label>Nama varian</label>' +
          '<input type="text" class="gk-admin-input js-varian-nama" maxlength="100" required ' +
            'value="' + $('<div>').text(variant.nama || '').html() + '" placeholder="Contoh: Reguler / Level 3">' +
        '</div>' +
        '<div class="gk-admin-field">' +
          '<label>Harga</label>' +
          '<input type="number" class="gk-admin-input js-varian-harga" min="0" required value="' + (variant.harga != null ? variant.harga : '') + '">' +
        '</div>' +
        '<div class="gk-admin-field">' +
          '<label>Stok</label>' +
          '<input type="number" class="gk-admin-input js-varian-stok" min="0" required value="' + (variant.stok != null ? variant.stok : '') + '">' +
        '</div>' +
        '<button type="button" class="gk-admin-btn gk-admin-btn-ghost gk-admin-btn-sm js-varian-remove" title="Hapus varian">' +
          '<i class="fas fa-trash"></i>' +
        '</button>' +
      '</div>'
    );
  }

  function renderVariants(variants) {
    var list = variants && variants.length ? variants : [{ id: 0, nama: 'Reguler', harga: '', stok: '' }];
    var $wrap = $('#form-produk-varian-list').empty();
    list.forEach(function (v, i) {
      $wrap.append(variantRowHtml(v, i));
    });
    syncRemoveButtons();
  }

  function syncRemoveButtons() {
    var $rows = $('#form-produk-varian-list .gk-admin-varian-row');
    $rows.find('.js-varian-remove').prop('disabled', $rows.length <= 1);
  }

  function collectVariants() {
    var out = [];
    $('#form-produk-varian-list .gk-admin-varian-row').each(function () {
      var $row = $(this);
      out.push({
        id: parseInt($row.find('.js-varian-id').val(), 10) || 0,
        nama: String($row.find('.js-varian-nama').val() || '').trim(),
        harga: parseInt($row.find('.js-varian-harga').val(), 10) || 0,
        stok: parseInt($row.find('.js-varian-stok').val(), 10) || 0,
      });
    });
    return out;
  }

  function fillStockSelect(variants) {
    var $sel = $('#form-stok-varian').empty();
    (variants || []).forEach(function (v) {
      var label = (v.nama || 'Varian') + ' · stok ' + (v.stok != null ? v.stok : 0);
      $sel.append($('<option>', { value: v.id, text: label }));
    });
  }

  $(document).on('click', '#btn-tambah-produk', function () {
    $('#form-produk-id').val('');
    $('#form-produk-kategori').prop('selectedIndex', 0);
    $('#form-produk-status').val('aktif');
    $('#form-produk-deskripsi').val('');
    $('#form-produk-gambar').val('');
    setPreview('');
    renderVariants([{ id: 0, nama: 'Reguler', harga: '', stok: '' }]);
  });

  $(document).on('click', '[data-edit-product]', function () {
    var $b = $(this);
    $('#form-produk-id').val($b.data('id'));
    $('#form-produk-deskripsi').val($b.data('deskripsi') || '');
    $('#form-produk-gambar').val('');
    setPreview($b.data('gambar') || '');
    var kategoriId = $b.attr('data-kategori-id');
    if (kategoriId) {
      $('#form-produk-kategori').val(String(kategoriId));
    }
    $('#form-produk-status').val($b.data('status') || 'aktif');
    renderVariants(parseVariants($b.attr('data-variants')));
  });

  $(document).on('click', '#btn-tambah-varian', function () {
    $('#form-produk-varian-list').append(variantRowHtml({ id: 0, nama: '', harga: '', stok: '' }, Date.now()));
    syncRemoveButtons();
  });

  $(document).on('click', '.js-varian-remove', function () {
    var $rows = $('#form-produk-varian-list .gk-admin-varian-row');
    if ($rows.length <= 1) { return; }
    $(this).closest('.gk-admin-varian-row').remove();
    syncRemoveButtons();
  });

  $(document).on('submit', '#form-produk', function (e) {
    var variants = collectVariants();
    if (!variants.length) {
      e.preventDefault();
      window.alert('Minimal satu varian.');
      return;
    }
    for (var i = 0; i < variants.length; i++) {
      if (!variants[i].nama) {
        e.preventDefault();
        window.alert('Nama varian wajib diisi.');
        return;
      }
    }
    $('#form-produk-varian-json').val(JSON.stringify(variants));
  });

  $(document).on('click', '[data-adjust-stock]', function () {
    var variants = parseVariants($(this).attr('data-variants'));
    fillStockSelect(variants);
    $('#form-stok-keterangan').val('');
  });

  $(document).on('change', '#form-produk-gambar', function () {
    var f = this.files && this.files[0];
    if (!f) { return; }
    if (f.size > 2 * 1024 * 1024) {
      window.alert('Ukuran gambar maksimal 2 MB.');
      this.value = '';
      return;
    }
    setPreview(URL.createObjectURL(f));
  });
})(window.jQuery);
