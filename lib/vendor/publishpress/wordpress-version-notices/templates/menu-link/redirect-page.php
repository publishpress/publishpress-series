<div class="pp-version-notice-upgrade-menu-item-page">
    <p>
        <span class="dashicons dashicons-smiley spin bounce"></span>
        <div class="message"><?php echo esc_html($context['message']); ?></div>
    </p>

    <script type="application/javascript">
        window.setTimeout(
            function () {
            window.location.replace("<?php echo esc_url($context['link']); ?>");
            },
            600
        );
    </script>
</div>
