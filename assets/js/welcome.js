/* global jQuery, ppseriesWelcome */
(function ($) {
    'use strict';

    $(function () {
        var $panel = $('.ppseries-welcome-panel');

        if (!$panel.length) {
            return;
        }

        $panel.on('click', '.ppseries-welcome-add', function () {
            var $field = $('#tag-name');

            if (!$field.length) {
                return;
            }

            $('html, body').animate({
                scrollTop: $field.offset().top - 80
            }, 300);

            $field.trigger('focus').addClass('ppseries-welcome-highlight');

            window.setTimeout(function () {
                $field.removeClass('ppseries-welcome-highlight');
            }, 2000);
        });

        $panel.on('click', '.ppseries-welcome-dismiss', function () {
            $panel.slideUp(200);

            $.post(ppseriesWelcome.ajaxUrl, {
                action: 'ppseries_dismiss_welcome_panel',
                nonce: ppseriesWelcome.nonce
            });
        });
    });
})(jQuery);
