/* global jQuery, ppseriesWelcome */
(function ($) {
    'use strict';

    $(function () {
        var $panel = $('.ppseries-welcome-panel');

        if (!$panel.length) {
            return;
        }

        $panel.on('click', '.ppseries-welcome-dismiss', function () {
            $panel.slideUp(200);

            $.post(ppseriesWelcome.ajaxUrl, {
                action: 'ppseries_dismiss_welcome_panel',
                nonce: ppseriesWelcome.nonce
            });
        });
    });
})(jQuery);
