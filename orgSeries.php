<?php
/*
Plugin Name: Organize Series
Plugin URI: http://www.unfoldingneurons.com/neurotic-plugins/organize-series-wordpress-plugin/
Version: 2.1.
Description: This plugin adds a number of features to wordpress that enable you to easily write and organize a series of posts and display the series dynamically in your blog. You can associate "icons" or "logos" with the various series. This version of Organize Series Plugin requires at least WordPress 2.8 to work. 
Author: Darren Ethier
Author URI: http://www.unfoldingneurons.com
*/

$org_series_version = "2.1";

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
  * Nifty function to get the name of the directory orgSeries is installed in.
*/
function orgSeries_dir(){
	global $org_domain;
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
$plugin_path = WP_CONTENT_DIR.'/plugins/'.plugin_basename(basename(dirname(__FILE__))) . '/'; //works with symlinks thanks to patch from Georg S. Adamsen (wordpress.blogos.dk)
$plugin_url = WP_CONTENT_URL.'/plugins/'.plugin_basename(basename(dirname(__FILE__))) . '/'; //works with symlinks thanks to patch from Georg S. Adamsen (wordpress.blogos.dk)
$org_series_loc = $plugin_url;

/**
  * This sets the constants for orgSeries
*/
define('SERIES_DIR' , $org_dir_name); //the name of the directory that orgSeries files are located.
define('SERIES_LOC', $org_series_loc); //the uri of the orgSeries files.
define('SERIES_PATH', $plugin_path); //the path of the orgSeries files
define('SERIES_QUERYVAR', 'series');  // get/post variable name for querying series from WP
define('SERIES_URL', 'series'); //URL tag to use when querying series archive pages.
define('SERIES_TEMPLATE', 'series.php'); //template file to use for displaying series queries.
define('SERIES_SEARCHURL','search'); //local search URL (from mod_rewrite_rules)
define('SERIES_PART_KEY', 'series_part'); //the default key for the Custom Field that distinguishes what part a post is in the series it belongs to.
define('SERIES_REWRITERULES','1'); //flag to determine if plugin can change WP rewrite rules.   

$org_series_args = array('hierarchical' => false, 'update_count_callback' => '_update_post_term_count', 'label' => __('Series'), 'query_var' => false, 'rewrite' => false);
$org_series_term = "series";
$org_series_type = "post";
global $org_series_version, $org_series_args, $org_series_term, $org_series_type, $wp_version;

/**
  * The following files are needed for orgSeries to work:
  * 1. series-utility.php: contains all the orgSeries utility functions required by all orgSeries files.
  * 2. series-taxonomy.php: contains all functions hooking into WordPress taxonomy and setting up the new series taxonomy.
  * 3. series-icon.php:  contains all the code required for series-icons implementation.
  * 4. series-template-tags.php: contains all the various functions that can be used as "template-tags" for customized display of orgSeries stuff in users blogs.  Theme authors will want to check this file out! 
  * 5. series-admin: contains all the hooks/code required for hooking into/implementation into the WordPress administration.
  * 6. orgSeries-rss.php: contains all the code required for hooking series related info into WordPress feeds.
  * 7. series-widgets.php: contains all the code for the orgSeries widgets (used in widget enabled themes).
 */
require ($plugin_path . 'series-utility.php');
require ($plugin_path .'series-taxonomy.php');
require ($plugin_path .'series-icon.php');
require ($plugin_path .'series-template-tags.php');
require ($plugin_path .'series-admin.php');
require ($plugin_path .'orgSeries-rss.php');
require ($plugin_path .'series-widgets.php');

/**
  * org_series_install() - contains all the routines that are run when Organize Series is activated via the WordPress plugins page.
  *
  * @uses orgSeries_roles() - function that adds manage_series capability to $wp_roles object.
  * @uses get_option()
  * @uses add_option()
  * @uses update_option()
  * @uses dbDelta() - core WordPress function for creating new database tables (for series-icon related data)
  * @uses $wpdb - global WordPress database object
*/
function org_series_install() {
	global $org_series_version, $wp_taxonomies, $wpdb, $org_domain;
	
	orgSeries_roles(); 
         
	if ( $oldversion = get_option( 'org_series_version' ) )  //register the current version of orgSeries
		update_option( 'org_series_oldversion', $org_series_version );
	else
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
	global $org_domain;
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
	global $wp_version, $org_domain;
	if (function_exists('add_options_page')) { 
		add_options_page(__('Organize Series Options',$org_domain), __('Series Options',$org_domain), 9, SERIES_PATH . 'orgSeries-options.php');
	}
	
	if (function_exists('add_posts_page')) {
		add_posts_page(__('Organize Series Management',$org_domain), __('Post Series', $org_domain), 9, SERIES_PATH . 'orgSeries-manage.php');
	} 
}

#####Filter function for adding series post-list box to a post in that series####
function add_series_post_list_box($content) {
	global $org_domain;
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
	global $org_domain;
	$settings = get_option('org_series_options');
	if($settings['auto_tag_seriesmeta_toggle']) {
		if ($series_meta = wp_seriesmeta_write()) {
			$addcontent = $content;
			$content = str_replace('%postcontent%', $addcontent, $series_meta);
		}
	}
	return $content;
}

function add_series_meta_excerpt($content) {
	global $org_domain;
	if ( is_single() ) return;
	$settings = get_option('org_series_options');
	remove_filter('the_content','add_series_meta');
	if($settings['auto_tag_seriesmeta_toggle']) {
		if ($series_meta = wp_seriesmeta_write(true)) {
			$addcontent = $content;
			$content = str_replace('%postcontent%', $addcontent, $series_meta);
		}
	}
	echo $content;
}

//filter function for showing the navigation strip for posts that are part of a series  on the page of a post that is part of a series.
function series_nav_filter($content) {
	global $org_domain;
	$settings = get_option('org_series_options');
	if (is_single()) {
		if($settings['auto_tag_nav_toggle'] && $series_nav = wp_assemble_series_nav() ) {
			$addcontent = $content;
			$content = str_replace('%postcontent%', $addcontent, $series_nav);
		}
	}
	return $content;
}

function add_series_wp_title( $title ) {
	global $org_domain;
	$series = single_series_title('', false);
	
	if ( !empty($series) ) {
		if ( !is_feed() )
			$title = __('Series: ',$org_domain) . $series . ' &laquo; ' . $title;
		else
			$title = __('Posts from the series: ',$org_domain) . $series . ' ('. get_bloginfo().')';
	}
	return $title;
}

//Roles and Capabilities Stuff
function orgSeries_roles() {
global $wp_roles, $org_domain;
$roles = array('administrator', 'editor');
$capability = 'manage_series';

	foreach ($roles as $role) {
		$wp_roles->add_cap($role, $capability, true);
	}
	return true;
}

##########ADD ACTIONS TO WP###########
//initialize plugin
function series_tax_init() {
	global $org_series_term, $org_series_type, $org_series_args;
	register_taxonomy($org_series_term, $org_series_type, $org_series_args);
	orgseries_admin_warnings();
}

//orgSeries dropdown nav js
function series_dropdown_js() {
 if ( SERIES_REWRITEON == 0 ) {
	?>
	<script lang='javascript'><!--
var seriesdropdown = document.getElementById("series");
    function onSeriesChange() {
		if ( seriesdropdown.options[seriesdropdown.selectedIndex].value > 0 ) {
			location.href = "<?php echo get_option('home'); ?>/?taxonomy=series&term="+seriesdropdown.options[seriesdropdown.selectedIndex].attributes.getNamedItem('class').value;
		}
    }
    seriesdropdown.onchange = onSeriesChange;
--></script>
	<?php
	} else {
	?>
	<script lang='javascript'><!--
var seriesdropdown = document.getElementById("series");
    function onSeriesChange() {
		if ( seriesdropdown.options[seriesdropdown.selectedIndex].value > 0 ) {
			location.href = "<?php echo get_option('home'); ?>/series/"+seriesdropdown.options[seriesdropdown.selectedIndex].attributes.getNamedItem('class').value;
		}
    }
    seriesdropdown.onchange = onSeriesChange;
--></script>
	<?php
	}
}

//remove series sub-menu item from edit posts menu
function unset_series_menu() {
  global $menu, $submenu;
  $index = 15;
  while ($submenu['edit.php'][$index]) {
	$submenuthere = 'edit-tags.php?taxonomy=' . SERIES_QUERYVAR;
	if ( in_array( $submenuthere, $submenu['edit.php'][$index] ) ) unset($submenu['edit.php'][$index]);
	$index++;
  }
}

//ADD in link to settings on plugin page.
function AddPluginActionLink( $links, $file ) {
		static $this_plugin;
		
		if( empty($this_plugin) ) $this_plugin = plugin_basename(__FILE__);

		if ( $file == $this_plugin ) {
			$settings_link = '<a href="' . admin_url( 'options-general.php?page='.SERIES_DIR.'/orgSeries-options.php' ) . '">' . __('Settings', $org_domain) . '</a>';
			array_unshift( $links, $settings_link );
		}

		return $links;
	}
	
//Add Admin warnings on plugin install for if OrgSeries settings  have not been initialized.
function orgseries_admin_warnings() {
	
	if ( !get_option('org_series_is_initialized') && !isset($_POST['submit']) ) {
		function orgseries_warning() {
			echo "
			<div id='orgseries-warning' class='updated fade'><p><strong>".__('Organize Series is almost ready.',$org_domain)."</strong> ".sprintf(__('You must <a href="%1$s">visit the Settings Page</a> for the options to be initialized.', $org_domain), 'options-general.php?page='.SERIES_DIR.'/orgSeries-options.php')."</p></div>
			";
		}
		add_action('admin_notices', 'orgseries_warning');
		return;
	}
}

//Load strings for the current locale (thanks to Benjamin Lupu for his help with this - http://benjaminlupu.net
function load_domain_strings() {

	global $plugin_path, $org_domain;
	
	// Get current locale (eg. fr_FR)
	$locale = get_locale();

	// Compose path to the strings file (.mo) to be loaded
	$mofile = $plugin_path . '/lang/' . $org_domain . '-' . $locale .'.mo';

	// Load strings corresponding to the current locale
	load_textdomain($org_domain,$mofile);
}

// Set the domain for L10N and load strings for the current locale
$org_domain = 'OrganizeSeries';
load_domain_strings();

add_action( 'wp_footer', 'series_dropdown_js', 1 );
add_action('admin_head', 'unset_series_menu', 1);
add_action( 'init', 'series_tax_init', 0 );
add_action('activate_' . SERIES_DIR . '/orgSeries.php','org_series_install');

//insert .css in header if needed
add_action('wp_head', 'orgSeries_header');

//add admin menu for selecting options
add_action('admin_menu', 'series_organize_options');

//add filter to automatically add the tag for showing other posts in a series in a single post of that series.  Conditional upon "autotags" being selected in the admin options menu.
add_action('the_content', 'add_series_post_list_box');

//add filter to automatically add the tag for showing the meta information for each post if it is part of a series (i.e.  What part in the series it is, what's the title of the series etc.).
add_filter('the_content', 'add_series_meta');
add_filter('get_the_excerpt', 'add_series_meta_excerpt',1);

//add filter to automatically add the series-post-navigation strip
add_action('the_content', 'series_nav_filter');
add_filter('wp_title', 'add_series_wp_title');

//filter for settings link on plugin page
add_filter( 'plugin_action_links', 'AddPluginActionLink', 10, 2 );
?>