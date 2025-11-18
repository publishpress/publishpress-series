<?php
/**
 * Post Navigation Module Init
 */

if (! defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/post-navigation.php';
require_once __DIR__ . '/includes/class-fields.php';
require_once __DIR__ . '/classes/PostNavigationRenderer.php';

add_action('init', static function () {
    if (class_exists('PostNavigationRenderer')) {
        PostNavigationRenderer::init();
    }

    if (class_exists('PPS_Series_Post_Navigation_Fields')) {
        PPS_Series_Post_Navigation_Fields::init();
    }
});
