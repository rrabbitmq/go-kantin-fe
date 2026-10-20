/* Go Kantin — sidebar shell */
(function ($) {
  'use strict';

  var STORAGE_KEY = 'gk-sidebar-collapsed';

  function isDesktop() {
    return $(window).width() > 992;
  }

  function closeMobileSidebar() {
    $('.gk-app').removeClass('gk-sidebar-open');
  }

  function applyCollapsed(collapsed) {
    $('.gk-app').toggleClass('gk-sidebar-collapsed', !!collapsed);
    try {
      localStorage.setItem(STORAGE_KEY, collapsed ? '1' : '0');
    } catch (e) {}
    updateToggleIcon();
  }

  function updateToggleIcon() {
    var $btn = $('.gk-menu-toggle');
    var $icon = $btn.find('i');
    if (!$icon.length) return;

    if (isDesktop()) {
      var collapsed = $('.gk-app').hasClass('gk-sidebar-collapsed');
      $icon.attr('class', collapsed ? 'fas fa-angle-double-right' : 'fas fa-angle-double-left');
      $btn.attr('aria-label', collapsed ? 'Perluas sidebar' : 'Ciutkan sidebar');
    } else {
      $icon.attr('class', 'fas fa-bars');
      $btn.attr('aria-label', 'Buka menu');
    }
  }

  function syncActiveNav() {
    var path = (window.location.pathname.split('/').pop() || 'beranda.html').toLowerCase();
    var map = {
      'beranda.html': 'beranda.html',
      'menu.html': 'menu.html',
      'detail-product.html': 'menu.html',
      'cart.html': 'cart.html',
      'checkout.html': 'cart.html',
      'katalog.html': 'menu.html'
    };
    var target = map[path] || path;
    $('.gk-sidebar-nav a').removeClass('gk-active').each(function () {
      var href = ($(this).attr('href') || '').toLowerCase();
      if (href === target) {
        $(this).addClass('gk-active');
      }
    });
  }

  $(document).on('click', '.gk-menu-toggle', function (e) {
    e.preventDefault();
    if (isDesktop()) {
      applyCollapsed(!$('.gk-app').hasClass('gk-sidebar-collapsed'));
    } else {
      $('.gk-app').toggleClass('gk-sidebar-open');
    }
  });

  $(document).on('click', '.gk-sidebar-backdrop', function () {
    closeMobileSidebar();
  });

  $(document).on('click', '.gk-sidebar-nav a', function () {
    if (!isDesktop()) {
      closeMobileSidebar();
    }
  });

  $(window).on('resize', function () {
    if (isDesktop()) {
      closeMobileSidebar();
    }
    updateToggleIcon();
  });

  function syncNavTitles() {
    $('.gk-sidebar-nav a').each(function () {
      var label = $(this).children('span').not('.gk-nav-badge').first().text().trim();
      if (label) {
        $(this).attr('title', label);
      }
    });
  }

  $(function () {
    var saved = false;
    try {
      saved = localStorage.getItem(STORAGE_KEY) === '1';
    } catch (e) {}
    if (saved && isDesktop()) {
      $('.gk-app').addClass('gk-sidebar-collapsed');
    }
    syncActiveNav();
    syncNavTitles();
    updateToggleIcon();
  });

  document.addEventListener('swup:contentReplaced', function () {
    syncActiveNav();
    updateToggleIcon();
  });
})(jQuery);
