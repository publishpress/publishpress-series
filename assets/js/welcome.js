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
            // Move focus before the panel (and the focused button) disappears.
            var $target = $('#tag-name');
            if (!$target.length || !$target.is(':visible')) {
                $target = $('.wrap h1').first();
                if ($target.length && !$target.attr('tabindex')) {
                    $target.attr('tabindex', '-1');
                }
            }

            $panel.slideUp(200, function () {
                $panel.attr('hidden', 'hidden');
            });

            if ($target.length) {
                $target.trigger('focus');
            }

            if (window.wp && wp.a11y && typeof wp.a11y.speak === 'function' && ppseriesWelcome.dismissed) {
                wp.a11y.speak(ppseriesWelcome.dismissed);
            }

            $.post(ppseriesWelcome.ajaxUrl, {
                action: 'ppseries_dismiss_welcome_panel',
                nonce: ppseriesWelcome.nonce
            });
        });
    });
})(jQuery);
