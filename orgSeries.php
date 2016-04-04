<?php
/*
Plugin Name: Organize Series
Plugin URI: http://organizeseries.com
Version: 2.5.2
Description: This plugin adds a number of features to wordpress that enable you to easily write and organize a series of posts and display the series dynamically in your blog. You can associate "icons" or "logos" with the various series. This version of Organize Series Plugin requires at least WordPress 3.4+ and PHP 5.2+ to work.
Author: Darren Ethier
Author URI: http://www.unfoldingneurons.com
Text Domain: organize-series
*/

### INSTALLATION/USAGE INSTRUCTIONS ###
//	Installation and/or usage instructions for the Organize Series Plugin
//	can be found at http://www.unfoldingneurons.com/neurotic-plugins/organize-series-wordpress-plugin/

$os_version = '2.5.2';
######################################

######################################
// Organize Series Wordpress Plugin
//
//"Organize Series Plugin" is copyright (c) 2007-2012 Darren Ethier. This program is free software; you can redistribute it and/or
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
//It goes without saying that this is a plugin for WordPress and I have no interest in developing it for other platforms so please, don't ask ;).
//

######################################
/* Changelog
Visit @link http://wordpress.org/extend/plugins/organize-series/changelog/ for the list of all the changes in Organize Series.

*/

/**
 * Ths file contains all requires/includes for all files packaged with orgSeries and has all the setup/initialization code for the WordPress plugin.
 *
 * @package Organize Series WordPress Plugin
 * @since 2.2
 */

/**
  * Nifty function to get the name of the directory orgSeries is installed in.
*/
function orgSeries_dir(){
	if (stristr(__FILE__, '/') )
		$orgdir = explode('/plugins/', dirname(__FILE__));
	else
		$orgdir = explode('\\plugins\\', dirname(__FILE__));
    return str_replace('\\' , '/', end($orgdir)); //takes care of MS slashes
}

$org_dir_name = orgSeries_dir();

/*to get plugin url*/
	// Guess the location
$plugin_path = '';
$plugin_url = '';
$plugin_path = plugin_dir_path(__FILE__);
$plugin_url = plugins_url('', __FILE__).'/';
$org_series_loc = $plugin_url;

/**
  * This sets the defaults for the constants for orgSeries
*/
define( 'SERIES_PATH_URL', $plugin_url );
define('ORG_SERIES_VERSION', $os_version); //the current version of the plugin
define('SERIES_DIR' , $org_dir_name); //the name of the directory that orgSeries files are located.
define('SERIES_LOC', $org_series_loc); //the uri of the orgSeries files.
define('SERIES_PATH', $plugin_path); //the path of the orgSeries file
//note 'SERIES_QUERY_VAR' is now defined in orgSeries class.
define('SERIES_TOC_QUERYVAR', 'series-toc'); //get/post variable name for querying series-toc from WP
define('SERIES_URL', 'series'); //URL tag to use when querying series archive pages.
define('SERIES_SEARCHURL','search'); //local search URL (from mod_rewrite_rules)
define('SERIES_PART_KEY', '_series_part'); //the default key for the Custom Field that distinguishes what part a post is in the series it belongs to. The underscore makes this hidden on edit post/page screens.
define('SPOST_SHORTTITLE_KEY', '_spost_short_title');
define('SERIES_REWRITERULES','1'); //flag to determine if plugin can change WP rewrite rules.

/*
*include all related files here
*/
//require utility files here first...?
require_once($plugin_path . 'orgSeries-setup.php');
require_once($plugin_path . 'orgSeries-options.php');
require_once($plugin_path . 'orgSeries-rss.php');
require_once($plugin_path . 'orgSeries-admin.php');
require_once($plugin_path . 'orgSeries-icon.php');
require_once($plugin_path . 'orgSeries-taxonomy.php');
require_once($plugin_path . 'orgSeries-template-tags.php');
require_once($plugin_path . 'orgSeries-utility.php');
require_once($plugin_path . 'orgSeries-widgets.php');
require_once($plugin_path . 'orgSeries-manage.php');
require_once($plugin_path . 'inc/debug/plugin_activation_errors.php');
?>
