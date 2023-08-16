<?php

/*****************************************************************
 * This file is generated on composer update command by
 * a custom script. 
 * 
 * Do not edit it manually!
 ****************************************************************/

namespace PublishPress\WordpressEddLicense;

use function add_action;
use function do_action;

if (! function_exists('add_action')) {
    return;
}

if (! function_exists(__NAMESPACE__ . '\register3Dot0Dot1')) {
    if (! defined('PUBLISHPRESS_WORDPRESS_EDD_LICENSE_INCLUDED')) {
        define('PUBLISHPRESS_WORDPRESS_EDD_LICENSE_INCLUDED', __DIR__);
    }
        
    if (! class_exists('PublishPress\WordpressEddLicense\Versions')) {
        require_once __DIR__ . '/Versions.php';

        add_action('plugins_loaded', [Versions::class, 'initializeLatestVersion'], -150, 0);
    }

    add_action('plugins_loaded', __NAMESPACE__ . '\register3Dot0Dot1', -190, 0);

    function register3Dot0Dot1()
    {
        if (! class_exists('PublishPress\WordPressEDDLicense\License')) {
            $versions = Versions::getInstance();
            $versions->register('3.0.1', __NAMESPACE__ . '\initialize3Dot0Dot1');
        }
    }

    function initialize3Dot0Dot1()
    {
        require_once __DIR__ . '/autoload.php';
        
        if (! defined('PUBLISHPRESS_WORDPRESS_EDD_LICENSE_VERSION')) {
            define('PUBLISHPRESS_WORDPRESS_EDD_LICENSE_VERSION', '3.0.1');
        }
        
        do_action('publishpress_wordpress_edd_license_3Dot0Dot1_initialized');
    }
}
