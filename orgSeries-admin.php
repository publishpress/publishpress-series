<?php
/**
 * This file contains all the code that hooks orgSeries into the various pages of the WordPress administration.   Some hooks will reference other files (series management, and series options).
 *
 * @package Organize Series WordPress Plugin
 * @since 2.2
*/

global $pagenow;
$checkpage = $pagenow;
global $checkpage;

/**
 * All the add_action and apply_filter hooks for this file go here
*/
//add action for admin-series.css and thickbox
add_action('admin_print_styles', 'orgSeries_admin_header');

##ADMIN-Write/Edit Post Script loader
add_action('admin_print_scripts','orgSeries_admin_script'); 

//add ajax for on-the-fly series adds
add_action('wp_ajax_add-series', 'admin_ajax_series');
add_action('wp_ajax_inline-series', 'admin_inline_series_ajax');

//hook into the quick-edit on edit.php
add_filter('manage_posts_columns', 'orgSeries_custom_column_filter');
add_action('manage_posts_custom_column','orgSeries_custom_column_action', 12, 2);

if ( $checkpage != 'upload.php' )
		add_action('restrict_manage_posts', 'orgSeries_custom_manage_posts_filter');
 
add_action('post_relatedlinks_list', 'add_series_management_link');

add_action( 'right_now_content_table_end', 'add_series_to_right_now');

//function: Add Meta-box
add_action('admin_menu', 'orgseries_add_meta_box');

function orgSeries_admin_header() {
	$plugin_path = SERIES_LOC;
	$csspath = $plugin_path . "orgSeries-admin.css";
	wp_register_style( 'orgSeries-admin', $csspath );
	wp_enqueue_style( 'orgSeries-admin' );
	wp_enqueue_style( 'thickbox' );
}

//add_action filter for the manage_series page...
function orgSeries_admin_script() {
//load in the series.js script and set localization variables.
global $checkpage, $orgseries, $pagenow;
	
	if (isset($_GET['page']))
		$checkpage = $_GET['page'];
	
	if ('post-new.php' == $checkpage || 'post.php' == $checkpage) {
		wp_enqueue_script( 'ajaxseries' );
	}
	if ( 'edit-tags.php' == $checkpage && 'series' == $_GET['taxonomy'] )
	orgSeries_manage_script();
	
}

function orgSeries_manage_script() {
	wp_enqueue_script( 'thickbox' );
	wp_enqueue_script('media-upload');
	wp_enqueue_script('orgseries_scripts','/'.PLUGINDIR.'/'.SERIES_DIR.'/js/orgseries_scripts.js',array('jquery', 'thickbox')); 
}

######ON THE FLY ADD SERIES########
function admin_ajax_series() { 
	if ( !current_user_can( 'manage_series' ) )
		die('-1');
	global $wp_taxonomies;
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

###AJAX FOR INLINE_SERIES UPDATE###
function admin_inline_series_ajax() {
	check_ajax_referer ( 'inlineeditnonce', '_inline_edit' );
	$series_id = $_POST['post_series'];
	$part = $_POST['series_part'];
	$post_id = $_POST['series_post_id'];
	orgseries_custom_column_action('series', $post_id);
	exit;
	break;
}

/**  
 * get_series_list() - queries the list of series from the database and creates an array indicating what 
 * series is associated with the current post (if existent).
 *
 * The code here is mirrored from get_nested_categories in the core  wp-admin\includes\template.php
 * file.
 *
 * @uses count() - internal WordPress function
 * @uses wp_get_post_series()
 * @uses get_series()
 * @uses get_the_series_by_ID()
 * @uses apply_filters() - user can modify the array that is returned.
 * @param int $default - id for the default series.  Currently there is not an option for users to set a
 * default series.  This may change in the future.
 * @return array|mixed $result - series ids, series names, and checked value. 
 */
function get_series_list( $default = 0 ) { 
	global $post, $postdata, $checked_series;
	$post_ID = isset( $post ) ? $post->ID : $postdata->ID;
	
	if ( empty( $checked_series ) ) {
		if ( $post_ID ) {
			$checked_series = wp_get_post_series( $post_ID );
			
			if ( count( $checked_series ) == 0 ) {
				$checked_series[] = $default;
			}
		} else {
			$checked_series[] = $default;
		}
		
		$series = get_series( "hide_empty=0&fields=ids" );
		
		$result = array ();
		
		if ( is_array( $series ) ) {
			foreach ( $series as $serial ) {
				$result[$serial]['series_ID'] = $serial;
				$result[$serial]['checked'] = in_array( $serial, $checked_series );
				$result[$serial]['ser_name'] = get_the_series_by_ID( $serial );
			}
		}
		
		$result = apply_filters( 'get_series_list', $result );
		usort( $result, '_usort_series_by_name' );
		
		return $result;
	}
}

/**  
 * write_series_list() - html output for the list of series in for user selection on the write/edit post screen
 * Code is mirror'd from the write_nested_categories() function in the core wp-admin\includes\template.php file.
 *
 * @uses wp_specialchars()
 * @param array|mixed $series - contains series_ID, ser_name, and checked (does post belong to this series) 
*/
function write_series_list( $series ) { //copied from write_nested_categories in template.php
	global $orgseries;
		echo '<li id="series-0"><label for ="in-series-0" class="selectit"><input value="0" type="radio" name="post_series" id="in-series-0" checked="checked" />' . __('Not part of a series', $orgseries->org_domain) . '</label></li>';
		foreach ( $series as $serial ) {
			echo '<li id="series-', $serial['series_ID'],'"><label for="in-series-', $serial['series_ID'], '" class="selectit"><input value="', $serial['series_ID'], '" type="radio" name="post_series" id="in-series-', $serial['series_ID'], '"', ($serial['checked'] ? ' checked="checked"' : '' ), '/> ' , wp_specialchars( $serial['ser_name'] ), "</label></li>";
			
		}
}

/**
* get_series_to_select() - wrapper function to output the list of series on the write/edit post page.
* Calls up the list of existing series in the WordPress db and displays the series that the post belongs to already selected.
*
*@uses write_series_list()
*@uses get_series_list()
*@param int $default = id for the default series. Currently there is not an option for users to set a default series id.  This may change in the future.
*/
function get_series_to_select( $default = 0 ) {
	write_series_list( get_series_list( $default) );
}

//series-edit box for wp 2.8 forward
function series_edit_meta_box() {
global $post, $postdata, $content, $orgseries;
	$id = isset($post) ? $post->ID : $postdata->ID;
	?>
	<p id="jaxseries"></p>
		<span id="series-ajax-response"></span>
		<ul id="serieschecklist" class="list:series serieschecklist form-no-clear">
				<?php get_series_to_select(); ?>
		</ul>
		<span id="seriespart"><strong> <?php _e('Series Part:', $orgseries->org_domain); ?>   </strong><input type="text" name="series_part" id="series_part" size="5" autocomplete="off" value="<?php echo get_post_meta($id, SERIES_PART_KEY, true); ?>" /></span>
			<p id="part-description"><?php _e('Note: that if you leave this blank or enter an invalid number the post will automatically be appended to the rest of the posts in the series', $orgseries->org_domain); ?></p>
	<?php
}

function orgseries_add_meta_box() {
	global $orgseries;
	add_meta_box('seriesdiv', __('Series', $orgseries->org_domain), 'series_edit_meta_box', 'post', 'advanced', 'core');
	remove_meta_box('tagsdiv-series', 'post', 'advanced'); //removes series meta box added by WordPress Taxonomy api.
}

/* ADDING SERIES INFO TO EDIT POST PAGE */ 

function orgSeries_custom_column_filter($defaults) {
	global $orgseries;
	$defaults['series'] = __('Series', $orgseries->org_domain);
	return $defaults;
}

function orgSeries_custom_column_action($column_name, $id) {
	global $wpdb, $orgseries;
	if ($column_name == 'series') {
		if ( $series = get_the_series($id) ) {
			$seriesid = $series[0]->term_id;
			$series_name = $series[0]->name;
			$series_link = get_series_link($series_name);
			$series_part = get_post_meta($id, SERIES_PART_KEY, TRUE);
			$count = $series[0]->count;
			$column_content = '';
				if ($series && get_post_status($id) == 'publish') {
					$column_content = '<div class="series_column">'.sprintf(__('Part %1$s of %2$s in the series, <a href="%3$s" title="%4$s">%5$s</a>', $orgseries->org_domain), $series_part, $count, $series_link, $series_name, $series_name);
					$column_content .= '<div class="hidden" id="inline_series_' . $id . '"><div class="series_inline_edit">'.$seriesid.'</div><div class="series_inline_part">'.$series_part.'</div><div class="series_post_id">'.$id.'</div><div class="series_inline_name">'.$series_name.'</div></div></div>';
					echo  $column_content;  
				} else {
					$column_content = '<div class="series_column">'.sprintf(__('<a href="%1$s" title="%2$s">%3$s</a> - (currently set as Part %4$s)', $orgseries->org_domain), $series_link, $series_name, $series_name, $series_part);
					$column_content .= '<div class="hidden" id="inline_series_' . $id . '"><div class="series_inline_edit">'.$seriesid.'</div><div class="series_inline_part">'.$series_part.'</div><div class="series_post_id">'.$id.'</div><div class="series_inline_name">'.$series_name.'</div></div></div>';
					echo $column_content;
				}
			} else {
				$column_content = '<div class="series_column"><div class="hidden" id="inline_series_' . $id . '"><div class="series_inline_edit">'.$seriesid.'</div><div class="series_inline_part">'.$series_part.'</div><div class="series_post_id">'.$id.'</div><div class="series_inline_name">'.$series_name.'</div></div>';
				$column_content .= '<em>'.__('No Series', $orgseries->org_domain).'</em></div>';
				echo $column_content;
			}
	} 
}

function orgSeries_custom_manage_posts_filter() {
	global $orgseries;
	
	if (isset($_GET['series'])) $series_name = $_GET['series'];
		
	wp_dropdown_series('show_option_all='.__('View all series', $orgseries->org_domain).'&hide_empty=0&show_count=0&selected='.$series_name);
}

function add_series_management_link() {
	global $orgseries;
	$link = get_option('siteurl') . '/wp-admin/edit.php?page=' .  SERIES_DIR . '/orgSeries-manage.php';
	?>
	<li><a href="<?php echo $link; ?>"><?php _e('Manage All Series', $orgseries->org_domain); ?></a></li>
	<?php
}

function add_series_to_right_now() {
	global $orgseries;
	$num_series = wp_count_terms('series');
	$num = number_format_i18n( $num_series );
	$text = __ngettext( 'Series', 'Series', $num_series, $orgseries->org_domain );
	$manage_link = get_option('siteurl') . '/wp-admin/edit-tags.php?taxonomy=series';
	if ( current_user_can( 'manage_series' ) ) {
		$series_num = "<a href='$manage_link'>$num</a>";
		$series_text = "<a href='$manage_link'>$text</a>";
	}
	
	echo '<tr>';
	echo '<td class="first b b-tags">'.$series_num.'</td>';
	echo '<td class="t tags">' . $series_text . '</td>';
	echo '<td></td><td></td></tr>';
}
?>