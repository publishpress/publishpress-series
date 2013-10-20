<?php
/**
 * This file contains all the functions that users and theme developers can use to add series related information in the blog/theme.  IF it is desired that there be more control on the display of series related information it is important to disable the auto-tag option for that particular function in the series options page of the WordPress admin.  Functions that are toggable in this way will be indicated in the comments.  Usage instructions are given in more detail in the orgSeries Usage Tips series (http://UnfoldingNeurons.com/series/organize-series-usage-tips).
 * THEME AUTHORS/POWER USERS NOTE:  if you see AUTOTAG in the function description that means that you need to disable the corresponding autotag toggle in the series options page before being able to use the function manually in your theme.
 *
 * @package Organize Series WordPress Plugin
 * @since 2.0
*/

/**
* get_series_posts() - use to call up the list of posts in a supplied series id.  The style of the outputted display is determined by the PostList template on the Series Options page in the WordPress admin.
* AUTOTAG - is part of the postlist_template call [autotag option - "Display list of series on post pages"
* @package Organize Series WordPress Plugin
* @since 2.0
* 
* @uses is_single() - checks if this is a single post page being displayed.
* @uses  get_the_series() - returns the series ids of a post. 
* @uses get_option() - calls up the 'org_series_options' field from the _options table.
* @uses get_objects_in_term() - WordPress core function for accessing the taxonomy tables and pulling up all the posts associated with the supplied taxonomy id and taxonomy.
* @uses get_series_order() - Takes the array of posts in a series and returns it sorted by post order.
* @uses series_post_title() - Finds and displays the title of a post that is part of the series.
* @uses token_replace() - utility function to replace %tokens% in the template as set by the user in the series->options page.
* 
* @param int $ser_ID The ID of the series we want to list the posts from.
* @param bool|string  $referral  options are 'widget' | false.  Indicates what the referring location for calling this function is.  If 'widget' then widget specific code is applied. Defaults to false.
* @param bool $display Indicates whether to return the post list (false) or to echo the post list (true).  Defaults to false.
* @param bool|string $serieswidg_title The title for a list of other posts in the series displayed in widget.
* @return string The post list as a assembled string ready for display (if $display is false)
*/
function get_series_posts( $ser_ID = array(), $referral = false, $display = false, $serieswidg_title = false ) {  
 	global $post, $orgseries;
	if ( is_single() )
		$cur_id = $post->ID; //to get the id of the current post being displayed.
	else
		$cur_id = -1;
		
	if ( !is_single() && ( !isset($ser_ID) ) )
		return false;
		
	if (!empty($ser_ID) ) $ser_ID = is_array($ser_ID) ? $ser_ID : array($ser_ID);
	
	if ( !isset($ser_ID) || empty($ser_ID) ) {
		$serarray = get_the_series();
		if (!empty($serarray) ) {
			foreach ($serarray as $series) {
				$ser_ID[] = $series->term_id;
			}
		}
	}

	$series_post = array();
	$posts_in_series = array();
	$settings = $orgseries->settings;
	$result = '';
	foreach ( $ser_ID as $ser ) {
		$series_post = get_objects_in_term($ser, 'series');
		$is_unpub_template = TRUE;
		$is_unpub_template = apply_filters('unpublished_post_template', $is_unpub_template);
		
		$posts_in_series = get_series_order($series_post, 0, $ser, FALSE, $is_unpub_template);
		if ( 'widget' == $referral ) {
			if ($serieswidg_title != false)
				$result .= '<h4>' . __($serieswidg_title, 'organize-series') . '</h4>';
			$result .= '<ul>';
		}
		
		foreach($posts_in_series as $seriespost) {
			$short_title = get_post_meta($seriespost['id'], SPOST_SHORTTITLE_KEY, true);
			if ($cur_id == $seriespost['id']) {
				if ( 'widget' == $referral ) {
					$result .= '<li class="serieslist-current-li">' . series_post_title($seriespost['id'], true, $short_title) . '</li>';
				}
				else
					$result .= token_replace(stripslashes($settings['series_post_list_currentpost_template']), 'other', $seriespost['id'], $ser);
				continue;
			}
			
			if (get_post_status($seriespost['id']) == 'publish') {
				if ( 'widget' == $referral )
					$result .= '<li>' . series_post_title($seriespost['id']) . '</li>';
				else
					$result .= token_replace(stripslashes($settings['series_post_list_post_template']), 'other', $seriespost['id'], $ser);
			}
			else
				$result .= apply_filters('unpublished_post_template', $settings, $seriespost, $ser);
		}
		
		if ( 'widget' == $referral ) {
			$result .= '</ul>';
		}
	}
	
	if ( !$display ) 
		return $result;
	else 
		echo $result;
}

/**
 * wp_postlist_display() - Used to display all the series information for the particular post.
 * Use this on a single post display page (is_single()).  Use this template tag in the loop.
 * AUTOTAG - uses the postlist display template on the series->options page [AUTOTAG option - "Display list of series on post page?"]
 *
 * @package Organize Series WordPress Plugin
 * @since 2.0
 *
 * @uses get_option() - pull all the 'org_series_options' for templating info.
 * @uses get_the_series() - if post is part of a series will return series data (needed for series_id).
 * @uses token_replace() - replaces all the %tokens% as set for the postlist template on the series options page.
 * 
 * @return string|bool  - if post is a part of a series then the assembled template string will be returned.  If not, then the boolean false is returned.
*/
function wp_postlist_display() { 
	global $orgseries;
	$settings = $orgseries->settings;
	$serarray = get_the_series();
	$postlist = '';
	$count = count($serarray);
	$i = 1;
	$trigger = false;
	
		if (!empty($serarray)) {
			foreach ($serarray as $series) {
				$serID = $series->term_id;
				$postlist .= token_replace(stripslashes($settings['series_post_list_template']), 'post-list', $serID);
				if ( $i != $count || $trigger ) {
					$pos = strpos($postlist, '%postcontent%');
					if ( $pos == 0 ) $trigger = true;
					$postlist = str_replace('%postcontent%', '', $postlist);
				}
				$i++;
			}
			
			if ( $trigger && $settings['auto_tag_toggle'] ) $postlist = '%postcontent%'.$postlist;
			return $postlist;
		}
		
	return false;
}

/**
 * get_series_toc() - use this to display or return the link for the series table of contents
 *
 * @package Organize Series WordPress Plugin
 * @since 2.0
 * 
 * @uses get_option()  'org_series_options' from the _options table.
 *
 * @param bool $link if true echos the link in href format.  If false returns the uri of the series_toc
 *
 * @return string $url The uri of the series_toc. 
*/
function get_series_toc( $link = TRUE ) {
	global $orgseries, $wp_rewrite;
	$options = $orgseries->settings;
	$series_toc = $options['series_toc_url'];
	$url = get_bloginfo('url').'/'.$series_toc;
	$title = __('All the Series I\'ve Written', 'organize-series');
	if (isset($wp_rewrite) && $wp_rewrite->using_permalinks()) {
		if ( $link )
			echo sprintf(__('<a href="%s" title="%s">Series</a>', 'organize-series'), $url, $title);
		else
			return $url;
	} else {
		$url = parse_url(get_bloginfo('url'));
		$url = $url['path'] . '/?seriestoc=1';
		if ( $link )
			echo sprintf(__('<a href="%s" title="%s">Series</a>', 'organize-series'), $url, $title);
		else
			return $url;
	}
}

/**
 * wp_postlist_count() - counts the number of posts in the series the post belongs to IF it belongs to a series.
 * Should be used in the WordPress loop.
 * @package Organize Series WordPress Plugin
 * @since 2.0
 *
 * @uses get_orgserial - returns the series information for a single series (using the supplied series_id)
 *
 * @param bool|int $ser_id - defaults to false. int is the series id.  REQUIRED (unless TRUE is passed via the $calc param)
 * @param bool $calc = indicates whether the function should try to figure out the count without the series_id for the user.
 *
 * @return int $postlist_count - The number of posts in a series.
*/
function wp_postlist_count($ser_id = false, $calc = false) { 
	if (!$ser_id && !$calc) 
		return false; //need the $ser_id to caculate the number of posts in the series.
	
	if (!$ser_id && $calc) {
		$series = get_the_series();
		if ( !empty($series) ) {
			$postlist_count = $series[0]->count;
		} else {
			$postlist_count = 0;
		}
		return $postlist_count;
	}
	
	$series = get_orgserial($ser_id);
	if (!empty($series)) {
		$postlist_count = $series->count;
	} else {
		$postlist_count = 0;
	}
	return $postlist_count;
}

/**
 * wp_series_part() - For a post that is part of a series, this function returns the value for what part this post is in the series.
 * Should be used in the WordPress loop.
 * @package Organize Series WordPress Plugin
 * @since 2.0
 *
 * @uses get_post_meta() - Gets the part of the series the post is from the post metadata table.
 *
 * @param int $id - The Post ID (defaults to 0)
 * @param int $ser_id = The id of the series the post belongs to - REQUIRED inless bool|true is selected for the $calc paramater
 * @param bool $calc = indicates whether the function should try to figure out the $series_id for the user.
 *
 * @return int $series_part - The part the post is in a series IF it is part of a series.
*/
function wp_series_part( $id = 0, $ser_id = 0, $calc = false ) { 
	global $post;
	if ( $id == 0 ) {
		if ( isset($post) )
			$id = $post->ID;
	}
	
	if ( empty($ser_id) && $calc  )  {
		$series = get_the_series();
		if ( !empty($series) ) {
			$ser_id = $series[0]->term_id;
		} 
	}

	if ( $id == 0 || $ser_id == 0 )
		return false;
	
	$ser_post_id = $id;
	$part_key = apply_filters('orgseries_part_key', SERIES_PART_KEY, $ser_id);
	$series_part = get_post_meta($ser_post_id, $part_key, true);
	return $series_part;
}

/**
 * wp_seriesmeta_write() - use this to insert meta information (post part and series count) about the series the post belongs to IF it belongs to a series.
 * AUTOTAG - part of the series meta template [AUTOTAG OPTION "Display series meta information with posts?"]
 *
 * @package Organize Series WordPress Plugin
 * @since 2.0
 *
 * @uses get_option() - pull org_series_options for templating information.
 * @uses get_the_series() - get series data for the post if post is part of a series.
 * @uses token_replace() - replace all the %tokens% in the template as set on the series->options page.
 *
 * @return string|bool  - returns the completed series_meta template if post is a part of a series.  If post is not part of a series then returns the boolean false.
*/
function wp_seriesmeta_write($excerpt = FALSE) { 
	global $post, $orgseries; 
	$settings = $orgseries->settings;
	$serarray = get_the_series();
	$series_meta = '';
	$count = count($serarray);
	$i = 1;
	$trigger = false;
	if (!empty($serarray) ) {
		foreach ($serarray as $series) {
		$serID = $series->term_id; 
			if ( $excerpt ) {
				$series_meta .= token_replace(stripslashes($settings['series_meta_excerpt_template']), 'other', $post->ID, $serID);
			} else {
				$series_meta .= token_replace(stripslashes($settings['series_meta_template']), 'other', 0, $serID);
			}
			
			if ( $i != $count || $trigger ) {
				$pos = strpos($series_meta, '%postcontent%');
				if ( $pos == 0 ) $trigger = true;
				$series_meta = str_replace('%postcontent%', '', $series_meta);
			}
			$i++;
		}
		
		if ($trigger) $series_meta = '%postcontent%'.$series_meta;
		return $series_meta;
	}
	
	return false;
}

/**
 * wp_serieslist_display_code() - Will output the a formatted list of the indicated series.
 * Does not have to be in the loop.   Requires a series id for the $series param.
 *
 * @package Organize Series WordPress Plugin
 * @since 2.0
 *
 * @uses get_option() - for getting 'series_table_of_contents_box_template' info from the series options field in the options db table.
 * @uses token_replace() - for replace %tokens% set in the template on the series options page.
 *
 * @param object|int  $series - object contains series id.  Int contains user defined series_id
 * @param bool|string $referral - defaults to false which means run default paramaters.  There are no other values that will be accepted in this paramater for now - I've left this in for future versions of orgSeries.
 *
 * @return bool false if there is no series for the provided series id.
*/
function wp_serieslist_display_code( $series, $referral = false, $display = true ) { //reusable function for display of series information
		global $orgseries;
		$settings = $orgseries->settings;
		
		if ( isset( $series->term_id ) )
			$serID = $series->term_id;
		else 
			$serID = $series;
			
		if (isset($serID)) {
			$series_display = token_replace(stripslashes($settings['series_table_of_contents_box_template']), 'series-toc', $serID);
			if ( $display )
				echo $series_display;
			else
				return $series_display;
		}
		return false;
}

/**
 * wp_serieslist_display() - Will output a formatted list of all series
 * Does not have to be in the loop.  Is used in the default template for the series table of contents page.
 *
 * @package Organize Series WordPress Plugin
 * @since 2.0
 *
 * @uses get_series() - gets all the series data from the blog (but won't grab empty series).
 * @uses wp_serieslist_display_code() - assembles the formatted list of series.
 *
 * @param bool|string $referral  If not set defaults to false.  Currently there isn't application for this param but I've left it in for future versions of orgSeries.
 * @param array ($args) This is so you can indicate various paramaters for what series you want displayed (see get_series for the description of the possible args).
*/ 
function wp_serieslist_display( $referral = false, $args='' ) {  
	global $orgseries;
	$options = is_object($orgseries) ? $orgseries->settings : null;
	$per_page = is_array($options) && isset($options['series_perp_toc']) ? $options['series_perp_toc'] : 5 ;
	$page = ( get_query_var('paged') ) ? get_query_var( 'paged' ) : 1;
	$offset = ( $page-1 ) * $per_page;
	
	$defaults = array (
		'number' => $per_page,
		'offset' => $offset,
		'hide_empty' => 1
	);

	$args = wp_parse_args( $args, $defaults );
	$series_list = get_series($args);

	foreach ($series_list as $series) {  
		wp_serieslist_display_code($series, $referral); //layout code
	}
}

/**
* series_toc_paginate() - Will do the pagination for queried terms of selected custom taxonomy.
*
* @package Organize Series WordPress Plugin
* 
* @param string $prev  A symbol or a word to be displayed in the pagination as a link to the previous page.
* @param string $next  A symbol or a word to be displayed in the pagination as a link to the next page.
*/
function series_toc_paginate($prev = "<< ", $next = " >>") {
	global $wp_query, $wp_rewrite, $orgseries;
	$options = is_object($orgseries) ? $orgseries->settings : NULL;
	$per_page = is_array($options) && isset($options['series_perp_toc']) ? $options['series_perp_toc'] : 5;
	$current = $wp_query->query_vars['paged'] > 1 ? $wp_query->query_vars['paged'] : 1;
	$total_terms = (int) wp_count_terms('series', array('hide_empty' => true));
	$max_num_pages = ceil($total_terms/$per_page);;
	$pagination = array(
		'base' => @add_query_arg('paged','%#%'),
		'format' => '',
		'total' => (int) $max_num_pages,
		'current' => $current,
		'prev_text' => $prev,
		'next_text' => $next,
		'type' => 'plain'
	);
	if( $wp_rewrite->using_permalinks() )
		$pagination['base'] = user_trailingslashit( trailingslashit( remove_query_arg( 'pg', get_pagenum_link( 1 ) ) ) . 'page/%#%/', 'paged' );
	if( !empty($wp_query->query_vars['pg']) )
		$pagination['add_args'] = array( 'pg' => get_query_var( 'pg' ) );
	echo paginate_links( $pagination );
}

//series navigation strip on single-post display pages.
/**
 * wp_series_nav() - assembles the links for the next or previous post links.
 * YOU can call this if you simply want to output either the next post in a series or the previous post in a series but it will not return both.
 * 
 * @package Organize Series WordPress Plugin
 * @since 2.0
 *
 * @uses get_post_meta() - will get the current series part for the displayed post.
 * @uses get_option() - will get the template for series nav.
 * @uses get_objects_in_term() - will get all the posts that belongs to the series the current post belongs to.
 * @uses get_series_order() - will take the list of posts and sort them by their order in the series.
 * @uses get_the_title() - post title.
 * @uses get_permalink() - permalink of a post
 * 
 * @param int $series_ID REQUIRED
 * @param bool $next  if TRUE will output the next post in the series.  if FALSE will output the previous post in the series.
 * @param bool $customtext (THIS paramater is deprecated as of Organize Series 2.3.6)
 * @param bool $display if TRUE will echo the linked post.  if FALSE will return the linked post.
 * @param bool $calc = indicates whether the function should try to figure out the $series_id for the user.
 *
 * @return string $result contains the linked post (next OR previous post depending on  $next param)
*/
function wp_series_nav($series_ID, $next = TRUE, $customtext = 'deprecated', $display = FALSE, $calc = false) {
	global $post, $orgseries;
	
	if ( empty($series_ID) && $calc ) {
		$series = get_the_series();
		if ( !empty($series) ) {
			$series_ID = $series[0]->term_id;
		} 
	}
	
	
	if (empty($series_ID)) return false; //we can't do anything without the series_ID;
	$cur_id = $post->ID;
	$settings = $orgseries->settings;
	$series_part_key = apply_filters('orgseries_part_key', SERIES_PART_KEY, $series_ID);
	$cur_part = get_post_meta($cur_id, $series_part_key, true);
	$series_posts = get_objects_in_term($series_ID, 'series');
	$posts_in_series = array();
	$posts_in_series = get_series_order($series_posts, $cur_id, $series_ID);
	$result = '';
	
	foreach ($posts_in_series as $seriespost) {
		$custom_next = esc_html(token_replace($settings['series_nextpost_nav_custom_text'], 'other', $seriespost['id'], $series_ID));
		$custom_prev = esc_html(token_replace($settings['series_prevpost_nav_custom_text'], 'other', $seriespost['id'], $series_ID))  ;
		if ($next) {
			if ( ($seriespost['part'] - $cur_part) == 1) {
					if ( !empty($custom_next) ) $title = $custom_next;
					else $title = get_the_title($seriespost['id']);
					$link = get_permalink($seriespost['id']);
					$result .= '<a href="' . $link . '" title="' . $title . '">' . $title . '</a>';
					}
		}
		
		if (!$next) {
			if (($cur_part - $seriespost['part']) == 1) {
					if (!empty($custom_prev)) $title = $custom_prev;
						else $title = get_the_title($seriespost['id']);
					$link = get_permalink($seriespost['id']);
					$result .= '<a href="' . $link . '" title="' . $title . '">' . $title . '</a>';
				}
		}
	}
		if ($display) echo $result;
			else return $result;	
}

/**
 * wp_assemble_series_nav() - outputs the entire series nav "strip" according to the template set in series options.
 * Needs to be in the loop.  AUTOTAG - will display automatically with posts that are a part of a series IF the %postcontent% token is in the series nav template.
 *
 * @package Organize Series WordPress Plugin
 * @since 2.0
 *
 * @uses get_option() - gets the 'series_post_nav_template' from the options table.
 * @uses get_the_series() - gets the series data for the post.
 * @uses token_replace() - replaces all the %tokens% in the series-nav template.
 *
 * @return string|bool - returns the assembled series nav strip as a string if the post is part of a series and returns the boolean false if not.
*/
function wp_assemble_series_nav() {
	global $orgseries;
	$settings = $orgseries->settings;
	$series = get_the_series();
	$nav = '';
	$count = count($series);
	$i = 1;
	$trigger = false;
		if (!empty($series)) {
			foreach ($series as $ser) {
				$series_id = $ser->term_id;
				$series_count = $ser->count;
				if ( $series_count > 1 ) {
					$nav .= token_replace(stripslashes($settings['series_post_nav_template']), 'other', $series_id);
					if ( $i != $count || $trigger ) {
						$pos = strpos($nav, '%postcontent%');
						if ( $pos == 0 ) $trigger = true; //%postcontent% is at the top in the template so we need to erase all %postcontent% to fix.
						$nav = str_replace('%postcontent%', '', $nav);
					}
					
					$i++;
				}
			}
			if ($trigger) $nav = '%postcontent%'.$nav;
			return $nav;
		}
	
	return FALSE;
}

/**
 * latest_series() - gets the most recent series from the database according to the latest post-modified date and uses the latest_series template from series options for how it is displayed.
 *
 * @package Organize Series WordPress Plugin
 * @since 2.1
 *
 * @uses get_option() - to get the 'latest_series_template' from the options table.
 * 
 * @uses token_replace() - to replace all the %tokens% in the template for latest_series as set on the series options page.
 * @uses get_series_ordered - to get all the series according to how it should be ordered.
 *
 * @param bool $display - if true the 'latest_series_template' will be echoed else it will be returned.
 *@param array $args - allow for setting criteria for the latest series being pulled from the database.
 *
 * @return string $result - the assembled latest_series code.
*/
function latest_series($display = true, $args = '') {
	global $wpdb, $orgseries;
	$defaults = array('orderby' => 'post_modified', 'order' => 'ASC', 'hide_empty' => true, 'number' => '5');
	$args = wp_parse_args( $args, $defaults );
	$args['number'] = absint( $args['number'] );
	extract($args, EXTR_SKIP);
	$settings = $orgseries->settings;
	$count = $number;
	
	$terms = get_series_ordered($args);
	
	$result = '';
	$result = stripslashes($settings['latest_series_before_template']);
	$k = 0;
	
	foreach ( $terms as $latestseries ) {
		
		if ($k < $count) {
			$result .= token_replace(stripslashes($settings['latest_series_inner_template']), 'latest_series', $latestseries->term_id); 
		}
		
		$k++;
	}
	
	$result .= stripslashes($settings['latest_series_after_template']);
	
	if ($display)
		echo $result;
	else
		return $result;
}

/**
 * get_series_link() - returns what the url is for the series id passed as the parameter.
 * requires series_id
 *
 * @package Organize Series WordPress Plugin
 * @since 2.0
 *
 * @uses get_series_permastruct() - gets the permastructure for series.
 * @uses get_term() - get's the series information from the taxonomy tables.
 * @uses get_option() - with the parameter 'home' calls up the uri for the home directory of the WordPress install.
 * @uses str_replace()
 * @uses apply_filters() - with 'series_link' as the callback for pluggable filtering of the series_link.
 *
 * @param int $series_id - the series_id we want the link for.
 *
 * @return string - the final constructed series link.
*/
function get_series_link( $series_id = '' ) {
	global $orgseries;
	$series_token = '%' . SERIES_QUERYVAR . '%';
	if ( empty($series_id) || $series_id == null )
		$series_slug = get_query_var(SERIES_QUERYVAR);
		
	if ( is_numeric($series_id) ) {
		$series_slug = get_term_field( 'slug', $series_id, 'series' );
	} else {
		if ( $series_slug_get = get_term_by('name', htmlentities2($series_id), 'series' ) ) {
				$series_slug = $series_slug_get;
		} 
	}
	
	if ( empty($series_slug) || $series_slug == null || $series_slug == '' )
		return false;
		
	$serieslink = get_term_link($series_slug, 'series');
	
	return apply_filters('series_link', $serieslink, $series_id); 
}

/**
 * get_the_series_by_ID() - providing a series_id this function will return the series name.
 *
 * @package Organize Series WordPress Plugin
 * @since 2.0
 *
 * @uses get_orgserial() - calls up the series information for one series by the provided series_Id
 * @uses is_wp_error() - checks to see if the series data returned an error.
 *
 * @param int $series_ID - the series ID required for gettting the series name.
 *
 * @return string|int $series - if series is_wp_error then a string will be returned with the error else the series name will be returned.
*/
function get_the_series_by_ID( $series_ID ) {
	$series_ID = (int) $series_ID;
	$series = &get_orgserial($series_ID);
	if ( is_wp_error( $series ) )
		return $series;
	return $series->name;
}

/**
 * in_series() - will check if the current post is in a given series OR if the post is in ANY series (when series ID isn't provided. Works similarily to in_category()
 * Needs to be in the loop.
 *
 * @package Organize Series WordPress Plugin
 * @since 2.0
 *
 * @uses get_object_term_cache() - pulls info from the wp_cache if there.
 * @uses wp_get_object_terms() - gets the series the post belongs to if the post belongs to a series.
 * @uses get_series_ID() - gets the ID of the series if the param supplied is the name - else the series_term is an id already.
 * @uses array_key_exists()
 *
 * @param string|int $series_term Can be the series_id or the series name.
 *
 * @return bool true if the post is in the supplied series.
*/
function in_series( $series_term = 0 ) { //check if the current post is in the given series
	global $post;
	
	if ( $series_term == 0  && empty($post->ID) ) 
		return false;
		
	if ( $series_term == 0 ) // we're just checking if the post is in ANY series
		$check_any = true;
		
	$ser_ID = get_series_ID($series_term);
	if ( $ser_ID )
		$series_term = $ser_ID;
	
	$series = get_object_term_cache($post->ID, 'series');
	
	if ( false === $series )
		$series = wp_get_object_terms($post->ID, 'series');
	
	if ( $check_any ) {
		if ( $series ) return true;
		else return false;
	}
	
	if ( array_key_exists($series_term, $series))
		return true;
	else
		return false;
}

/**
 * get_series_name() - Using the supplied series_id this function will return the series name.
 *
 * @package Organize Series WordPress Plugin
 * @since 2.0
 * 
 * @uses get_orgSerial() - returns series information for supplied series ID.
 *
 * @param int $series_id
 *
 * @return string $series->name
*/
function get_series_name($series_id, $slug = false) {
	$series_id = (int) $series_id;		
	$series = get_orgserial($series_id);
	
	if ( !empty($series) ) {
		return ( $slug ) ? $series->slug : $series->name;
	}
	return false;
}

/**
 * the_series_title() - gets the series name for the supplied series ID 
 * 
 * This is different from get_series_name in that it allows for choosing to have the title hyperlinked or not & displayed or not.
 *
 * @package Organize Series WordPress Plugin
 * @since 2.0
 * 
 * @uses get_series_name()
 * @uses is_wp_error()
 * @uses get_series_link()
 * 
 * @param int $series_id
 * @param bool $linked if true the title will be linke to the series archive page.
 * @param bool $display if true the title will be echoed.
 *
 * @return string|bool - string $result if $display is false and bool false if there is no series name for the supplied series_id.
*/
function the_series_title($series_id=0, $linked=TRUE, $display=FALSE) {
	if( 0==$series_id )
		return false;
	
	$series_id = (int) $series_id ;
	
	if ( !empty($series_id) ) {
		$series_name = get_series_name($series_id);
		if ( is_wp_error( $series_name ) )
			return false;
		$prefix = '';
		$suffix = '';
		
		if ( !empty($series_name) ) {
			if ( $linked ) {
				$series_link = get_series_link($series_id);
				$prefix = '<a href="' . $series_link . '" class="series-' . $series_id . '" title="'.$series_name.'">';
				$suffix = '</a>';
			}
			
			$result = $prefix . $series_name . $suffix;
			if ( $display ) 
				echo $result;
			else
				return $result;
		}
	}
	return false;
}

/**
 * series_description() - Gets the description of the series from the database for the supplied series_id
 *
 * @package Organize Series WordPress Plugin
 * @since 2.0
 *
 * @uses get_term_field()
 *
 * @param int $series_id
 * @return string description text
*/
function series_description($series_id = 0) {
	global $orgseries;
	if ( !$series_id ) {
		$ser_var = get_query_var(SERIES_QUERYVAR);
		$ser_var = term_exists( $ser_var, SERIES_QUERYVAR );
		if ( !empty($ser_var) ) 
			$series_id = $ser_var['term_id'];
	}
	
	if ($series_id == '') return false;
		
	return get_term_field('description', $series_id, 'series');
}

/**
 * series_post_title() - gets the post title of a post that is part of the series with the supplied post_ID (if not in loop - if in loop the post ID will be taken from the global $post object)
 * 
 * @package Organize Series WordPress Plugin
 * @since 2.0
 * 
 * @uses get_the_title() - get's the title of the post with the supplied post ID.
 * @uses get_permalink() - get's the permalink of the post with the supplied post ID
 * 
 * @param int $post_ID
 * @param bool $linked - if true then the post will be linked to it's permalink page.
 * @return string $return - title text OR linked title text.
*/
function series_post_title($post_ID, $linked=TRUE, $short_title = false) {
	global $post;
	if (!isset($post_ID))
		$post_ID = (int)$post->ID;
	if(($short_title != false) && (!empty($short_title)))
		$title = $short_title;
	else
		$title = get_the_title($post_ID);
	if ($linked) {
		$link = get_permalink($post_ID);
		$return = '<a href="' . $link . '" title="' . $title . '">' . $title . '</a>';
	} else {
		$return = $title;
	}
	return $return;
}

/**
 * is_series() - checks if displayed page is a series related page.
 *
 * @package Organize Series WordPress Plugin
 * @since 2.1
 *
 * @ $wp_query;
 * 
 * @param sting|array $slug optional.  Slug or slugs to check in current query.
 * @return bool true if displayed page is a series.
*/
function is_series( $slug = '' ) { 
	global $wp_query, $orgseries;
	$series = get_query_var(SERIES_QUERYVAR);
	
	if ( (!is_null($series) && ($series != '')) || (isset($wp_query->is_series) && $wp_query->is_series ))
		return true;
	else
		return false;
}

/**
 * is_seriestoc() - checks if displayed page is the main seriestoc page.
 *
 * @package Organize Series WordPress Plugin
 * @since 2.1
 *
 * @ $wp_query;
 * 
 * @return bool true if displayed page is the seriestoc.
*/
function is_seriestoc() {
	global $wp_query;
	if ( $wp_query->is_seriestoc == true ) {
		return true;
	}
	return false;
}

/**
 * get_series_icon() -  Template tag for insertion of series-icons
 * 
 * @package Organize Series WordPress Plugin
 * @since 2.0
 *
 * @uses parse_str()
 * @uses stripslaghes_gpc_arr()
 * @uses get_the_series()
 * @uses is_array()
 * @uses series_get_icons()
 * @uses seriesicons_path()
 * @uses seriesicons_url()
 * @uses get_series_link()
 * @uses getimagesize()
 * @uses series_fit_rect()
 *
 * @param int[-1] $fit_width Maximum width (or desired width if $expanded=true) of the image.
 * @param int[-1] $fit_height Macimum height (or desired height if $expanded=true) of the image.
 * @param boolean [false] $expand Whether the image should be expanded to fit the rectangle specified by fit_xxx.
 * @param int $series Series ID. If not specified, the current series is used or the current post's series.
 * @param string $prefix String to echo before the image tag. If no image, no otuput.
 * @param string $suffix String to echo after the image tag. Ignored if no image found.
 * @param string [] $class  Class attribute for the image tag.
 * @param boolean [1] $link If true the image is made a hyperlink (wrapped by anchor tag).
 * @param  boolean [1] $display If true the function will echo the image.  If false the function will return the assembled image as a string.
 *
 * @return mixed|bool|string Will return false if image is not found.  Will return string containing assembled html code for image if $display is false.  Will echo image if the $display param = true.
 */
 function get_series_icon($params='') {
 	global $orgseries;
	parse_str($params, $p);
	if (!isset($p['fit_width'])) $p['fit_width']=-1;
	if (!isset($p['fit_height'])) $p['fit_height']=-1;
	if (!isset($p['expand'])) $p['expand']=false;
	if (!isset($p['series'])) $p['series']= get_query_var(SERIES_QUERYVAR); 
	if (!isset($p['prefix'])) $p['prefix'] = '';
	if (!isset($p['suffix'])) $p['suffix'] = '';
	if (!isset($p['class'])) $p['class'] = 'series-icon-' . $p['series'];
	if (!isset($p['link'])) $p['link'] = 1;
	if (!isset($p['display'])) $p['display'] = 1;
	stripslaghes_gpc_arr($p);
	
	if (empty($p['series']) && isset($GLOBALS['post'])) {
		$serieslist = get_the_series($GLOBALS['post']->ID);
		if ( is_array($serieslist) ) $p['series'] = $serieslist[0]->term_id;
	}
	
	if (!isset($p['series'])) return;
		
	$icon = series_get_icons($p['series']);
	$s_name = get_series_name($p['series']);
	$file = seriesicons_path() . $icon;
	$url = seriesicons_url() . $icon;
		
	if ($p['link']) {
		$p['prefix'] .= '<a href="' . get_series_link($p['series']) . '">';
		$p['suffix'] = '</a>' . $p['suffix'];
	}
	
	if (is_file($file)) {
		list($width, $height, $type, $attr) = getimagesize($file);
		list($w, $h) = series_fit_rect($width, $height, $p['fit_width'], $p['fit_height'], $p['expand']);
		$series_icon = $p['prefix'] . '<img class="' . $p['class'] . '" src="' . $url . '" width="' . $w . '" height="' . $h . '"  alt="' . $icon . '" />' . $p['suffix'];
		if ($p['display'] == 1) {
			echo $series_icon;
		 } else {
			return $series_icon;
		}
		}
	return false;
}

/**
 * single_series_title() - another function to get a series name except this calls a series name on a series archive page without having to supply the series_id.
 *
 * @package Organize Series WordPress Plugin
 * @since 2.0
 *
 * @uses get_query_var() - to get the series_id 
 * @uses term_exists() - to make sure the series query var is actually a series
 * @uses get_term()
 * @uses is_wp_error()
 * @uses apply_filters() on 'single_series_title' with the series name.
 * 
 * @param string $prefix Want something to show up before the series title?
 * @param bool $display If true the series title is echoed if false then returned.
 * @return string $my_series_name
*/
function single_series_title($prefix = '', $display = true) {
	global $orgseries;
	$series_id = get_query_var(SERIES_QUERYVAR);
	$serchk = term_exists( $series_id, SERIES_QUERYVAR );
	
	if ( !empty($serchk) ) {
		$series_id = $serchk['term_id'];
	}
	
	if ( !empty($series_id) ) {
		$my_series = get_term($series_id, 'series', OBJECT, 'display');
		if ( is_wp_error( $my_series ) )
			return false;
		$my_series_name = apply_filters('single_series_title', $my_series->name);
		if ( !empty($my_series_name) ) {
			if ( $display )
				echo $prefix, $my_series_name;
			else
				return $my_series_name;
		}
	}
}
?>
