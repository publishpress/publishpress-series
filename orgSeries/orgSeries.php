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
//TODO - CREATE A PAGE FOR THE SERIES TABLE OF CONTENTS AND DISPLAY LIST OF SERIES (using series toc display tag - pulling display options from orgseries settings)

function org_series_install() {
          global $org_series_version, $org_series_args, $org_series_term, $org_series_type, $wp_taxonomies, $wpdb;
         register_taxonomy($org_series_term, $org_series_type, $org_series_args);
         
		 //do test to see if older version of orgSeries exists and if so set oldversion number so that any necessary import changes can be done. 
		 if ( $options = get_option('org_series_options') && !( $oldversion = get_option('org_series_version') ) ) { //for versions prior to 2.0
			add_option('org_series_oldversion', '1.6');
		}
		
		if ( $oldversion = get_option('org_series_version') ) { //for versions after 2.0
			update_option('org_series_oldversion', $oldversion);
		} else { //for versions prior to 2.0
			add_option('org_series_oldverison', '1.6');
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
		add_option('series_icon_path', '');
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
}

##ADMIN-Write/Edit Post Script loader
add_action('admin_head','orgSeries_admin_script');
function orgSeries_admin_script() {
//load in the series.js script and set localization variables.
wp_register_script( 'ajaxseries', '/wp-content/plugins/orgSeries/series.js', array('listman'), '20071201' );
wp_localize_script('ajaxseries','seriesL10n',array(
	'add' => attribute_escape(__('Add')),
	'how' => __('Add the series name in this box')
	));
wp_print_scripts( 'ajaxseries' );
}
	
function get_series_posts( $ser_ID ) {  //was formerly get_cat_posts()...which is now of course deprecated.  TODO: Add "current" class for the post that is currently displayed on the page so people can tweak the way it displays -- REQUIRES adding class to the default .css as well.  IF THIS DOESN'T WORK - it might be better to use the get_objects_in_term() function in the taxonomy.php file.
 	global $post;
	$settings = get_option('org_series_options');
	$args = 'series=' . (int) $ser_ID;  //if doesn't work try category=
	$posts_in_series = get_posts( $args );
	$result = '';
	foreach($posts_in_series as $post) :
		setup_postdata($post); ?>
		<?php echo stripslashes($settings['before_title_post_page']); ?>
			<a href="<?php the_permalink(); ?>" rel="permalink"><?php the_title(); ?></a>
		<?php echo stripslashes($settings['after_title_post_page']); 
	endforeach; 
}

function wp_seriespost_check() {  //this checks if the post is a part of a series and returns an array with the cat_ID, category title and category description if it is and a value of 0 if it isn't.
	//deprecated - use get_the_series( $id = 0 ) instead now.
}

function wp_postlist_count() {  //counts the number of posts in the series the post belongs to IF it belongs to a series.  TODO: modify this in future versions so that a post can belong to multiple series.
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

function wp_series_part( $ser_post_id ) { //For a post that is part of a series, this function returns the value for what part this post is in the series.

	$serarray = get_the_series($ser_post_id);
	$part_key = SERIES_PART_KEY;
	$series_part = '';
	
	if (!empty($serarrray) ) {
		$series_part = get_post_meta($ser_post_id, $part_key, true);
	}
	
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
		$seriestitle = $series->name;
		$seriesdescription = $series->description;
		}
	}
	
	$post_part = wp_series_part($post->ID);
	if (isset($serID)) { ?>
	<?php echo stripslashes($settings['before_series_meta']); ?>
	<?php echo "This " . $settings['series_meta_word'] . " is part " . $post_part . " of " . wp_postlist_count() . " in the series, " . $seriestitle . "."; ?>
	<?php echo stripslashes($settings['after_series_meta']);
	}
}

## SERIES CATEGORY POSTS TEMPLATE TAG ##
## Place this tag in the loop.  It "discovers" the series a post belongs to and then echoes a list of other posts in that series. Place this tag in the loop. ##

function wp_postlist_display() { //TODO - change to make it in line with the new series options set up (%tokens% etc.).  Also, change to reflect new series_icon integration.
	$settings = get_option('org_series_options');
	
	$serarray = get_the_series();
	if (!empty($serarray) ) {
		foreach ($serarray as $series) {
			$serID = $series->term_id;
			$seriestitle = $series->name;
			$seriesdescription = $series->description;
		}
	}
	
	if (isset($serID)) : ?>
	<?php echo stripslashes($settings['beforelistbox_post_page']); ?>
	<?php echo stripslashes($settings['series_intro_text_post_page']); ?>
	<?php if (function_exists(get_cat_icon)) { //NEW SERIES ICON INTEGRATION NEEDS TO GO HERE.
		?> 
		<?php if ($settings['cat_icon_chk_post_page']) { ?>
		<div class="center">
		<?php get_cat_icon('cat=' . $ser_ID . '&fit_width=' . $settings['cat_icon_width_post_page'] . '&height=-1&expand=true'); ?>
		</div>
		 <?php } } ?>
	<?php if ($settings['text_chk_post_page']) { ?>
	
		<?php if ($settings['cat_title_chk_post_page']) { ?>
			<?php echo stripslashes($settings['before_series_title_post_page']); ?><?php echo '<a href="' . get_series_link(	$serID ) . '">' . $seriestitle . '</a>'; ?><?php echo stripslashes($settings['after_series_title_post_page']); ?>
		<?php } ?>
		<?php if ($settings['cat_description_cat_post_page']) { ?>
			<?php
				 echo stripslashes($settings['cat_before_description_post_page']);
				 echo $seriesdescription;
				 echo stripslashes($settings['cat_after_description_post_page']);
				 ?>
				 <?php }
	} ?>
	<?php echo stripslashes($settings['before_post_title_list_post_page']); ?>
	<?php get_series_posts($serID); ?>
	<?php echo stripslashes($settings['after_post_title_list_post_page']); ?>
	<?php echo stripslashes($settings['afterlistbox_post_page']); ?>
	<?php endif;
}
#########################################

##SERIES DISPLAY FUNCTION TAG##
##Place this tag in a custom page called category-xxx.php where xxx is the category number for the series category. When users view the category page for this series they will be presented with a list of all the available series they can read (sub-categories under the main series category). Also there are a number of parameters that can be adjusted depending on what you want to do: ##
function wp_serieslist_display_code($series) { //reusable function for display of series information
		$settings = get_option('org_series_options');
		$cat_title = $series->name;
		$cat_ID = $series->term_id;
		$cat_description = $series->description; 
				// LAYOUT AND DISPLAY ?>
		<?php echo stripslashes($settings['beforedisplay_cat_page']); ?>
			<?php if ($settings['cat_icon_chk_cat_page']) { ?>
		 		<?php if (function_exists(get_cat_icon)) { ?>
		 		<?php echo stripslashes($settings['before_cat_icon_cat_page']); ?>	
		 		<?php get_cat_icon('cat=' . $cat_ID . '&fit_width=' . $settings['cat_icon_width_cat_page'] . '&height=-1&expand=true');  ?>
		 		<?php echo stripslashes($settings['after_cat_icon_cat_page']); ?>
		 	<?php } } ?>
		 	
		 	<?php if ($settings['text_chk_cat_page']) { ?>
		 	
			 	<?php echo stripslashes($settings['before_catlist-content_cat_page']); ?>
			 	<?php if ($settings['cat_title_chk_cat_page']) { ?>
			 		<?php echo stripslashes($settings['beforetitle_cat_page']); ?><?php echo '<a href="' . get_series_link($cat_ID) . '">' . $cat_title . '</a>'; ?><?php echo stripslashes($settings['aftertitle_cat_page']); ?>
			 	<?php } ?>
			 	<?php if ($settings['cat_description_cat_page']) { ?>
			 		<?php echo stripslashes($settings['beforedescript_cat_page']); ?><?php echo $cat_description; ?><?php echo stripslashes($settings['afterdescript_cat_page']); ?>
			 	<?php } ?>
			 <?php echo stripslashes($settings['after_catlist-content_cat_page']); ?>
			 <?php } ?>
			 <hr style="clear: left; border:none" />
		  <?php echo stripslashes($settings['afterdisplay_cat_page']); ?>
<?php
}
 
function wp_serieslist_display() {  
	global $wpdb, $post;
	
	$settings = get_option('org_series_options');
			
	$num_per_page = $settings['perpage_cat_page'];
	
	####PAGING RELATED####
	if ($settings['paging_cat_page']==1) {
		//by default we show first page
		$pageNum = 1;
		
		//if $_GET['scpage'] defined, use it as page number
		if(isset($_GET['scpage'])) {
	    	$pageNum = $_GET['scpage'];
		}
	}

	$series_list = get_series('hide_empty=0');
	$num_objects = count($series_list->term_id); // Will return the number of series in the array which is needed for doing paging.
	
	if ($settings['paging_cat_page']==1) {
		//find out how many rows in database
		$get_numrows = $num_objects;
		
		//how many pages we have when using paging?
		$maxPage = ceil($get_numrows/$num_per_page);
		
		$self =  get_series_link($GLOBALS['series']); // TODO -  okay to do this (I think): On plugin init create a series called "TOC" and do excludes for that series in any function I don't want it called in (i.e. series list).  Then the table of contents will be located @ (...series_permalink...)/toc and on series .php I can do a 'if (is_series('toc')' for determining whether the series_list_display() gets done or not.  Alternatively, look at the permalink for series (cross reference how tags and categories work).  Is there a way of displaying the sereis toc when located at /blog_address/series/ (series are located at /blog_address/series/series_name)?
		
		//creating paging links
				if ($pageNum > 1) {
				    $tocpage = $pageNum - 1;
				    $prev = " <a href=\"$self?tocpage=$tocpage\">[Prev]</a> ";
				    
				    $first = " <a href=\"$self?tocpage=1\">[First Page]</a> ";
				    
				} else {
				    $prev  = '';       // we're on page one, don't enable 'previous' link
				    $first = ''; // nor 'first page' link
				}
			
			// print 'next' link only if we're not
			// on the last page
				if ($pageNum < $maxPage) {
				    $tocpage = $pageNum + 1;
				    $next = " <a href=\"$self?tocpage=$tocpage\">[Next]</a> ";
				    
				    $last = " <a href=\"$self?tocpage=$maxPage\">[Last Page]</a> ";
				    
				} else	{
				    $next = '';      // we're on the last page, don't enable 'next' link
				    $last = ''; // nor 'last page' link
				}
	}	
// fetch query arrays and display
	if ($settings['paging_cat_page']==1) { //do if paging is enabled via the plugin options
	$pagecheck = ($pageNum-1) * $num_per_page; //this will give me how many rows I want to skip in the array
	$pagebreak = '0'; //sets the var we'll use for breaking out of the foreach loop when we've reached the number of series we want per page.
	$startdisplay = '1'; //sets the var we'll use for indicating when we can start displaying array results.
	foreach ($series_list as $series) {  
	if ($startdisplay < $pagecheck) {
		$startdisplay++;
		continue; // This will continue the loop skipping the display code IF the offset isn't right.
		}
	if ($pagebreak > $num_per_page)
		break; //if we've reached the number we want on the page
		}
	wp_serieslist_display_code($series); //layout code
	$pagebreak++;
	} else {
		foreach ($series_list as $series) { //for when paging is disabled
		wp_serieslist_display_code($series);
		}
	}
			
// DISPLAY PAGING LINKS (if paging enabled)
	if ($settings['paging_cat_page'] == 1) {
		echo '<div align="center">' . $first . $prev . ' Showing page <strong>' . $pageNum . '</strong> of <strong>' . $maxPage . '</strong> pages ' . $next . $last . '</div>';
	}
	
}
#####Filter function for adding series post-list box to a post in that series####

function add_series_post_list_box($content) {
	
	$settings = get_option('org_series_options');
	
	if ($settings['auto_tag_toggle']) {
		if (is_single()) {
			$addcontent = $content;
			$content = wp_postlist_display() . $addcontent;
		}
	}
	
	return $content;
}

#####Filter function for adding series meta information to posts in series#####

function add_series_meta($content) {
	$settings = get_option('org_series_options');
	
	if($settings['auto_tag_seriesmeta_toggle']) {
	$content = wp_seriesmeta_write($postID) . $content;
	return $content;
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
function admin_ajax_series() { //TODO integrate series with the "roles" features of the admin.
	/*if ( !current_user_can( 'manage_categories' ) )
		die('-1');*/
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

##########ADD ACTIONS TO WP###########
//initialize plugin
register_taxonomy($org_series_term, $org_series_type, $org_series_args);
add_action('activate_orgSeries/orgSeries.php','org_series_install');

//add ajax for on-the-fly series adds
add_action('wp_ajax_add-series', 'admin_ajax_series');

//insert .css in header if needed
add_action('wp_head', 'orgSeries_header');

//add admin menu for selecting options
add_action('admin_menu', 'series_organize_options');

//add filter to automatically add the tag for showing other posts in a series in a single post of that series.  Conditional upon "autotags" being selected in the admin options menu.
add_action('the_content', 'add_series_post_list_box');

//add filter to automatically add the tag for showing the meta information for each post if it is part of a series (i.e.  What part in the series it is, what's the title of the series etc.).
add_action('the_content', 'add_series_meta');

//add action for admin-series.css
add_action('admin_head', 'orgSeries_admin_header');

//add filter for sort_series_page_options ...TODO: Check to see if this would work.
//add_filter('posts_orderby','sort_series_page_options');
?>