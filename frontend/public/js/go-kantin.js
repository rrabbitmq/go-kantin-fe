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
    var path = (window.location.pathname.split('/').pop() || 'index.html').toLowerCase();
    if (!path || path === '/') {
      path = 'index.html';
    }
    var map = {
      'index.html': 'index.html',
      'beranda.html': 'index.html',
      'menu.html': 'menu.php',
      'menu.php': 'menu.php',
      'detail-product.html': 'menu.php',
      'detail-product.php': 'menu.php',
      'katalog.html': 'menu.php',
      'katalog.php': 'menu.php',
      'cart.html': 'cart.php',
      'cart.php': 'cart.php',
      'checkout.html': 'cart.php',
      'checkout.php': 'cart.php',
      'status-pesanan.html': 'status-pesanan.php',
      'status-pesanan.php': 'status-pesanan.php',
      'riwayat-pesanan.html': 'status-pesanan.php',
      'riwayat-pesanan.php': 'status-pesanan.php',
      'queue.html': 'index.html',
      'queue.php': 'index.html'
    };
    var target = map[path] || path;
    $('.gk-sidebar-nav a').removeClass('gk-active').each(function () {
      var href = ($(this).attr('href') || '').toLowerCase().split('?')[0].split('#')[0];
      var hrefFile = href.split('/').pop();
      if (hrefFile === target || href.endsWith('/' + target) || href === target) {
        $(this).addClass('gk-active');
      }
    });
  }

  function closeNotifPanel() {
    var $panel = $('#gk-notif-panel');
    if (!$panel.length) return;
    $panel.prop('hidden', true);
    $('#gk-notif-toggle').attr('aria-expanded', 'false');
  }

  $(document).on('click', '.gk-menu-toggle', function (e) {
    e.preventDefault();
    if (isDesktop()) {
      applyCollapsed(!$('.gk-app').hasClass('gk-sidebar-collapsed'));
    } else {
      $('.gk-app').toggleClass('gk-sidebar-open');
    }
  });

  $(document).on('click', '#gk-notif-toggle', function (e) {
    e.preventDefault();
    e.stopPropagation();
    var $panel = $('#gk-notif-panel');
    if (!$panel.length) return;
    var open = $panel.prop('hidden');
    $panel.prop('hidden', !open);
    $(this).attr('aria-expanded', open ? 'true' : 'false');
  });

  $(document).on('click', function (e) {
    if (!$(e.target).closest('.gk-topbar-notif').length) {
      closeNotifPanel();
    }
  });

  $(document).on('keydown', function (e) {
    if (e.key === 'Escape') {
      closeNotifPanel();
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

  function initCheckoutTiming() {
    var $tip = $('#gk-queue-tip');
    var $panel = $('#gk-schedule-panel');
    var $submitLabel = $('#gk-submit-label');
    if (!$('[name="order_timing"]').length) return;

    var threshold = parseInt($tip.attr('data-queue-threshold'), 10) || 30;
    var currentQueue = parseInt($tip.attr('data-current-queue'), 10) || 0;
    var isBusy = currentQueue > threshold;

    $tip.prop('hidden', !isBusy);

    function syncTimingUI() {
      var mode = $('[name="order_timing"]:checked').val();
      var scheduled = mode === 'schedule';
      $panel.prop('hidden', !scheduled);
      if (scheduled) {
        $submitLabel.text('Jadwalkan pesanan');
        if (!$('[name="pickup_slot"]:checked').length) {
          $('[name="pickup_slot"]').not('[data-current]').first().prop('checked', true);
        }
      } else {
        $submitLabel.text('Buat pesanan sekarang');
      }
    }

    $(document).off('change.gkTiming', '[name="order_timing"]').on('change.gkTiming', '[name="order_timing"]', syncTimingUI);
    $(document).off('click.gkTip', '#gk-queue-tip-cta').on('click.gkTip', '#gk-queue-tip-cta', function () {
      $('#gk-timing-schedule').prop('checked', true).trigger('change');
      var $el = $panel.get(0);
      if ($el && $el.scrollIntoView) {
        $el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
      }
    });

    syncTimingUI();
  }

  function initMenuCarousels() {
    if (typeof window.Swiper === 'undefined') {
      return;
    }

    $('.gk-menu-carousel').each(function () {
      var $section = $(this);
      var el = $section.find('.gk-menu-carousel-slider')[0];
      if (!el || el.swiper) {
        return;
      }

      var slideCount = $section.find('.swiper-slide').length;
      var canLoop = slideCount > 4;

      new window.Swiper(el, {
        slidesPerView: 4,
        spaceBetween: 18,
        speed: 800,
        watchOverflow: true,
        loop: canLoop,
        autoplay: canLoop
          ? {
              delay: 3500,
              disableOnInteraction: false,
              pauseOnMouseEnter: true,
            }
          : false,
        navigation: {
          prevEl: $section.find('.gk-menu-carousel-prev')[0],
          nextEl: $section.find('.gk-menu-carousel-next')[0],
        },
        breakpoints: {
          1200: { slidesPerView: 3, spaceBetween: 16 },
          992: { slidesPerView: 2, spaceBetween: 14 },
          768: { slidesPerView: 1.15, spaceBetween: 12 },
        },
      });
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
    initCheckoutTiming();
    initMenuCarousels();
  });

  document.addEventListener('swup:contentReplaced', function () {
    syncActiveNav();
    updateToggleIcon();
    initCheckoutTiming();
    initMenuCarousels();
    closeNotifPanel();
  });
})(jQuery);
