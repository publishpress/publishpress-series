<?php
/**
 * Post List Box Module Init
 * Loads the Post List Box module
 */

if (! defined('ABSPATH')) {
    exit;
}

// Always load utilities (needed by settings page)
require_once __DIR__ . '/includes/class-utilities.php';

// Skip everything else if the Post List Box toggle is off
$pps_plb_opts = get_option('org_series_options', []);
if (array_key_exists('auto_tag_toggle', $pps_plb_opts) && empty($pps_plb_opts['auto_tag_toggle'])) {
    return;
}

// Load the main Post List Box class
require_once __DIR__ . '/post-list-box.php';

// Load supporting classes
require_once __DIR__ . '/classes/PostListBoxFields.php';
require_once __DIR__ . '/classes/PostListBoxRenderer.php';

// Initialize the renderer
add_action('init', function() {
    if (class_exists('PostListBoxRenderer')) {
        PostListBoxRenderer::init();
    }
    if (class_exists('PostListBoxFields')) {
        PostListBoxFields::init();
    }
});
