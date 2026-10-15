/**
 * Collapsible filter panel.
 *
 * Ported from vlsm's FilterPanel (public/assets/js/main.js.php), so a filter
 * panel behaves the same in both apps. A page opts in with classes alone:
 *
 *   <div class="filter-panel">                 folds, gets a "Filters" header
 *     <div class="filter-panel-body">...</div>  the controls; this part folds
 *     <div class="filter-actions">              optional button bar, stays put
 *       <button class="filter-search">          collapses the panel when clicked
 *       <button class="filter-export">          stays visible while collapsed
 *       <button class="filter-keep">            same, for non-export buttons
 *
 * then calls FilterPanel.init(panel, { labels: {...} }) with translated labels.
 * Collapsed, the other buttons in the bar hide and an "Expand Filters" button
 * appears. The header shows what the panel is filtered to. Add
 * "filter-panel-collapsed" to the panel to start folded. A page that sets
 * filters without a change event can call FilterPanel.refresh(panel).
 */
(function (window, $) {
  'use strict';

  var defaultLabels = {
    filters: 'Filters',
    filtersApplied: 'Filters applied',
    toggle: 'Show or hide filters',
    expand: 'Expand Filters',
    remove: 'Remove filter'
  };

  function labels(panel) {
    return $(panel).data('filter-panel-labels') || defaultLabels;
  }

  function body(panel) {
    return panel.find('.filter-panel-body').first();
  }

  function labelFor(el) {
    var label = el.attr('id') ? $('label[for="' + el.attr('id') + '"]').first() : $();
    if (!label.length) {
      label = el.closest('.form-group').find('label').first();
    }
    return $.trim(label.text()).replace(/\s*:$/, '');
  }

  // The placeholder option is empty, and a multi-select reports it as [""],
  // so empty values are dropped rather than read as a filter. A control
  // hidden because it does not apply to this user is not a filter either.
  function valueText(el) {
    if (el.is(':disabled')) {
      return '';
    }
    if (el.is('select')) {
      return el.find('option:selected').map(function () {
        return $.trim($(this).val()) === '' ? null : $.trim($(this).text());
      }).get().join(', ');
    }
    return $.trim(el.val() || '');
  }

  // A control can be cleared when "nothing" is a value it can hold: a text
  // box, a multi-select, or a select with an empty placeholder option.
  function clearable(el) {
    if (!el.is('select') || el.prop('multiple')) {
      return true;
    }
    return el.find('option').filter(function () {
      return $.trim($(this).val()) === '';
    }).length > 0;
  }

  function clear(el) {
    el.val(el.is('select[multiple]') ? [] : '').trigger('change');
  }

  // Every applied filter is shown as a chip; clicking one opens the panel on
  // that control. Where the panel has a search button to re-run, a clearable
  // filter also gets an x that removes it.
  function refresh(panel) {
    $(panel).each(function () {
      var text = labels(this);
      var canSearch = $(this).find('.filter-search').length > 0;
      var summary = $(this).find('.filter-panel-summary').first().empty();
      body($(this))
        .find('select, input[type="text"], input[type="number"], textarea')
        .not('.select2-search__field, .filter-panel-ignore, .filter-panel-ignore *')
        .each(function () {
          var el = $(this);
          var label = labelFor(el);
          var value = valueText(el);
          if (!label || !value) {
            return;
          }
          var chip = $('<span class="filter-panel-chip" role="button" tabindex="0">')
            .attr('title', label + ': ' + value)
            .append($('<span class="filter-panel-chip-label">').text(label))
            .append($('<span class="filter-panel-chip-value">').text(value))
            .data('control', el)
            .appendTo(summary);
          if (canSearch && clearable(el)) {
            $('<span class="filter-panel-chip-remove" role="button" tabindex="0">&times;</span>')
              .attr('title', text.remove)
              .appendTo(chip);
          }
        });
      var count = summary.children().length;
      summary.toggleClass('is-visible', count > 0);
      $(this).find('.filter-panel-count').first()
        .text(count).toggleClass('is-visible', count > 0);
    });
  }

  function toggle(panel, collapsed) {
    panel = $(panel).first();
    if (!panel.length) {
      return;
    }
    if (typeof collapsed === 'undefined') {
      collapsed = !panel.hasClass('filter-panel-collapsed');
    }
    panel.toggleClass('filter-panel-collapsed', collapsed);
    panel.find('.filter-panel-toggle i').first()
      .toggleClass('fa-minus', !collapsed).toggleClass('fa-plus', collapsed);
    if (collapsed) {
      refresh(panel);
      body(panel).slideUp(150);
    } else {
      body(panel).slideDown(150);
    }
  }

  function init(panel, options) {
    panel = $(panel);
    if (panel.data('filter-panel')) {
      return;
    }
    panel.data('filter-panel', true);
    var text = $.extend({}, defaultLabels, (options && options.labels) || {});
    panel.data('filter-panel-labels', text);

    // Built with text() and attr() so a translation holding a quote or markup
    // character stays text
    if (!panel.children('.filter-panel-header').length) {
      var title = $('<h3 class="filter-panel-title">')
        .append('<i class="fa fa-filter"></i> ')
        .append(document.createTextNode(text.filters))
        .append(' ')
        .append($('<span class="filter-panel-count">').attr('title', text.filtersApplied));
      var toggleButton = $('<button type="button" class="btn btn-link filter-panel-toggle"><i class="fa fa-minus"></i></button>')
        .attr('title', text.toggle);
      $('<div class="filter-panel-header">')
        .append(title)
        .append('<span class="filter-panel-summary"></span>')
        .append(toggleButton)
        .prependTo(panel);
    }
    var actions = panel.find('.filter-actions').first();
    if (actions.length && !actions.find('.filter-expand').length) {
      $('<button type="button" class="filter-expand btn btn-default btn-sm"><i class="fa fa-filter"></i> </button>')
        .append(document.createTextNode(text.expand))
        .prependTo(actions)
        .after(' ');
    }
    if (panel.hasClass('filter-panel-collapsed')) {
      body(panel).hide();
      panel.find('.filter-panel-toggle i').first().removeClass('fa-minus').addClass('fa-plus');
    }
    panel.on('change', '.filter-panel-body', function () {
      refresh(panel);
    });
    refresh(panel);
  }

  // Removing a filter re-runs the search, so the results never disagree with
  // the chips, even when a page's own change handler on the control throws.
  // The panel stays folded.
  $(document).on('click', '.filter-panel-chip-remove', function (e) {
    e.stopPropagation();
    var chip = $(this).closest('.filter-panel-chip');
    var panel = chip.closest('.filter-panel');
    try {
      clear(chip.data('control'));
    } finally {
      refresh(panel);
      panel.find('.filter-search').first().trigger('click');
    }
  });
  $(document).on('keydown', '.filter-panel-chip, .filter-panel-chip-remove', function (e) {
    if (e.key === 'Enter' || e.key === ' ') {
      e.preventDefault();
      e.stopPropagation();
      $(this).trigger('click');
    }
  });
  $(document).on('click', '.filter-panel-chip', function (e) {
    e.stopPropagation();
    var control = $(this).data('control');
    toggle($(this).closest('.filter-panel'), false);
    setTimeout(function () {
      control[0].scrollIntoView({ block: 'center', behavior: 'smooth' });
      if (control.hasClass('select2-hidden-accessible')) {
        control.select2('open');
      } else {
        control.trigger('focus');
      }
    }, 200);
  });
  $(document).on('click', '.filter-panel-header', function () {
    toggle($(this).closest('.filter-panel'));
  });
  $(document).on('click', '.filter-panel .filter-expand', function () {
    toggle($(this).closest('.filter-panel'), false);
  });
  $(document).on('click', '.filter-panel .filter-search', function () {
    toggle($(this).closest('.filter-panel'), true);
  });

  window.FilterPanel = {
    init: init,
    refresh: refresh,
    collapse: function (panel) {
      toggle(panel, true);
    },
    expand: function (panel) {
      toggle(panel, false);
    }
  };
})(window, jQuery);
