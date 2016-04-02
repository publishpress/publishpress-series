<?php
/**
 * This file contains all the code that hooks orgSeries into the various pages of the WordPress administration.   Some hooks will reference other files (series management, and series options).
 *
 * @package Organize Series WordPress Plugin
 * @since 2.2
*/

global $pagenow, $orgseries;

/**
 * All the add_action and apply_filter hooks for this file go here
*/
//add action for admin-series.css and thickbox
add_action('admin_print_styles', 'orgSeries_admin_header');

##ADMIN-Write/Edit Post Script loader
add_action('admin_print_scripts','orgSeries_admin_script');
add_action('admin_print_scripts-post.php', 'orgSeries_post_script');
add_action('admin_print_scripts-post-new.php', 'orgSeries_post_script');
add_action('admin_print_scripts-edit-tags.php', 'orgSeries_manage_script');

//add ajax for on-the-fly series adds
add_action('wp_ajax_add_series', 'admin_ajax_series');
//add_action('wp_ajax_inline-series', 'admin_inline_series_ajax');
add_action('admin_init', 'orgseries_load_custom_column_actions', 10);
add_action('admin_init', 'orgseries_load_custom_column_filters', 10);

function orgseries_load_custom_column_actions() {
	//support for custom post types
	$posttypes = apply_filters('orgseries_posttype_support', array('post') );
	foreach ( $posttypes as $posttype ) {
		$action_ref = ( $posttype == 'post' ) ? 'manage_posts_custom_column' : 'manage_' . $posttype . 'posts_custom_column';
		$action_ref = ( $posttype == 'page' ) ? 'manage_pages_custom_column' : $action_ref;
		add_action($action_ref,'orgSeries_custom_column_action', 12, 2);
	}
}

function orgseries_load_custom_column_filters() {
	//support for custom post types
	$posttypes = apply_filters('orgseries_posttype_support', array('post') );
	foreach ( $posttypes as $posttype ) {
		$filter_ref = ( $posttype == 'page' ) ? 'manage_pages_columns' : 'manage_posts_columns';
		add_filter($filter_ref, 'orgSeries_custom_column_filter');
	}
}


if ( $pagenow != 'upload.php' )
		add_action('restrict_manage_posts', 'orgSeries_custom_manage_posts_filter');

add_action('post_relatedlinks_list', 'add_series_management_link');

add_action( 'right_now_content_table_end', 'add_series_to_right_now');

//function: Add Meta-box
add_action('add_meta_boxes', 'orgseries_add_meta_box', 9);

function orgSeries_admin_header() {
	$plugin_path = SERIES_LOC;
	$csspath = $plugin_path . "orgSeries-admin.css";
	wp_register_style( 'orgSeries-admin', $csspath );
	wp_enqueue_style( 'orgSeries-admin' );
	wp_enqueue_style( 'thickbox' );
}

//add_action filter for the manage_series page...

function orgSeries_post_script() {
	wp_enqueue_script('ajaxseries');
}
function orgSeries_admin_script() {
	//load in the series.js script and set localization variables.
	global $pagenow;

	if ( ( 'edit-tags.php' == $pagenow || 'term.php' == $pagenow  ) && 'series' == $_GET['taxonomy'] ) {
		orgSeries_manage_script();
	}

}

function orgSeries_manage_script() {
	wp_enqueue_script( 'thickbox' );
	wp_enqueue_script('media-upload');
	wp_enqueue_script('orgseries_scripts', SERIES_PATH_URL . '/js/orgseries_scripts.js', array('jquery', 'thickbox'), ORG_SERIES_VERSION, true);
}

######ON THE FLY ADD SERIES########
function admin_ajax_series() {
	$response = array();

	if ( !current_user_can( 'manage_series' ) )
		$response['error'] = __('Sorry but you don\'t have permission to add series', 'organize-series');

	if ( ! check_ajax_referer ( 'add-series-nonce', 'addnonce', false ) ) {
		$response['error'] = 'Sorry but security check failed';
	}
	$new_nonce = wp_create_nonce('add-series-nonce');

	$name = $_POST['newseries'];

	$series_name = trim($name);
	if ( !$series_nicename = sanitize_title($series_name) )
		$response['error'] = __('The name you picked isn\'t sanitizing correctly. Try something different.', 'organize-series');
	if (  empty( $response['error'] ) ) {
		if ( !$series_id = series_exists( $series_name ) ) {
			$ser_id = wp_create_single_series( $series_name );
			$series_id = $ser_id['term_id'];
		} else {
			$response['error'] = __('Hmm... it looks like there is already a series with that name. Try something else', 'organize-series');
		}

		$series_name = esc_html(stripslashes($series_name));
	}

	if ( empty( $response['error'] ) ) {
		$response = array(
			'id' => $series_id,
			'html' => "<li id='series-$series_id' class='series-added-indicator'><label for='in-series-$series_id' class='selectit'><input value='$series_id' type='radio' name='post_series' id='in-series-$series_id' checked /><input type='hidden' name='is_series_save' value='1' /> <span class='li-series-name'>$series_name</span></label><span id='new_series_id' class='hidden'>$series_id</span></li>",
			'new_nonce' => $new_nonce,
			'error' => false
			);
	}
	echo json_encode($response);
	exit();
}

###AJAX FOR INLINE_SERIES UPDATE###
function admin_inline_series_ajax() {
	check_ajax_referer ( 'inlineeditnonce', '_inline_edit' );
	$data[$seriesid] = isset($_POST['post_series']) ? $_POST['post_series'] : '';
	$data[$series_part] = isset($_POST['series_part']) ? $_POST['series_part'] : '';
	$data[$id] = isset($_POST['series_post_id']) ? $_POST['series_post_id'] : '';
	$data[$is_series_save] = isset($_POST['is_series_save']) ? $_POST['is_series_save'] : 0;
	/*$series_id = $_POST['post_series'];
	$part = $_POST['series_part'];
	$post_id = $_POST['series_post_id'];*/
	//orgSeries_custom_column_action('series', $data[$id]);
	exit;
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
 * @uses esc_html
 * @param array|mixed $series - contains series_ID, ser_name, and checked (does post belong to this series)
*/
function write_series_list( $series ) { //copied from write_nested_categories in template.php
	global $orgseries;
		echo '<li id="series-0"><label for ="in-series-0" class="selectit"><input value="0" type="radio" name="post_series" id="in-series-0" checked="checked" /><span class="li-series-name">' . __('Not part of a series', 'organize-series') . '</span></label></li>';
		foreach ( $series as $serial ) {
			echo '<li id="series-'. $serial['series_ID'].'"><label for="in-series-'. $serial['series_ID']. '" class="selectit"><input value="' .  $serial['series_ID'] .  '" type="radio" name="post_series" id="in-series-' .  $serial['series_ID'] .  '"' . ($serial['checked'] ? ' checked="checked"' : '' ) .  '/> <span class="li-series-name">' . esc_html( $serial['ser_name'] ) . "</span></label></li>";

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
	$ser_id = wp_get_post_series( $id );
	?>
	<p id="jaxseries"></p>
		<span id="series-ajax-response"></span>
		<ul id="serieschecklist" class="list:series serieschecklist form-no-clear">
				<?php get_series_to_select(); ?>
		</ul>
		<span id="seriespart"><strong> <?php _e('Series Part:', 'organize-series'); ?>   </strong><input type="text" name="series_part[<?php echo isset($ser_id[0]) ? $ser_id[0] : 0; ?>]" id="series_part" size="5" value="<?php echo get_post_meta($id, SERIES_PART_KEY, true); ?>" /></span>
			<p id="part-description"><?php _e('Note: that if you leave this blank or enter an invalid number the post will automatically be appended to the rest of the posts in the series', 'organize-series'); ?></p></br>
		<strong> <?php _e('Post title in widget:', 'organize-series'); ?></strong>
		<p id="part-description"><?php _e('A "short" title of the post that will be used in the series widget. [Leave blank to use a full title]', 'organize-series'); ?></p>
		<input type="text" name="serie_post_shorttitle[<?php echo isset($ser_id[0]) ? $ser_id[0] : 0; ?>]" id="serie_post_shorttitle" size="30" value="<?php echo get_post_meta($id, SPOST_SHORTTITLE_KEY, true); ?>"/>
		<input type="hidden" name="is_series_save" value="1" />
	<?php
}

function orgseries_add_meta_box() {
	global $orgseries;
	$posttypes = apply_filters('orgseries_posttype_support', array('post') );
	foreach ( $posttypes as $posttype ) {
		add_meta_box('seriesdiv', __('Series', 'organize-series'), 'series_edit_meta_box', $posttype, 'side');
		remove_meta_box('tagsdiv-series', $posttype, 'side'); //removes series meta box added by WordPress Taxonomy api.
	}
}

/* ADDING SERIES INFO TO EDIT POST PAGE */

function orgSeries_custom_column_filter($defaults) {
	global $orgseries;
	$post_types = apply_filters( 'orgseries_posttype_support', array('post') );
	if ( isset($_REQUEST['post_type']) && !in_array($_REQUEST['post_type'], $post_types) )
		return $defaults; //get out we only want this showing up on post post types for now.*/
	$defaults['series'] = __('Series', 'organize-series');
	return $defaults;
}

function orgSeries_custom_column_action($column_name, $id) {
	global $orgseries;
	$seriesid = null;
	$series_part = null;
	$series_name = null;
	$column_content = null;
	$post_types = apply_filters('orgseries_posttype_support', array('post'));

	if ($column_name == 'series') {
		$column_content .= '<div class="series_column">';
		$column_content .= '<input type="hidden" name="is_series_save" value="1" />';
		if ( $series = get_the_series($id, false) ) {
			$seriesid = $series[0]->term_id;
			$series_name = $series[0]->name;
			$series_link = get_series_link($series_name);
			$series_part = get_post_meta($id, SERIES_PART_KEY, TRUE);
			$count = $series[0]->count;

			$draft_posts = get_posts( array(
				'post_type'	=> $post_types,
				'post_status' => array('draft', 'future', 'pending'),
				'taxonomy'	=> 'series',
				'term'	=> $series_name
			) );
			$count_draft_posts = count($draft_posts);
			$drafts_included = '';
			if($count_draft_posts != 0){
				$all_serie_posts = $count_draft_posts+$count;
				$drafts_included = "($all_serie_posts)";
			}

			$post_status = get_post_status($id);

			if ($series && in_array( $post_status, array( 'publish', 'private' ) ) ) {
					$column_content .= sprintf(__('Part %1$s of %2$s%6$s in the series <br/><a href="%3$s" title="%4$s">%5$s</a>', 'organize-series'), $series_part, $count, $series_link, $series_name, $series_name, $drafts_included);
					$column_content .= '<div class="hidden" id="inline_series_' . $id . '"><div class="series_inline_edit">'.$seriesid.'</div><div class="series_inline_part">'.$series_part.'</div><div class="series_post_id">'.$id.'</div><div class="series_inline_name">'.$series_name.'</div></div>';
				} else {
					$column_content .= sprintf(__('<a href="%1$s" title="%2$s">%3$s</a> - (currently set as Part %4$s)', 'organize-series'), $series_link, $series_name, $series_name, $series_part);
					$column_content .= '<div class="hidden" id="inline_series_' . $id . '"><div class="series_inline_edit">'.$seriesid.'</div><div class="series_inline_part">'.$series_part.'</div><div class="series_post_id">'.$id.'</div><div class="series_inline_name">'.$series_name.'</div></div>';
				}
		} else {
			$column_content .= '<div class="hidden" id="inline_series_' . $id . '"><div class="series_inline_edit">'.$seriesid.'</div><div class="series_inline_part">'.$series_part.'</div><div class="series_post_id">'.$id.'</div><div class="series_inline_name">'.$series_name.'</div></div>';
			$column_content .= '<em>'.__('No Series', 'organize-series').'</em>';
		}
		$column_content .= '</div>';
		echo $column_content;
	}
}

function orgSeries_custom_manage_posts_filter() {
	global $orgseries;
	$series_name = '';
	if (isset($_GET[SERIES_QUERYVAR])) $series_name = $_GET[SERIES_QUERYVAR];

	wp_dropdown_series('show_option_all='.__('View all series', 'organize-series').'&hide_empty=0&show_count=0&selected='.$series_name);
}

function add_series_management_link() {
	global $orgseries;
	$link = get_option('siteurl') . '/wp-admin/edit.php?page=' .  SERIES_DIR . '/orgSeries-manage.php';
	?>
	<li><a href="<?php echo $link; ?>"><?php _e('Manage All Series', 'organize-series'); ?></a></li>
	<?php
}

function add_series_to_right_now() {
	global $orgseries;
	$num_series = wp_count_terms('series');
	$num = number_format_i18n( $num_series );
	$text = _n( 'Series', 'Series', $num_series, 'organize-series' );
	$manage_link = get_option('siteurl') . '/wp-admin/edit-tags.php?taxonomy=' . SERIES_QUERYVAR;
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
