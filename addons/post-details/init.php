<?php
/**
 * Series Post Details Module Init
 */

if (! defined('ABSPATH')) {
    exit;
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
