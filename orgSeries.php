<?php
/**
 * Plugin Name: PublishPress Series
 * Plugin URI: https://publishpress.com/publishpress-series/
 * Description: PublishPress Series allows you to group content together into a series. This is ideal for magazines, newspapers, short-story writers, teachers, comic artists, or anyone who writes multiple posts on the same topic.
 * Version: 2.10.1
 * Author: PublishPress
 * Author URI: https://publishpress.com/
 * Text Domain: organize-series
 * Domain Path: /languages
 * Requires at least: 5.5
 * Requires PHP: 7.2.5
 * License: GPLv3
 *
 * Copyright (c) 2022 PublishPress
 *
 * ------------------------------------------------------------------------------
 * Based on Organize Series
 * Author: Darren Ethier
 * Copyright (c) 2007, 2011 Darren Ethier
 * ------------------------------------------------------------------------------
 *
 * @package 	publishpress-series
 * @author		PublishPress
 * @copyright   Copyright (C) 2007, 2011 Darren Ethier; modifications Copyright (C) 2022 PublishPress
 * @license		GNU General Public License version 2
 * @link		https://publishpress.com/
 */

######################################

######################################
// Publishpress Series Wordpress Plugin
//
// "PublishPress Series Plugin" is copyright (c) 2007-2021 Darren Ethier and also PublishPress LLC. This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
//
//

######################################
/* Changelog
Visit @link http://wordpress.org/extend/plugins/organize-series/changelog/ for the list of all the changes in Publishpress Series.

*/

global $wp_version;

$min_php_version = '7.2.5';
$min_wp_version  = '5.5';

$invalid_php_version = version_compare(phpversion(), $min_php_version, '<');
$invalid_wp_version = version_compare($wp_version, $min_wp_version, '<');

if ($invalid_php_version || $invalid_wp_version) {
    return;
}

$includeFileRelativePath = '/publishpress/publishpress-instance-protection/include.php';
if (file_exists(__DIR__ . '/vendor' . $includeFileRelativePath)) {
    require_once __DIR__ . '/vendor' . $includeFileRelativePath;
}

if (class_exists('PublishPressInstanceProtection\\Config')) {
    $pluginCheckerConfig = new PublishPressInstanceProtection\Config();
    $pluginCheckerConfig->pluginSlug    = 'orgSeries';
    $pluginCheckerConfig->pluginFolder  = 'organize-series';
    $pluginCheckerConfig->pluginName    = 'PublishPress Series';

    $pluginChecker = new PublishPressInstanceProtection\InstanceChecker($pluginCheckerConfig);
}

//composer autoload
$autoloadPath = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
}

require_once PUBLISHPRESS_SERIES_VENDOR_PATH . '/publishpress/psr-container/lib/include.php';
require_once PUBLISHPRESS_SERIES_VENDOR_PATH . '/publishpress/pimple-pimple/lib/include.php';
require_once PUBLISHPRESS_SERIES_VENDOR_PATH . '/publishpress/wordpress-version-notices/src/include.php';

add_action('plugins_loaded', function() {
    if (! class_exists('PublishPress\\OrganizeSeries\\Autoloader')) {
        require_once __DIR__ . '/includes-core/Autoloader.php';
    }

    $autoloader = new PublishPress\OrganizeSeries\Autoloader();
    $autoloader->register();

    require_once (dirname(__FILE__) . '/inc/utility-functions.php');
    require_once (dirname(__FILE__) . '/includes-core/functions.php');
    register_activation_hook( __FILE__, 'pp_series_core_activation' );

    if (!defined('ORG_SERIES_VERSION')) {
        define('ORG_SERIES_VERSION', '2.10.1'); //the current version of the plugin
        define( 'SERIES_FILE_PATH', __FILE__ );
        define( 'SERIES_PATH_URL', plugins_url('', __FILE__).'/' );
        define('SERIES_LOC', plugins_url('', __FILE__).'/' ); //the uri of the orgSeries files.
        define('SERIES_PATH', plugin_dir_path(__FILE__)); //the path of the orgSeries file
        //note 'SERIES_QUERY_VAR' is now defined in orgSeries class.
        define('SERIES_TOC_QUERYVAR', 'series-toc'); //get/post variable name for querying series-toc from WP
        define('SERIES_SEARCHURL','search'); //local search URL (from mod_rewrite_rules)
        define('SERIES_PART_KEY', '_series_part'); //the default key for the Custom Field that distinguishes what part a post is in the series it belongs to. The underscore makes this hidden on edit post/page screens.
        define('SPOST_SHORTTITLE_KEY', '_spost_short_title');
        define('SERIES_REWRITERULES','1'); //flag to determine if plugin can change WP rewrite rules.
        define ('PUBLISHPRESS_SERIES_ABSPATH', __DIR__);
        define('SERIES_DIR' , orgSeries_dir()); //the name of the directory that orgSeries files are located.
    }

    $pro_active = false;

    foreach ((array)get_option('active_plugins') as $plugin_file) {
        if (false !== strpos($plugin_file, 'publishpress-series-pro.php')) {
            $pro_active = true;
            break;
        }
    }

    if (!$pro_active && is_multisite()) {
        foreach (array_keys((array)get_site_option('active_sitewide_plugins')) as $plugin_file) {
            if (false !== strpos($plugin_file, 'publishpress-series-pro.php')) {
                $pro_active = true;
                break;
            }
        }
    }

    if ($pro_active) {
        add_filter(
            'plugin_row_meta',
            function($links, $file)
            {
                if ($file == plugin_basename(__FILE__)) {
                    $links[]= __('<strong>This plugin can be deleted.</strong>', 'press-permit-core');
                }

                return $links;
            },
            10, 2
        );
    }

    if (defined('PPSERIES_FILE') || $pro_active) {
        return;
    }

    define ('PPSERIES_FILE', __FILE__ );
    define ('PPSERIES_PATH', plugin_dir_path(__FILE__));
    define ('PPSERIES_URL', plugin_dir_url(__FILE__));
    define ('PPSERIES_BASE_NAME', plugin_basename(__FILE__));

    //new bootstrapping, eventually this will replace all of the above.
    require PPSERIES_PATH . 'bootstrap.php';

    pp_series_free_version_init();
}, -10);
