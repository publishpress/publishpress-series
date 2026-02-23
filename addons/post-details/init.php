<?php
/**
 * Series Post Details Module Init
 */

if (! defined('ABSPATH')) {
    exit;
}

// Always load utilities (needed by settings page)
require_once __DIR__ . '/includes/class-utilities.php';

// Skip everything else if the Post Details toggle is off
$pps_pd_opts = get_option('org_series_options', []);
if (empty($pps_pd_opts['auto_tag_seriesmeta_toggle'])) {
    return;
}

require_once __DIR__ . '/post-details.php';
require_once __DIR__ . '/includes/class-fields.php';
require_once __DIR__ . '/classes/SeriesPostDetailsRenderer.php';

add_action('init', static function () {
    if (class_exists('SeriesPostDetailsRenderer')) {
        SeriesPostDetailsRenderer::init();
    }

    if (class_exists('PPS_Series_Post_Details_Fields')) {
        PPS_Series_Post_Details_Fields::init();
    }
});
