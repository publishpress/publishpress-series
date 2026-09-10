<?php
// Load this checkout without changing the site's active plugin configuration.
WP_CLI::add_wp_hook('muplugins_loaded', function () {
    add_filter('pre_option_active_plugins', '__return_empty_array');
    require dirname(__DIR__) . '/orgSeries.php';
});
