<?php
/*
Plugin Name: Organize Series Plugin
Plugin URI: http://www.unfoldingneurons.com/neurotic-plugins/organize-series-wordpress-plugin/
Version: 2.0.3
Description: This plugin adds a number of features to wordpress that enable you to easily write and organize a series of posts and display the series dynamically in your blog. You can associate "icons" or "logos" with the various series. This version of Organize Series Plugin requires at least WordPress 2.3 to work.
Author: Darren Ethier
Author URI: http://www.unfoldingneurons.com
*/

### INSTALLATION/USAGE INSTRUCTIONS ###
//	Installation and/or usage instructions for the Organize Series Plugin
//	can be found at http://www.unfoldingneurons.com/neurotic-plugins/organize-series-wordpress-plugin/
// IMPORTANT UPGRADE INSTRUCTIONS FROM EARLIER VERSIONS (i.e. 1.x.x branch) to version 2.0:
// I have implemented a "import" script for people using the earlier versions of this plugin and who want to get your settings and series structure transferred over to the new version.  The plugin will check to see if you have an earlier version and if so it will give you the option of importing settings when you first visit the "Series Options" page.  Follow the instructions there and the transfer should go okay. //

######################################

######################################
// Organize Series Wordpress Plugin
//
//"Organize Series Plugin" is copyright (c) 2007,2008 Darren Ethier. This program is free software; you can redistribute it and/or
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
Visit http://dev.wp-plugins.org/log/organize-series for the list of all the changes in Organize Series.

*/

/**
 * Ths file contains all requires/includes for all files packaged with orgSeries and has all the setup/initialization code for the WordPress plugin. 
 *
 * @package Organize Series WordPress Plugin
 * @since 2.0
 */
 
/**
  * NIfty function to get the name of the directory orgSeries is installed in.
*/
function orgSeries_dir(){
	if (stristr(__FILE__, '/') ) 
		$orgdir = explode('/plugins/', dirname(__FILE__));
	else
		$orgdir = explode('\\plugins\\', dirname(__FILE__));
    return str_replace('\\' , '/', end($orgdir)); //takes care of MS slashes
}

$org_dir_name = orgSeries_dir();
$org_series_loc = get_option('siteurl') . '/wp-content/plugins/' . $org_dir_name . '/';
/**
  * This sets the constants for orgSeries
*/
define('SERIES_DIR' , $org_dir_name); //the name of the directory that orgSeries files are located.
define('SERIES_LOC', $org_series_loc); //the uri of the orgSeries files.
define('SERIES_QUERYVAR', 'series');  // get/post variable name for querying series from WP
define('SERIES_URL', 'series'); //URL to use when querying series
define('SERIES_TEMPLATE', 'series.php'); //template file to use for displaying series queries.
define('SERIES_SEARCHURL','search'); //local search URL (from mod_rewrite_rules)
define('SERIES_PART_KEY', 'series_part'); //the default key for the Custom Field that distinguishes what part a post is in the series it belongs to.
 define('SERIES_REWRITERULES','1'); //flag to determine if plugin can change WP rewrite rules.
$org_series_version = "2.0";
$org_series_args = array('hierarchical' => false, 'update_count_callback' => '_update_post_term_count');
$org_series_term = "series";
$org_series_type = "post";
global $org_series_version, $org_series_args, $org_series_term, $org_series_type, $wp_version;

/**
  * The following files are needed for orgSeries to work:
  * 1. series-utility.php: contains all the orgSeries utility functions required by all orgSeries files.
  * 2. series-taxonomy.php: contains all functions hooking into WordPress taxomony and setting up the new series taxomony.
  * 3. series-icon.php:  contains all the code required for series-icons implementation.
  * 4. series-template-tags.php: contains all the various functions that can be used as "template-tags" for customized display of orgSeries stuff in users blogs.  Theme authors will want to check this file out! 
  * 5. series-admin: contains all the hooks/code required for hooking into/implementation into the WordPress administration.
  * 6. orgSeries-rss.php: contains all the code required for hooking series related info into WordPress feeds.
  * 7. series-widgets.php: contains all the code for the orgSeries widgets (used in widget enabled themes).
*/
require (ABSPATH . '/wp-content/plugins/' . SERIES_DIR . '/series-utility.php');
require (ABSPATH . '/wp-content/plugins/' . SERIES_DIR .'/series-taxonomy.php');
require (ABSPATH . '/wp-content/plugins/' . SERIES_DIR .'/series-icon.php');
require (ABSPATH . '/wp-content/plugins/' . SERIES_DIR .'/series-template-tags.php');
require (ABSPATH . '/wp-content/plugins/' . SERIES_DIR .'/series-admin.php');
require (ABSPATH . '/wp-content/plugins/' . SERIES_DIR .'/orgSeries-rss.php');
require (ABSPATH . '/wp-content/plugins/' . SERIES_DIR .'/series-widgets.php');

/**
  * org_series_install() - contains all the routines that are run when Organize Series is activated via the WordPress plugins page.
  *
  * @uses register_taxonomy() - WordPress hook for setting the new series taxonomy
  * @uses orgSeries_roles() - function that adds manage_series capability to $wp_roles object.
  * @uses get_option()
  * @uses add_option()
  * @uses update_option()
  * @uses dbDelta() - core WordPress function for creating new database tables (for series-icon related data)
  * @uses $wpdb - global WordPress database object
*/
function org_series_install() {
	global $org_series_version, $org_series_args, $org_series_term, $org_series_type, $wp_taxonomies, $wpdb;
	register_taxonomy( $org_series_term, $org_series_type, $org_series_args );
	orgSeries_roles(); 
         
	if ( $options = get_option( 'org_series_options' ) && !( $oldversion = get_option('org_series_version' ) ) )  //for versions prior to 2.0
		add_option('org_series_oldversion', '1.6');
		
	if ( $oldversion = get_option( 'org_series_version' ) )  //for versions after 2.0
		update_option( 'org_series_oldversion', $oldversion );
		
	add_option("org_series_version", $org_series_version);
		
	//create table for series icons
	$table_name = $wpdb->prefix . "orgSeriesIcons";
	if( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) != $table_name ) {
		$sql = "CREATE TABLE " . $table_name . " (
			term_id INT NOT NULL,
			icon VARCHAR(100) NOT NULL,
			PRIMARY KEY term_id (term_id)
		);";
		require_once( ABSPATH . 'wp-admin/upgrade-functions.php' );
		dbDelta( $sql );
	}
	
	add_option( 'series_icon_path', '' );
	add_option( 'series_icon_url', '' );
	add_option( 'series_icon_filetypes', 'jpg gif jpeg png' );
}

//*** Add .css to header if enabled via options ***//
function orgSeries_header() {
	$settings = get_option('org_series_options');
	$plugin_path = SERIES_LOC;
	if ($settings['custom_css']) {
		$csspath = $plugin_path."orgSeries.css";
		$text = '<link rel="stylesheet" href="' . $csspath . '" type="text/css" media="screen" />';
	} else {
		$text = '';
	}
	
	echo $text;
}

###### CREATE SERIES OPTIONS PANEL FUNCTION #######
function series_organize_options() {
	global $wp_version;
	if (function_exists('add_options_page')) { 
		if ( isset( $wp_version ) && $wp_version >= 2.5 )
			add_options_page('Organize Series Options', 'Series Options', 9, SERIES_LOC . 'orgSeries-options-new.php');
		else
			add_options_page('Organize Series Options', 'Series Options', 9, SERIES_LOC . 'orgSeries-options.php'); 
	}
	if (function_exists('add_management_page'))	
		add_management_page('Organize Series Management', 'Series', 9, SERIES_LOC . 'orgSeries-manage.php');
}

#####Filter function for adding series post-list box to a post in that series####
function add_series_post_list_box($content) {
	
	$settings = get_option('org_series_options');
	
	if ($settings['auto_tag_toggle']) {
		if (is_single() && $postlist = wp_postlist_display() ) {
			$addcontent = $content;
			$content = str_replace('%postcontent%', $addcontent, $postlist);
		}
	}
	
	return $content;
}

#####Filter function for adding series meta information to posts in series#####

function add_series_meta($content) {
	$settings = get_option('org_series_options');
	
	if($settings['auto_tag_seriesmeta_toggle']) {
		if ($series_meta = wp_seriesmeta_write()) {
			$addcontent = $content;
			$content = str_replace('%postcontent%', $addcontent, $series_meta);
		}
	}
	return $content;
}

//filter function for showing the navigation strip for posts that are part of a series  on the page of a post that is part of a series.
function series_nav_filter($content) {
	$settings = get_option('org_series_options');
	if (is_single()) {
		if($settings['auto_tag_toggle'] && $series_nav = wp_assemble_series_nav() ) {
			$addcontent = $content;
			$content = str_replace('%postcontent%', $addcontent, $series_nav);
		}
	}
	return $content;
}

function add_series_wp_title( $title ) {
	$series = single_series_title('', false);
	
	if ( !empty($series) ) {
		if ( !is_feed() )
			$title = 'Series: ' . $series . ' &laquo; ' . $title;
		else
			$title = 'Posts from the series: ' . $series . '(UnfoldingNeurons.com)';
	}
	return $title;
}

//Roles and Capabilities Stuff
function orgSeries_roles() {
global $wp_roles;
$roles = array('administrator', 'editor');
$capability = 'manage_series';

foreach ($roles as $role) {
	$wp_roles->add_cap($role, $capability, true);
}
return true;
}

##########ADD ACTIONS TO WP###########
//initialize plugin
register_taxonomy($org_series_term, $org_series_type, $org_series_args);
add_action('activate_' . SERIES_DIR . '/orgSeries.php','org_series_install');

//insert .css in header if needed
add_action('wp_head', 'orgSeries_header');

//add admin menu for selecting options
add_action('admin_menu', 'series_organize_options');

//add filter to automatically add the tag for showing other posts in a series in a single post of that series.  Conditional upon "autotags" being selected in the admin options menu.
add_action('the_content', 'add_series_post_list_box');

//add filter to automatically add the tag for showing the meta information for each post if it is part of a series (i.e.  What part in the series it is, what's the title of the series etc.).
add_action('the_content', 'add_series_meta');

//add filter to automatically add the series-post-navigation strip
add_action('the_content', 'series_nav_filter');
add_filter('wp_title', 'add_series_wp_title');
?>