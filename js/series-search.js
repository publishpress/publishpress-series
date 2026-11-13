jQuery(function ($) {
    'use strict';

    var config = window.ppSeriesSearch || {};
    var $input = $('#editor-series-search-input');
    var $list = $('#serieschecklist');
    var $button = $('#ppseries-load-more');
    var $status = $('#ppseries-search-status');
    var timer;
    var request;
    var sequence = 0;
    var nextPage = 1;

    if (!$input.length || !$list.length) {
        return;
    }

    function renderItem(item) {
        var id = parseInt(item.id, 10);
        var $li = $('<li>').attr('id', 'series-' + id);
        var $radio = $('<input>', {
            type: 'radio',
            name: 'post_series',
            id: 'in-series-' + id,
            value: id
        });
        var $label = $('<label>', {
            'class': 'selectit',
            'for': 'in-series-' + id
        }).append($('<span>', {'class': 'li-series-name'}).text(item.name));
        var $link = $('<a>', {
            'class': 'selected-series-order',
            href: config.orderUrl + encodeURIComponent(id),
            target: '_blank',
            rel: 'noopener noreferrer'
        }).css('display', 'none')
            .append(document.createTextNode(config.orderLabel + ' '))
            .append($('<span>', {'class': 'dashicons dashicons-external', 'aria-hidden': 'true'}))
            .append($('<span>', {'class': 'screen-reader-text'}).text(config.newTab));

        return $li.append($radio, $label, $link);
    }

    function search(page) {
        var current = ++sequence;
        var query = $input.val();

        if (request) {
            request.abort();
        }

        $list.attr('aria-busy', 'true');
        $status.text(config.loading);
        $button.prop('disabled', true);

        request = $.ajax({
            url: config.ajaxUrl,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'ppseries_search_metabox_series',
                nonce: config.nonce,
                post_id: $('#ppseries-search-post-id').val(),
                post_type: $('#ppseries-search-post-type').val(),
                search: query,
                page: page
            }
        }).done(function (response) {
            var items;
            var selectedId;
            var count;

            if (current !== sequence || !response.success) {
                if (current === sequence) {
                    $status.text(config.error);
                }
                return;
            }

            items = response.data.items;
            if (page === 0) {
                // Keep checked radio in DOM so filtering cannot clear post's selection.
                $list.children('li').not('#series-0').filter(function () {
                    return !$(this).find('input[name="post_series"]').prop('checked');
                }).remove();
            }

            selectedId = parseInt($list.find('input[name="post_series"]:checked').val(), 10);
            $.each(items, function (index, item) {
                if (item.id !== selectedId && !document.getElementById('series-' + item.id)) {
                    $list.append(renderItem(item));
                }
            });

            nextPage = page + 1;
            $button.prop('hidden', !response.data.hasMore);
            count = items.length;
            $status.text(count === 0 ? config.noResults : (count === 1 ? config.oneResult : config.results.replace('%d', count)));
        }).fail(function (xhr, state) {
            if (state !== 'abort' && current === sequence) {
                $status.text(config.error);
            }
        }).always(function () {
            if (current === sequence) {
                $list.removeAttr('aria-busy');
                $button.prop('disabled', false);
                request = null;
            }
        });
    }

    $input.on('input', function () {
        clearTimeout(timer);
        $button.prop('hidden', true);
        timer = setTimeout(function () {
            search(0);
        }, 250);
    });

    $button.on('click', function () {
        search(nextPage);
    });
});
