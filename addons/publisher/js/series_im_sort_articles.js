jQuery(document).ready( function($) {
  /*im_update_post_order();*/
  /*$(".im_article_list").sortable({
    axis: "y",
  });*/

  var i18n = window.seriesImSortArticles || {
    moveUp: 'Move up',
    moveDown: 'Move down',
    moveUpLabel: 'Move up: %s',
    moveDownLabel: 'Move down: %s',
    moved: '%1$s moved to position %2$d of %3$d.',
    unsaved: 'Order changed. Save to keep the new order.'
  };

  var sortableSelectors = [
    ".pp-series-publisher-wrap.series-order table tbody",
    ".pp-series-publisher-wrap.series-order-pending table tbody",
    ".pp-series-publisher-wrap.series-publish table tbody"
  ];

  function speak(message) {
    if (window.wp && wp.a11y && typeof wp.a11y.speak === 'function') {
      wp.a11y.speak(message, 'assertive');
    }
  }

  function format(str) {
    var args = Array.prototype.slice.call(arguments, 1);
    var index = 0;
    return str.replace(/%(\d+\$)?[sd]/g, function (match, position) {
      var value = position ? args[parseInt(position, 10) - 1] : args[index++];
      return typeof value === 'undefined' ? '' : value;
    });
  }

  function rowTitle($row) {
    var title = $.trim($row.find('.row-title').first().text());
    return title || $.trim($row.find('td').first().text());
  }

  /**
   * Hidden order field updated on every change, same values the submit
   * button onclick handlers collect, so keyboard moves and drags save the same way.
   */
  function updatePostOrder($tbody) {
    var ids = [];
    $tbody.children('tr').each(function () {
      var id = $(this).attr('id');
      if (id) {
        ids.push(id.substring(5));
      }
    });
    $tbody.closest('.pp-series-publisher-wrap').find('.im_publish_posts').val(ids.join(','));
  }

  function refreshButtons($tbody) {
    var $rows = $tbody.children('tr').not('.no-items');
    $rows.each(function (index) {
      var $row = $(this);
      $row.find('.pp-series-move-up').prop('disabled', index === 0);
      $row.find('.pp-series-move-down').prop('disabled', index === $rows.length - 1);
    });
  }

  function addMoveButtons($tbody) {
    var $rows = $tbody.children('tr').not('.no-items');
    if ($rows.length < 2) {
      return;
    }
    $rows.each(function () {
      var $row = $(this);
      if ($row.find('.pp-series-move-buttons').length) {
        return;
      }
      var title = rowTitle($row);
      var $cell = $row.find('td.column-title, td.column-primary').first();
      if (!$cell.length) {
        $cell = $row.children('td').first();
      }
      var $wrap = $('<div class="pp-series-move-buttons"></div>');
      $('<button type="button" class="button button-small pp-series-move-up"></button>')
        .text(i18n.moveUp)
        .attr('aria-label', format(i18n.moveUpLabel, title))
        .appendTo($wrap);
      $('<button type="button" class="button button-small pp-series-move-down"></button>')
        .text(i18n.moveDown)
        .attr('aria-label', format(i18n.moveDownLabel, title))
        .appendTo($wrap);
      $cell.append($wrap);
    });
    refreshButtons($tbody);
  }

  $.each(sortableSelectors, function (i, selector) {
    $(selector).each(function () {
      var $tbody = $(this);
      $tbody.sortable({
        axis: "y",
        update: function () {
          updatePostOrder($tbody);
          refreshButtons($tbody);
        }
      });
      addMoveButtons($tbody);
    });
  });

  $(document).on('click', '.pp-series-move-up, .pp-series-move-down', function (event) {
    event.preventDefault();
    var $button = $(this);
    var $row = $button.closest('tr');
    var $tbody = $row.closest('tbody');
    var up = $button.hasClass('pp-series-move-up');

    if (up) {
      var $prev = $row.prev('tr');
      if (!$prev.length) {
        return;
      }
      $row.insertBefore($prev);
    } else {
      var $next = $row.next('tr');
      if (!$next.length) {
        return;
      }
      $row.insertAfter($next);
    }

    updatePostOrder($tbody);
    refreshButtons($tbody);
    if ($tbody.data('ui-sortable')) {
      $tbody.sortable('refreshPositions');
    }

    var $rows = $tbody.children('tr').not('.no-items');
    var position = $rows.index($row) + 1;

    // Keep focus on the moved button; if it hit an edge and is now disabled, use its sibling.
    var $target = $button.prop('disabled') ? $row.find(up ? '.pp-series-move-down' : '.pp-series-move-up') : $button;
    $target.trigger('focus');

    speak(format(i18n.moved, rowTitle($row), position, $rows.length) + ' ' + i18n.unsaved);
  });

 function im_update_post_order() {
  var im_post_IDs = new Array();
  jQuery(".im_article_list tr").each( function() {
    im_post_IDs.push(jQuery(this).attr('id').substring(5));
  });
  jQuery(".im_publish_posts").val(im_post_IDs.join(','));
}

});
