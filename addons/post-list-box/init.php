<?php
/**
 * Post List Box Module Init
 * Loads the Post List Box module
 */

if (! defined('ABSPATH')) {
    exit;
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