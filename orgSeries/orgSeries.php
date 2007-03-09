<?php
/*
Plugin Name: Organize Series Plugin
Plugin URI: http://www.unfoldingneurons.com/neurotic-plugins/organize-series-wordpress-plugin/
Version: 1.6
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

++Version 1.6: Fix and New Feature (March 9, 2007)
	1. BUG FIX: Blank screen/errors in the options panel when plugin installed in WordPress versions below 2.1.
	2. NEW FEATURE: You can now set how posts will be displayed on the series "table of contents" page (i.e. ascending or descending, ordered by date, author, title etc.)  This can be selected via the new options panel on the plugin options page.  (NOTE:  This appears to only work with WP 2.1+ for now - I'm still investigating if it works with other versions) 
	3. NEW TEMPLATE TAG:  "is_series()" This tag will check for if the displayed category archive page is a series category.  Returns true if it is, false if it isn't.

++Version 1.5: Enhancement Update (February 12, 2007)
	1. restructured functions so that the plugin is more forward thinking friendly. Main changes were in placing query calls in individual functions.
	2. Added a function for counting the number of posts in a series. (tag returns a value).  For manual insertion use the following tag where you want the number of posts in a series to be displayed: <?php echo wp_postlist_count(); ?>  NOTE - this tag must be included in the WordPress Loop.
	3. Added a function for writing series_meta information to the posts that belong to a series. This tag can be inserted automatically (at the top of the content) or manually depending on whether the auto-tag option is checked in the options panel.  For manual insertion use the following tag where you want the series meta information to be displayed: <?php wp_seriesmeta_write(); ?> NOTE - this tag must be included in the WordPress Loop.
	4. Added options for the html tags surrounding the series_meta and for the post description word. Added to the options panel.
	5. Added new .css code to the default file (.seriesmeta class)
	6. Split out the auto-tag toggle in the options panel so that the "post-list-box" auto-insert (the container displayed on single page views of posts belonging to series) and the "series-meta-box" auto-insert are individual settings rather than one master auto-tag toggle.
	7. Redid the layout of the admin options page for the plugin so it's organized a bit better and a "little" bit more prettier.  I recognize that more work still needs to be done.
	8. Added to the admin options page for the plugin a feed from unfoldingneurons.com that displays posts related to the Organize Series Wordpress Plugin so that users can see at a glance if there are any updates available.
	9. FIXED: You no longer have to have the main parent series category selected on posts in order for the plugin to output the series related displays properly.  
	10. ADDED: <?php wp_seriesmeta_write(); ?> This tag calls and displays the series meta information for a post (i.e. "This post is part x of x in the series, 'Title of Series Here'"). IT MUST be placed in the WordPress Loop for it to work properly.
	11. ADDED <?php wp_series_part($ser_post_id); ?> For a post that is part of a series, this function returns the value for what part this post is in the series.  Note for this to work properly there must a parameter added for the $ser_post_id variable.  If the tag is used within the WordPress Loop you can use the following to echo what part the post is if it is part of a series (note: the function already checks if the post is part of a series): <?php echo wp_series_part($post->ID); ?>
	
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

function post_cat_query( $cat_ID ) {
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
		return $get_posts_in_cat_result;
}
	
function get_cat_posts( $cat_ID ) {
 	
	$settings = get_option('org_series_options');
	$get_posts_in_cat_result = post_cat_query($cat_ID);
	while ($posts_in_cat_row = mysql_fetch_assoc($get_posts_in_cat_result)) {	
	 $post_title = $posts_in_cat_row['post_title'];
	 $postID = $posts_in_cat_row['ID'];	
				
		echo $settings['before_title_post_page'] . '<a href="' . get_permalink($postID) . '">' . $post_title . '</a>' . $settings['after_title_post_page'];
		}		
}

function is_series() {
	//this checks if the category archive page being displayed is a subcategory of the parent series category.  If it is it returns a value of true otherwise false.
	global $wp_query;
	$cat_obj = $wp_query->get_queried_object();
	$catid = $cat_obj->cat_ID;
	$cat = $cat_obj->category_parent;
	
	if (is_category()) {
		$settings = get_option('org_series_options');
			
		if ($cat == $settings['series_cats']) {
			return true;
		} else {
	
	return false; } }
}

function wp_seriespost_check() {  //this checks if the post is a part of a series and returns an array with the cat_ID, category title and category description if it is and a value of 0 if it isn't.
	$settings = get_option('org_series_options');
	
	foreach(get_the_category() as $cat) {
		if ($cat->category_parent == $settings['series_cats']) {
			$catarray = array('id' => $cat->cat_ID, 'title' => $cat->cat_name, 'description' => $cat->category_description); 
		}
	}
	 if (isset($catarray['id'])) { return $catarray;  } else { $catarray = 0; return $catarray; }
}

function wp_postlist_count() {  //counts the number of posts in the series the post belongs to IF it belongs to a series.
	$catarray = wp_seriespost_check();
	if ($catarray !=0) {
		$catID = $catarray['id'];
		$postlist_count = post_cat_query($catID);
		$postlist_count = mysql_num_rows($postlist_count);
	} else {
		$postlist_count = 0;
	}
	return $postlist_count;
}

function wp_series_part( $ser_post_id ) { //For a post that is part of a series, this function returns the value for what part this post is in the series.
	$catarray = wp_seriespost_check();
	if ($catarray !=0) {
		$catID = $catarray['id'];
	}
	if (isset($catID)) {
		$post_part = post_cat_query($catID);
		$count = 0;
		while ($part = mysql_fetch_assoc($post_part)) {
		$count++;
			if ($part['ID'] == $ser_post_id) {
				break;
			}
		}
		return $count;
	}
}

##TAG FOR INSERTING meta info about the series the post belongs to IF it belongs to a series.  In other words this will show up on the index loop and the archive loops (if auto-tag insert is enabled in the options panel). 

function wp_seriesmeta_write() {
	global $wp_query; 
	
	$settings = get_option('org_series_options');
	
	$catarray = wp_seriespost_check();
	if ($catarray != 0) {
	$catID = $catarray['id'];
	$seriestitle = $catarray['title'];
	$seriesdescription = $catarray['description'];
	}
	$postID = $wp_query->post->ID;
	$post_part = wp_series_part($postID);
	if (isset($catID)) { ?>
	<?php echo stripslashes($settings['before_series_meta']); ?>
	<?php echo "This " . $settings['series_meta_word'] . " is part " . $post_part . " of " . wp_postlist_count() . " in the series, " . $seriestitle . "."; ?>
	<?php echo stripslashes($settings['after_series_meta']);
	}
}


## SERIES CATEGORY POSTS TEMPLATE TAG ##
## Place this tag in the loop.  It "discovers" the series a post belongs to and then echoes a list of other posts in that category. Place this tag in the loop. ##

function wp_postlist_display() { 
	$settings = get_option('org_series_options');
	
	$catarray = wp_seriespost_check();
	if ($catarray != 0) {
	$catID = $catarray['id'];
	$seriestitle = $catarray['title'];
	$seriesdescription = $catarray['description'];
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
	if ($settings['paging_cat_page']==1) {
		//by default we show first page
		$pageNum = 1;
		
		//if $_GET['scpage'] defined, use it as page number
		if(isset($_GET['scpage'])) {
	    	$pageNum = $_GET['scpage'];
		}
		
		//counting the offset
		$offset = ($pageNum - 1) * $num_per_page;
	
	}

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
	
	if ($settings['paging_cat_page']==1) {
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
	 
	if ($settings['paging_cat_page']==1) {
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

######Filter function for selecting how posts are displayed on the series posts table of contents page.#############

function sort_series_page_options($q) {
	$settings = get_option('org_series_options');
	$orderby = 'post_' . $settings['order_by_series_page'] . ' ';
	$order = $settings['order_series_page'];
	if(is_series()) {
		$q = $orderby.$order;
		return $q;
	}
	return $q;
}

##########ADD ACTIONS TO WP###########

//insert .css in header if needed
add_action('wp_head', 'orgSeries_header');

//add admin menu for selecting options
add_action('admin_menu', 'series_organize_options');

//add filter to automatically add the tag for showing other posts in a series in a single post of that series.  Conditional upon "autotags" being selected in the admin options menu.
add_action('the_content', 'add_series_post_list_box');

//add filter to automatically add the tag for showing the meta information for each post if it is part of a series (i.e.  What part in the series it is, what's the title of the series etc.).
add_action('the_content', 'add_series_meta');

//add filter for sort_series_page_options
add_filter('posts_orderby','sort_series_page_options');

?>