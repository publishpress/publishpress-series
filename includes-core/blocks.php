<?php

/**
 * Gutenberg blocks for PublishPress Series.
 */

namespace PublishPress\Series;

if (! defined('ABSPATH')) {
    exit;
}

if (class_exists('PublishPress\\Series\\Blocks')) {
    return;
}

require_once __DIR__ . '/class-blocks.php';

\PublishPress\Series\Blocks::init();
