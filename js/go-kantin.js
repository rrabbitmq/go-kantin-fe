/* Go Kantin — sidebar shell */
(function ($) {
  'use strict';

  function closeSidebar() {
    $('.gk-app').removeClass('gk-sidebar-open');
  }

  function toggleSidebar() {
    $('.gk-app').toggleClass('gk-sidebar-open');
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
    toggleSidebar();
  });

  $(document).on('click', '.gk-sidebar-backdrop', function () {
    closeSidebar();
  });

  $(document).on('click', '.gk-sidebar-nav a', function () {
    if ($(window).width() <= 992) {
      closeSidebar();
    }
  });

  $(window).on('resize', function () {
    if ($(window).width() > 992) {
      closeSidebar();
    }
  });

  $(function () {
    syncActiveNav();
  });

  document.addEventListener('swup:contentReplaced', syncActiveNav);
})(jQuery);
