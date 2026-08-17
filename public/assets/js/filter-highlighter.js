/**
 * Highlight filter controls that currently hold a value.
 *
 * Ported from vlsm's Utilities.initFilterHighlighter (public/assets/js/utils.js).
 * Same class name and styling, so a filter panel looks the same in both apps.
 * Standalone rather than a method on a utilities class, because odkdash has no
 * equivalent, and carrying one over for a single helper is not worth it.
 *
 *   initFilterHighlighter('#filterPanel');
 *
 * Returns { refresh, destroy }. Call refresh() after changing a control from
 * script — a programmatic .val() fires no event, so nothing else would notice.
 */
(function (window, $) {
  'use strict';

  function debounce(fn, wait) {
    var timer = null;
    return function () {
      var context = this;
      var args = arguments;
      clearTimeout(timer);
      timer = setTimeout(function () {
        fn.apply(context, args);
      }, wait);
    };
  }

  function initFilterHighlighter(containerSelector, options) {
    var settings = $.extend({
      highlightClass: 'filter-filled',
      checkOnLoad: true,
      debounceDelay: 100
    }, options || {});

    function highlightFilledFilters() {
      var $container = $(containerSelector);
      if ($container.length === 0) {
        return;
      }

      $container.find('input[type="text"], input[type="date"]').each(function () {
        var val = $(this).val();
        $(this).toggleClass(settings.highlightClass, !!val && $.trim(val) !== '');
      });

      $container.find('select').each(function () {
        var val = $(this).val();
        /* "All" is an empty-valued option, and a multi-select reports it as [""]
           rather than [], so an empty string counts as unset either way. */
        var filled = !(val === null || val === '' ||
          ($.isArray(val) && $.grep(val, function (v) { return v !== '' && v !== null; }).length === 0));

        $(this).toggleClass(settings.highlightClass, filled);

        // select2 hides the real control, so the visible box needs the class too.
        if ($(this).hasClass('select2-hidden-accessible')) {
          $(this).next('.select2-container')
            .find('.select2-selection')
            .toggleClass(settings.highlightClass, filled);
        }
      });

      $container.find('input[type="checkbox"]').each(function () {
        $(this).parent().toggleClass(settings.highlightClass, $(this).is(':checked'));
      });
    }

    var debounced = debounce(highlightFilledFilters, settings.debounceDelay);

    if (settings.checkOnLoad) {
      highlightFilledFilters();
    }

    $(containerSelector).on('change keyup', 'input, select', debounced);
    $(containerSelector).on('select2:select select2:unselect select2:clear', 'select', highlightFilledFilters);
    $(containerSelector).on('apply.daterangepicker cancel.daterangepicker', 'input', highlightFilledFilters);

    return {
      refresh: highlightFilledFilters,
      destroy: function () {
        $(containerSelector).off('change keyup', 'input, select');
        $(containerSelector).off('select2:select select2:unselect select2:clear');
        $(containerSelector).off('apply.daterangepicker cancel.daterangepicker');
        $(containerSelector).find('.' + settings.highlightClass).removeClass(settings.highlightClass);
      }
    };
  }

  window.initFilterHighlighter = initFilterHighlighter;
})(window, jQuery);
