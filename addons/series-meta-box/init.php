<?php
/**
 * Series Meta Box Module Init
 */

if (! defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/series-meta-box.php';
require_once __DIR__ . '/includes/class-fields.php';
require_once __DIR__ . '/classes/SeriesMetaBoxRenderer.php';

add_action('init', static function () {
    if (class_exists('SeriesMetaBoxRenderer')) {
        SeriesMetaBoxRenderer::init();
    }

    if (class_exists('PPS_Series_Meta_Box_Fields')) {
        PPS_Series_Meta_Box_Fields::init();
    }
});
