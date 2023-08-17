<?php

use PublishPress\WordPressEDDLicense\Autoloader;

add_action('plugins_loaded', function () {
    if (! defined('PUBLISHPRESS_WORDPRESS_EDD_LICENSE_LOADED')) {
        if (! class_exists('PublishPress\\WordPressEDDLicense\\Autoloader')) {
            require_once __DIR__ . '/classes/Autoloader.php';
        }

        Autoloader::register();

        define('PUBLISHPRESS_WORDPRESS_EDD_LICENSE_LOADED', true);
    }
}, -125, 0);
