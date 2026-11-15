/* Go Kantin — DataTables helper (client-side) */
(function (window, $) {
  'use strict';

  window.gkDataTable = function (selector, extra) {
    var $el = $(selector);
    if (!$el.length || !$.fn.DataTable) {
      return null;
    }

    if ($.fn.DataTable.isDataTable($el)) {
      return $el.DataTable();
    }

    return $el.DataTable($.extend(true, {
      pageLength: 10,
      order: [[0, 'desc']],
      responsive: true,
      autoWidth: false,
      language: {
        url: '/js/plugins/datatables-id.json'
      }
    }, extra || {}));
  };
})(window, jQuery);
