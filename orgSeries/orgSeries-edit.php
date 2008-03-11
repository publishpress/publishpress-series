<?php
/**
 * This file contains the code that helps with the implementation of the OrgSeries plugin in the
 * write/edit post pages.
 *
 * @package WordPress
 * @since 2.3
 */
 global $wp_version;
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
		usort( $result, '_usort_terms_by_name' );
		
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
		echo '<li id="series-0"><label for ="in-series-0" class="selectit"><input value="0" type="radio" name="post_series" id="in-series-0" checked="checked" />Not part of a series</label></li>';
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

if ( isset( $wp_version ) && $wp_version >= 2.5 )
	add_action( 'submitpost_box', 'series_new_edit_box' );
else
	add_action('dbx_post_sidebar', 'series_edit_box');

function series_new_edit_box() {
	global $post, $postdata, $content;
	$id = isset($post) ? $post->ID : $postdata->ID;
	?>
	<div class="side-info">
	<h5><?php _e('Series') ?></h5>
		<div id="seriesdiv">
			
		<p id="jaxseries"></p>
				<?php /*<input type="text" name="newseries" id="newseries" class="form-required form-input-tip" value="<?php _e( 'New series name' ); ?>" />
				<input type="button" id="series-add" class="add:serieschecklist:seriesdiv button" value="<?php _e( 'Add' ); ?>" />
				<?php wp_nonce_field( 'add-series', '_ajax_nonce', false ); ?> */ ?>
				<span id="series-ajax-response"></span>
		</p>
			
		<ul id="serieschecklist" class="list:series serieschecklist form-no-clear">
				<?php get_series_to_select(); ?>
		</ul>
		</div>
	</div>
	<?php
}

function series_edit_box() {
global $post, $postdata, $content;
$id = isset($post) ? $post->ID : $postdata->ID;
?>
	<fieldset id="seriesdiv" class="dbx-box">
		<h3 class="dbx-handle"><?php _e("Organize Series") ?></h3>
		<div class="dbx-content">
			<p id="jaxseries"></p>
		<ul id="serieschecklist">	<?php get_series_to_select(); ?></ul>
		<span id="seriespart"><strong> Series Part:   </strong><input type="text" name="series_part" id="series_part" size="5" autocomplete="off" value="<?php echo get_post_meta($id, SERIES_PART_KEY, true); ?>" /></span>
			<p id="part-description">Note: that if you leave this blank or enter an invalid number the post will automatically be appended to the rest of the posts in the series</p>
		</div>
	</fieldset>
	<?php
}

add_filter('manage_posts_columns', 'orgSeries_custom_column_filter');
function orgSeries_custom_column_filter($defaults) {
	$defaults['series'] = __('Series');
	return $defaults;
}

add_action('manage_posts_custom_column','orgSeries_custom_column_action', 10, 2);
function orgSeries_custom_column_action($column_name, $id) {
	global $wpdb;
	if ($column_name == 'series') {
		$series = get_the_series($id);
		$seriesid = $series[0]->term_id;
		$series_name = $series[0]->name;
		$series_link = get_series_link($seriesid);
		$series_part = get_post_meta($id, SERIES_PART_KEY, TRUE);
		$count = $series[0]->count;
		if ($series) {
			$column_content = 'Part ' . $series_part . ' of ' . $count . ' the series, <a href="' . $series_link . '" title="' . $series_name . '">' . $series_name . '</a>';
			echo  $column_content;
		} else {
			echo '<em>No Series</em>';
		}
	}
}

add_action('restrict_manage_posts', 'orgSeries_custom_manage_posts_filter');
function orgSeries_custom_manage_posts_filter() {
	$_GET['series'] = (int) $_GET['series'];
	//$h2_series = isset($_GET['series']) && $_GET['series'] ? ' ' . sprintf(__('in&#8220;%s&#8221;'), single_series_title('' , false) ) : ''; //TODO: Keeping an eye out for a hook into the page title in future versions of WordPress
	?>
	<form name="searchform" id="seriessearchform" action="" method="get">
		<fieldset><legend><?php _e('Series&hellip;') ?></legend>
			<?php wp_dropdown_series('show_option_all='.__('All').'&hide_empty=1&show_count=1&selected='.$_GET['series']);?>
		</fieldset>
		<input type="submit" id="post-query-submit2" value="<?php _e('Filter by Series &#187;'); ?>" class="button" />
	</form>
<?php
}
//BELOW FOR IF MY patch [ticket #5899] get's accepted for future version of WP
/*add_action('manage_posts_title','orgSeries_manage_posts_title');
function orgSeries_manage_posts_title() {
	$h2_series = isset($_GET['series']) && $_GET['series'] ? ' ' . sprintf(__('in &#8220;%s&#8221;'), single_series_title('', false) ) : '';
	}
	
add_filter('edit_manage_posts_reorder', 'orgSeries_manage_posts_reorder');
function orgSeries_manage_posts_reorder($order) {
	$order = '';
	$order = _c('%1$s%2$s%3$s%4$s%5$s%6$s|You can reorder these: 1: Posts, 2: by {s}, 3: matching {s}, 4: in {s}, 5: and the series {s}, 6: during {s}');
	return $order;
}

add_filter('edit_manage_posts_titles', 'orgSeries_manage_posts_titles');
function orgSeries_manage_posts_titles($titles) {
	$h2_series = isset($_GET['series']) && $_GET['series'] ? ' ' . sprintf(__('in &#8220;%s&#8221;'), single_series_title('', false) ) : '';
	array_push($titles, $h2_series);
	
	return $titles;
}*/
//TODO add a function/ajaxified code  for calling up the other posts in the selected series. and have a box for choosing the order of the current post. - to add in future version
?>