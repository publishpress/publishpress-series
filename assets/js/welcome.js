/* global jQuery, ppseriesWelcome */
(function ($) {
    'use strict';

    $(function () {
        var $panel = $('.ppseries-welcome-panel');

        if (!$panel.length) {
            return;
        }

        $panel.on('click', '.ppseries-welcome-start', function () {
            var $name = $('#tag-name');

            if (!$name.length) {
                return;
            }

            $('html, body').animate({
                scrollTop: Math.max(0, $name.offset().top - 80)
            }, 200, function () {
                $name.trigger('focus');
            });
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
