<?php
/*
Plugin Name: Organize Series Plugin
Plugin URI: http://www.unfoldingneurons.com/neurotic-plugins/organize-series-wordpress-plugin/
Version: 2.0
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
//"Organize Series Plugin" is copyright (c) 2007 Darren Ethier. This program is free software; you can redistribute it and/or
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
// *****************************************************************
######################################

######################################
/* Changelog
++Version 2.0
- Integrates with the new taxonomy system introduced in WordPress 2.3
-  Version 2.0 of Organize Series will only work with WordPress 2.3 and greater.
++Versions .5beta-1.6.3 Changelogs are no longer published in this file.  I also no longer support these versions as there were some pretty significant changes in the plugin structure going from 1.6.3 to 2.0 and I simply do not have the time to support the two variations.

*/
#####################################

#####################################
// TO-DO (Feature additions to add in future versions) --moved to plugin page.
#####################################

/*
INITIAL INSTALL OF PLUGIN
*/
$org_series_version = "2.0";
$org_series_args = array('hierarchical' => false, 'update_count_callback' => '_update_post_term_count');
$org_series_term = "series";
$org_series_type = "post";
require (ABSPATH . '/wp-content/plugins/orgSeries/series.php');
require (ABSPATH . '/wp-content/plugins/orgSeries/orgSeries-edit.php');
require (ABSPATH . '/wp-content/plugins/orgSeries/series-icon.php');
//TODO - CREATE A PAGE FOR THE SERIES TABLE OF CONTENTS AND DISPLAY LIST OF SERIES (using series toc display tag - pulling display options from orgseries settings) - CHECK how the category.php template works in the default wp install and see if there is code that could be modified for a default series.php template (will want to do a check for if the request address is ....www.myaddress.com/series to see if the series TOC should be displayed OR the regular series template (similar to how category templates are displayed.
//TODO - Create a way for the user to select how the series posts are displayed on the series archive page:  Options:  chronologically (ASC||DESC), series-part (ASC||DESC).

function org_series_install() {
          global $org_series_version, $org_series_args, $org_series_term, $org_series_type, $wp_taxonomies, $wpdb;
         register_taxonomy($org_series_term, $org_series_type, $org_series_args);
		 orgSeries_roles(); 
         
		 //TODO - do test to make sure the WordPress version is greater than or equal to 2.3 and gracefully "die" if it isn't (outputting an error message using WP_Error?
		 //do test to see if older version of orgSeries exists and if so set oldversion number so that any necessary import changes can be done. 
		 if ( $options = get_option('org_series_options') && !( $oldversion = get_option('org_series_version') ) ) { //for versions prior to 2.0
			add_option('org_series_oldversion', '1.6');
		}
		
		if ( $oldversion = get_option('org_series_version') ) { //for versions after 2.0
			update_option('org_series_oldversion', $oldversion);
		} else { //for versions prior to 2.0
			add_option('org_series_oldversion', '1.6');
		}
		
		add_option("org_series_version", $org_series_version);
		
		//create table for series icons
		$table_name = $wpdb->prefix . "orgSeriesIcons";
		if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
			$sql = "CREATE TABLE " . $table_name . " (
				term_id INT NOT NULL,
				icon VARCHAR(100) NOT NULL,
				PRIMARY KEY term_id (term_id)
			);";
			require_once(ABSPATH . 'wp-admin/upgrade-functions.php');
			dbDelta($sql);
		}
		add_option('series_icon_path', '');
		add_option('series_icon_url', '');
		add_option('series_icon_filetypes', 'jpg gif jpeg png');
}

//*** Add .css to header if enabled via options ***//
function orgSeries_header() {
	$settings = get_option('org_series_options');
	$plugin_path = get_settings('siteurl') . '/wp-content/plugins/orgSeries';
	if ($settings['custom_css']) {
		$csspath = $plugin_path."/orgSeries.css";
		$text = '<link rel="stylesheet" href="' . $csspath . '" type="text/css" media="screen" />';
	} else {
		$text = '';
	}
	
	echo $text;
}

function orgSeries_admin_header() {
	$plugin_path = get_settings('siteurl') . '/wp-content/plugins/orgSeries';
	$csspath = $plugin_path . "/orgSeries-admin.css";
	$text = '<link rel="stylesheet" href="' . $csspath . '" type="text/css" media="screen" />';
	echo $text;
}

###### CREATE ADMIN PANEL FUNCTION #######
function series_organize_options() {
	if (function_exists('add_options_page')) 
		add_options_page('Organize Series Options', 'Series Options', 9, get_settings('siteurl') . '/wp-content/plugins/orgSeries/orgSeries-options.php'); 
	if (function_exists('add_management_page'))	
		add_management_page('Organize Series Management', 'Manage Series', 9, get_settings('siteurl') . '/wp-content/plugins/orgSeries/orgSeries-manage.php');
}

##ADMIN-Write/Edit Post Script loader
add_action('admin_head','orgSeries_admin_script'); 
//add_action filter for the manage_series page...
function orgSeries_admin_script() {
//load in the series.js script and set localization variables.
global $pagenow;
	if (isset($_GET['page']))
		$pagenow = $_GET['page'];
	if ('post-new.php' == $pagenow || 'post.php' == $pagenow) {
		wp_register_script( 'ajaxseries', '/wp-content/plugins/orgSeries/series.js', array('listman'), '20071201' );
		wp_localize_script('ajaxseries','seriesL10n',array(
			'add' => attribute_escape(__('Add')),
			'how' => __('Select "Not...series" to remove any series data from post')
			));
		wp_print_scripts( 'ajaxseries' );
	}
	
	if ('orgSeries/orgSeries-manage.php' == $pagenow)
		orgSeries_manage_script();
}

function orgSeries_manage_script() {
wp_register_script( 'admin-series', '/wp-content/plugins/orgSeries/manageseries.js',array('listman'), '20070125' );
wp_print_scripts('admin-series');
}

function get_cat_posts( $ser_ID ) { //deprecated: see get_series_posts()
		get_series_posts( $ser_ID );
}

function get_series_posts( $ser_ID ) {  //was formerly get_cat_posts()...which is now of course deprecated.  TODO - order the posts that are called by their part number.
 	global $post;
	$cur_id = $post->ID; //to get the id of the current post being displayed.
	if (!isset($ser_ID)) {
		$serarray = get_the_series();
		if (!empty($serarray) ) {
			foreach ($serarray as $series) {
				$ser_ID = $series->term_id;
			}
		}
	}
	$settings = get_option('org_series_options');
	$series_post = get_objects_in_term($ser_ID, 'series');
	$posts_in_series = array();
	$posts_in_series = get_series_order($series_post, 0, FALSE);
	$result = '';
	foreach($posts_in_series as $seriespost) {
		if ($cur_id == $seriespost['id']) {
			$result .= token_replace(stripslashes($settings['series_post_list_currentpost_template']), 'other', $seriespost['id']);
			continue;
		}
		$result .= token_replace(stripslashes($settings['series_post_list_post_template']), 'other', $seriespost['id']);
	}
	return $result;
}

function wp_seriespost_check() {  //this checks if the post is a part of a series and returns an array with the cat_ID, category title and category description if it is and a value of 0 if it isn't.
	//deprecated - use get_the_series( $id = 0 ) instead now.
	return get_the_series();
}

function wp_postlist_count() {  //counts the number of posts in the series the post belongs to IF it belongs to a series.  
	$serarray = get_the_series();
	if (!empty($serarray)) {
		foreach ($serarray as $series) {
		$postlist_count = $series->count;
		}
	} else {
		$postlist_count = 0;
	}
	return $postlist_count;
}

function wp_series_part( $id = 0 ) { //For a post that is part of a series, this function returns the value for what part this post is in the series.
	global $post;
	//$post = &get_post($id);
	$ser_post_id = $id;
	$part_key = SERIES_PART_KEY;
	$series_part = get_post_meta($ser_post_id, $part_key, true);
	return $series_part;
}

##TAG FOR INSERTING meta info about the series the post belongs to IF it belongs to a series.  In other words this will show up on the index loop and the archive loops (if auto-tag insert is enabled in the options panel). 

function wp_seriesmeta_write() { //TODO have this customizable via %tokens% rather than the existing options setup. 
	global $post; 
	$settings = get_option('org_series_options');
	$serarray = get_the_series();
	if (!empty($serarray) ) {
		foreach ($serarray as $series) {
		$serID = $series->term_id; 
		}
	}
	
	if (isset($serID)) { 
		$series_meta = token_replace(stripslashes($settings['series_meta_template']), 'other', $serID);
		return $series_meta;
	
	}
	
	return false;
}

## SERIES CATEGORY POSTS TEMPLATE TAG ##
## Place this tag in the loop.  It "discovers" the series a post belongs to and then echoes a list of other posts in that series. Place this tag in the loop. ##

function wp_postlist_display() { //TODO - change to make it in line with the new series options set up (%tokens% etc.).  Also, change to reflect new series_icon integration.
	$settings = get_option('org_series_options');
	$serarray = get_the_series();
		if (!empty($serarray)) {
			foreach ($serarray as $series) {
				$serID = $series->term_id;
			}
		}
	if (isset($serID)) {
		$postlist = token_replace(stripslashes($settings['series_post_list_template']), 'post-list', $serID);
		return $postlist;
	}
	
	return false;
}
#########################################

##SERIES DISPLAY FUNCTION TAG##
##Place this tag in a custom page called category-xxx.php where xxx is the category number for the series category. When users view the category page for this series they will be presented with a list of all the available series they can read (sub-categories under the main series category). Also there are a number of parameters that can be adjusted depending on what you want to do: ##
function wp_serieslist_display_code($series) { //reusable function for display of series information
		$settings = get_option('org_series_options');
		$serID = $series->term_id;
		if (isset($serID)) {
			$series_display = token_replace(stripslashes($settings['series_table_of_contents_box_template']), 'other', $serID);
			return $series_display;
		}
		return false;
}
 
function wp_serieslist_display() {  
	global $wpdb, $post;
	$settings = get_option('org_series_options');
	$series_list = get_series('hide_empty=0');
	
	foreach ($series_list as $series) {  
	wp_serieslist_display_code($series); //layout code
	}
}
#####Filter function for adding series post-list box to a post in that series####

function add_series_post_list_box($content) {
	
	$settings = get_option('org_series_options');
	
	if ($settings['auto_tag_toggle']) {
		if (is_single()) {
			$postlist = wp_postlist_display();
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
	$series_meta = wp_seriesmeta_write($postID);
	$addcontent = $content;
	$content = str_replace('%postcontent%', $addcontent, $series_meta);
	
	}
	return $content;
}

//series navigation strip on single-post display pages.
function wp_series_nav($series_ID, $next = TRUE, $customtext = FALSE, $display = FALSE) {
	global $post;
	$cur_id = $post->ID;
	$cur_part = get_post_meta($cur_id, SERIES_PART_KEY, true);
	$setttings = get_option('org_series_options');
	$custom_next = $settings['series_nextpost_nav_custom_text'];
	$custom_prev = $settings['series_prevpost_nav_custom_text'];
	
	if (!isset($series_ID)) {
		$series = get_the_series();
		if (!empty($series) ) {
			foreach ($series as $ser) {
				$series_ID = $series->term_ID;
			}
		}
	}
	$series_posts = get_objects_in_term($series_ID, 'series');
	$posts_in_series = array();
	$posts_in_series = get_series_order($series_posts, $cur_id);
	$result = '';
	
	foreach ($posts_in_series as $seriespost) {
		if ($next) {
			if ($seriespost['part'] > $cur_part && ($seriespost['part'] - $cur_part) > 1) {
					continue;
				} else {
					if ($customtext) $title = $custom_next;
						else $title = get_the_title($seriespost['id']);
					$link = get_permalink($seriespost['id']);
					$result .= '<a href="' . $link . '" title="' . $title . '">' . $title . '</a>';
					}
		}
		
		if (!next) {
			if ($cur_part > $seriespost['part'] && ($cur_part - $seriespost['part']) > 1) {
				continue;
				} else {
					if ($customtext) $title = $custom_prev;
						else $title = get_the_title($seriespost['id']);
					$link = get_permalink($seriespost['id']);
					$result .= '<a href="' . $link . '" title="' . $title . '">' . $title . '</a>';
				}
		}
	}
		if ($display) echo $result;
			else return $result;	
}

//filter for assembling the navigation strip
function wp_assemble_series_nav() {
	$settings = get_option('org_series_options');
	$series = get_the_series();
		if (!empty($series)) {
			foreach ($series as $ser) {
				$series_id = $ser->term_id;
			}
		}
		if (isset($series_id)) {
			$nav = token_replace(stripslashes($settings['series_post_nav_template']), 'other', $series_id);
			return $nav;
		}
	
	return FALSE;
}
		
//filter function for showing the navigation strip for posts that are part of a series  on the page of a post that is part of a series.
function series_nav_filter($content) {
	$settings = get_option('org_series_options');
	if (is_single()) {
		if($settings['auto_tag_toggle']) {
			$series_nav = wp_assemble_series_nav();
			$addcontent = $content;
			$content = str_replace('%postcontent%', $addcontent, $series_nav);
		}
	}
	return $content;
}
######Filter function for selecting how posts are displayed on the series posts table of contents page.############# // TODO: CHECK TO SEE IF WILL WORK...I THINK THAT THIS WON'T WORK NOW...

function sort_series_page_options($q) {
	$settings = get_option('org_series_options');
	$orderby = 'post_' . $settings['order_by_series_page'] . ' ';
	$order = $settings['order_series_page'];
	if(is_series() && !is_feed()) {
		$q = $orderby.$order;
		return $q;
	}
	return $q;
}

######ON THE FLY ADD SERIES########
function admin_ajax_series() { 
	if ( !current_user_can( 'manage_series' ) )
		die('-1');
	global $wp_taxonomies;
	//$series_test = $wp_taxonomies['series'];
	$name = $_POST['newseries'];
	$x = new WP_Ajax_Response();
	$series_name = trim($name);
	if ( !$series_nicename = sanitize_title($series_name) )
		die('0');
	if ( !$series_id = series_exists( $series_name ) )
		$series_id = wp_create_single_series( $series_name );
	$series_name = wp_specialchars(stripslashes($series_name));
	$x->add( array(
		'what' => 'series',
		'id' => $series_id,
		'data' => "<li id='series-$series_id'><label for='in-series-$series_id' class='selectit'><input value='$series_id' type='radio' checked='checked' name='post_series' id='in-series-$series_id' /> $series_name</label></li>"
	) );
	$x->send();
}

//AJAX for manage series page (Manage->Series)
function admin_ajax_series_add() {
	if (!current_user_can( 'manage_series' ) )
		die('-1');
	if (!$series = wp_insert_series( $_POST, $_FILES['series_icon'] ) )
		die('0');
	if ( !$series = get_orgserial( $series ) )
		die('0');
	$series_full_name = $series->name;
	$series_full_name = attribute_escape($series_full_name);
	$x = new WP_Ajax_Response();
	$x->add( array(
		'what' => 'serial',
		'id' => $series->term_ID,
		'data' => _series_row( $series ),
		'supplemental' => array('name' => $series_full_name, 'show-link' => sprintf(__('Series <a href="#%s">%s</a> added' ), "serial-$series->term_ID", $series_full_name))
		) );
		$x->send();
}

//delete series ajax
function admin_ajax_delete_series() {
	$id = (int) $_POST['id'];
	if ( !current_user_can( 'manage_series' ) )
		die('-1');
	
	if ( wp_delete_series( $id ) )
		die('1');
	else die ('0');
}

##########ADD ACTIONS TO WP###########
//initialize plugin
register_taxonomy($org_series_term, $org_series_type, $org_series_args);
add_action('activate_orgSeries/orgSeries.php','org_series_install');

//add ajax for on-the-fly series adds
add_action('wp_ajax_add-series', 'admin_ajax_series');
add_action('wp_ajax_add-serial', 'admin_ajax_series_add');
add_action('wp_ajax_delete-serial', 'admin_ajax_delete_series');

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

//add action for admin-series.css
add_action('admin_head', 'orgSeries_admin_header');

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
//add filter for sort_series_page_options ...TODO: Check to see if this would work. Don't think I'll add this in ver 2.0
//add_filter('posts_orderby','sort_series_page_options');
?>