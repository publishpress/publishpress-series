<?php
/*
Plugin Name: Organize Series Plugin
Plugin URI: http://www.unfoldingneurons.com/neurotic-plugins/organize-series-wordpress-plugin/
Version: 1.0
Description: This plugin adds a number of features to wordpress that enable you to easily write and organize a series of posts and display the series dynamically in your blog. This plugin also makes use of (optionally) the <a href="http://devcorner.georgievi.net/wp-plugins/wp-category-icons/">Category Icons</a> plugin by <a href="http://devcorner.georgievi.net/">Ivan Georgiev</a>. As far as I can tell this plugin is compatible with 1.5+ (including 2.1). 
Author: Darren Ethier
Author URI: http://www.unfoldingneurons.com
*/

### INSTALLATION/USAGE INSTRUCTIONS ###
//	Installation and/or usage instructions for the Organize Series Plugin
//	can be found at http://www.unfoldingneurons.com/neurotic-plugins/organize-series-wordpress-plugin/
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

++Version. 1.0: Minor Fix and Stable release
	- fixed incorrect links to help documents on plugin page
	- fixed incorrect "</div>" location that affected display of the post list in the series display box when the category icon display option was unchecked.
	- fixed default .css for the series list box that caused the category icon to go beyond the box borders if the category icon was set for a width larger than the box.  

++Version .65R1: One step up from the first version - 1st release.
	- Added admin options menu for plugin allowing you to customize alot of the display of the plugin
	- Added custom .css file (orgSeries.css) for plugin
	- Seperated plugin admin options from main plugin file for clearer architecture.
	- Defined variables/functions better to match their purpose (and to make future upgrades more seamless).
	- Did a pre-check of code for 2.1 compatability (but DID NOT test on 2.1).
	- Added example custom category template (category-xx.php) for the series display page addition to themes.
	- Created a plugin page and added Installation Instructions.
	- Auto-tag insertion on is_single() pages so that the series related container will "auto-magically" appear.  This can be disabled via the options menu. 
	- Released plugin to the wild...
	
++Version .5 Beta:  Very first crude version that is a knockoff of a private plugin I created for my Sermons site (www.unashamedsermons.com).  I just quickly hashed this off to be able to use the features from my earlier plugin in designing my Church Website

*/
#####################################

#####################################
// TO-DO (Feature additions to add in future versions) --moved to plugin page.
#####################################

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
	

###### CREATE ADMIN PANEL FUNCTION #######
function series_organize_options() {
	if (function_exists('add_options_page')) 
		add_options_page('Organize Series Options', 'Series Options', 9, get_settings('siteurl') . '/wp-content/plugins/orgSeries/orgSeries-options.php'); 
	}

function get_cat_posts( $cat_ID ) {
	
	global $wpdb;
		
	$settings = get_option('org_series_options');
			
		$get_posts_in_cat = "SELECT $wpdb->posts.ID, $wpdb->posts.post_title, ";
		$get_posts_in_cat .= "$wpdb->post2cat.post_id, $wpdb->post2cat.category_id ";	
		$get_posts_in_cat .= "FROM $wpdb->posts, $wpdb->post2cat ";
		$get_posts_in_cat .= "WHERE $wpdb->posts.ID = $wpdb->post2cat.post_ID ";
		$get_posts_in_cat .= "AND $wpdb->post2cat.category_id = '$cat_ID' ";
		$get_posts_in_cat .= "AND $wpdb->posts.post_status = 'publish' ";
		$get_posts_in_cat .= "ORDER BY $wpdb->posts.post_date ";
		$get_posts_in_cat .= "ASC ";
				
		$get_posts_in_cat_result = mysql_query($get_posts_in_cat);

	while ($posts_in_cat_row = mysql_fetch_assoc($get_posts_in_cat_result)) {	
	  $post_title = $posts_in_cat_row['post_title'];
		$postID = $posts_in_cat_row['ID'];	
				
		echo $settings['before_title_post_page'] . '<a href="' . get_permalink($postID) . '">' . $post_title . '</a>' . $settings['after_title_post_page'];
		}		
}

## SERIES CATEGORY POSTS TEMPLATE TAG ##
## Place this tag in the loop.  It "discovers" the series a post belongs to and then echoes a list of other posts in that category. Place this tag in the loop. ##

function wp_postlist_display() { 
	$settings = get_option('org_series_options');
	
	foreach((get_the_category()) as $cat) {
		if ($cat->category_parent == $settings['series_cats']) {
			$catID = $cat->cat_ID;
			$seriestitle = $cat->cat_name;
			$seriesdescription = $cat->category_description; }
	}
	
	if (isset($catID)) : ?>
	<?php echo stripslashes($settings['beforelistbox_post_page']); ?>
	<?php echo stripslashes($settings['series_intro_text_post_page']); ?>
	<?php if (function_exists(get_cat_icon)) {
		?> 
		<?php if ($settings['cat_icon_chk_post_page']) { ?>
		<div class="center">
		<?php get_cat_icon('cat=' . $catID . '&fit_width=' . $settings['cat_icon_width_post_page'] . '&height=-1&expand=true'); ?>
		</div>
		 <?php } } ?>
	<?php if ($settings['text_chk_post_page']) { ?>
	
		<?php if ($settings['cat_title_chk_post_page']) { ?>
			<?php echo stripslashes($settings['before_series_title_post_page']); ?><?php echo '<a href="' . get_category_link(	$catID ) . '">' . $seriestitle . '</a>'; ?><?php echo stripslashes($settings['after_series_title_post_page']); ?>
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
	<?php get_cat_posts($catID); ?>
	<?php echo stripslashes($settings['after_post_title_list_post_page']); ?>
	<?php echo stripslashes($settings['afterlistbox_post_page']); ?>
	<?php endif;
}
#########################################

##SERIES DISPLAY FUNCTION TAG##
##Place this tag in a custom page called category-xxx.php where xxx is the category number for the series category. When users view the category page for this series they will be presented with a list of all the available series they can read (sub-categories under the main series category). Also there are a number of parameters that can be adjusted depending on what you want to do: ##

 
function wp_serieslist_display() {
	global $wpdb;
	
	$settings = get_option('org_series_options');
		
	$parent_cat = $settings['series_cats'];
	$num_per_page = $settings['perpage_cat_page'];
	
	####PAGING RELATED####
	if ($settings['paging_cat_page']) {
		//by default we show first page
		$pageNum = 1;
		
		//if $_GET['scpage'] defined, use it as page number
		if(isset($_GET['scpage'])) {
	    	$pageNum = $_GET['scpage'];
		}
		
		//counting the offset
		$offset = ($pageNum - 1) * $num_per_page;
	
	}

	//redefine parent_cat and num_per_page variables so easily insert into query
		
	$get_subcats = "SELECT $wpdb->categories.cat_ID, $wpdb->categories.cat_name, $wpdb->categories.category_description, $wpdb->categories.category_parent, $wpdb->categories.category_count, ";
	$get_subcats .= "$wpdb->posts.ID, $wpdb->posts.post_date, ";
	$get_subcats .= "$wpdb->post2cat.post_id, $wpdb->post2cat.category_id ";
	$get_subcats .= "FROM $wpdb->categories, $wpdb->posts, $wpdb->post2cat ";
	$get_subcats .= "WHERE $wpdb->posts.ID = $wpdb->post2cat.post_ID ";
	$get_subcats .= "AND $wpdb->post2cat.category_id = $wpdb->categories.cat_ID  ";
	$get_subcats .= "AND $wpdb->categories.category_parent = '$parent_cat' ";
	$get_subcats .= "GROUP BY $wpdb->categories.cat_ID ORDER BY $wpdb->posts.post_date ";
	$get_subcats .= "DESC ";
			
	$get_subcats_result = mysql_query($get_subcats);
	
	if ($settings['paging_cat_page']) {
		//find out how many rows in database
		$get_numrows = mysql_num_rows($get_subcats_result);
		
		//how many pages we have when using paging?
		$maxPage = ceil($get_numrows/$num_per_page);
		
		$self =  get_category_link($GLOBALS['cat']);
		
		
		//creating paging links
				if ($pageNum > 1) {
				    $scpage = $pageNum - 1;
				    $prev = " <a href=\"$self?scpage=$scpage\">[Prev]</a> ";
				    
				    $first = " <a href=\"$self?scpage=1\">[First Page]</a> ";
				    
				} else {
				    $prev  = '';       // we're on page one, don't enable 'previous' link
				    $first = ''; // nor 'first page' link
				}
			
			// print 'next' link only if we're not
			// on the last page
				if ($pageNum < $maxPage) {
				    $scpage = $pageNum + 1;
				    $next = " <a href=\"$self?scpage=$scpage\">[Next]</a> ";
				    
				    $last = " <a href=\"$self?scpage=$maxPage\">[Last Page]</a> ";
				    
				} else	{
				    $next = '';      // we're on the last page, don't enable 'next' link
				    $last = ''; // nor 'last page' link
				}
	}	

// fetch query arrays and display
	 
	if ($settings['paging_cat_page']) {
		$get_subcats .= "LIMIT $offset, $num_per_page ";
	}
	$get_subcats_result2 = mysql_query($get_subcats);
	?>
	<?php while ($subcats_row = mysql_fetch_assoc($get_subcats_result2)) {
		$cat_title = $subcats_row['cat_name'];
		$cat_ID = $subcats_row['cat_ID'];
		$cat_description = $subcats_row['category_description']; 
				// LAYOUT AND DISPLAY ?>
		<?php echo stripslashes($settings['beforedisplay_cat_page']); ?>
			<?php if ($settings['cat_icon_chk_cat_page']) { ?>
		 		<?php if (function_exists(get_cat_icon)) { ?>
		 		<?php echo stripslashes($settings['before_cat_icon_cat_page']); ?>	
		 		<?php get_cat_icon('cat=' . $cat_ID . '&fit_width=' . $settings['cat_icon_width_cat_page'] . '&height=-1&expand=true');  } ?>
		 		<?php echo stripslashes($settings['after_cat_icon_cat_page']); ?>
		 	<?php } ?>
		 	
		 	<?php if ($settings['text_chk_cat_page']) { ?>
		 	
			 	<?php echo stripslashes($settings['before_catlist-content_cat_page']); ?>
			 	<?php if ($settings['cat_title_chk_cat_page']) { ?>
			 		<?php echo stripslashes($settings['beforetitle_cat_page']); ?><?php echo '<a href="' . get_category_link($cat_ID) . '">' . $cat_title . '</a>'; ?><?php echo stripslashes($settings['aftertitle_cat_page']); ?>
			 	<?php } ?>
			 	<?php if ($settings['cat_description_cat_page']) { ?>
			 		<?php echo stripslashes($settings['beforedescript_cat_page']); ?><?php echo $cat_description; ?><?php echo stripslashes($settings['afterdescript_cat_page']); ?>
			 	<?php } ?>
			 <?php echo stripslashes($settings['after_catlist-content_cat_page']); ?>
			 <?php } ?>
			 <hr style="clear: left; border:none" />
		  <?php echo stripslashes($settings['afterdisplay_cat_page']); ?><?php  } ?>
<?php
// DISPLAY PAGING LINKS (if paging enabled)
if ($settings['paging_cat_page']) {
	echo '<div align="center">' . $first . $prev . ' Showing page <strong>' . $pageNum . '</strong> of <strong>' . $maxPage . '</strong> pages ' . $next . $last . '</div>';
}
	
}

#####Filter function for adding series post-list box to a post in that series####

function add_series_post_list_box($content) {
	
	$settings = get_option('org_series_options');
	
	if ($settings['auto_tag_toggle']) {
		if ((in_category($settings['series_cats']) && is_single())) {
			$addcontent = $content;
			$content = wp_postlist_display() . $addcontent;
		}
	}
	
	return $content;
}

##########ADD ACTIONS TO WP###########

//insert .css in header if needed
add_action('wp_head', 'orgSeries_header');

//add admin menu for selecting options
add_action('admin_menu', 'series_organize_options');

//add filter to automatically add the tag for showing other posts in a series in a single post of that series.  Conditional upon "autotags" being selected in the admin options menu.
add_action('the_content', 'add_series_post_list_box');
?>