/*
 * Go Kantin admin: melengkapi modal menu dan stok.
 * Dimuat SETELAH admin.js, sehingga mengatur ulang nilai yang diisi admin.js
 * (id produk, kategori, deskripsi, gambar, dan id varian untuk stok).
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

  $(document).on('click', '#btn-tambah-produk', function () {
    $('#form-produk-id').val('');
    $('#form-produk-kategori').prop('selectedIndex', 0);
    $('#form-produk-deskripsi').val('');
    $('#form-produk-gambar').val('');
    setPreview('');
  });

  $(document).on('click', '[data-edit-product]', function () {
    var $b = $(this);
    $('#form-produk-id').val($b.data('id'));
    $('#form-produk-deskripsi').val($b.data('deskripsi') || '');
    $('#form-produk-gambar').val('');
    setPreview($b.data('gambar') || '');
  });

  $(document).on('click', '[data-adjust-stock]', function () {
    $('#form-stok-varian').val($(this).data('varian'));
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
