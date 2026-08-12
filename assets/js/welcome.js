/* global jQuery, ppseriesWelcome */
(function ($) {
    'use strict';

    $(function () {
        var $greeting = $('.ppseries-welcome-greeting');
        var $panel = $('.ppseries-welcome-panel');

        $greeting.on('click', '.ppseries-welcome-dismiss', function () {
            $greeting.slideUp(200);

            $.post(ppseriesWelcome.ajaxUrl, {
                action: 'ppseries_dismiss_welcome_panel',
                nonce: ppseriesWelcome.nonce
            });
        });

        $panel.on('click', '.ppseries-preview-tab', function () {
            var $tab = $(this);
            var target = $tab.data('preview');

            $panel.find('.ppseries-preview-tab')
                .removeClass('is-active')
                .attr('aria-selected', 'false');
            $tab.addClass('is-active').attr('aria-selected', 'true');

            $panel.find('.ppseries-preview-pane').removeClass('is-active');
            $panel.find('[data-preview-pane="' + target + '"]').addClass('is-active');
        });
    });
})(jQuery);
