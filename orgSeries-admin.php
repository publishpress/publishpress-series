<?php
/**
 * This file contains all the code that hooks orgSeries into the various pages of the WordPress administration.   Some hooks will reference other files (series management, and series options).
 *
 * @package Publishpress Series WordPress Plugin
 * @since 2.2
*/

global $pagenow, $orgseries;

/**
 * All the add_action and apply_filter hooks for this file go here
*/
//add action for admin-series.css and thickbox
add_action('admin_enqueue_scripts', 'orgSeries_admin_header');

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

// Load JavaScript and CSS
add_action('admin_enqueue_scripts','orgSeries_admin_assets');
//add footer credit
add_action( 'in_admin_footer', 'orgSeries_admin_footer' );

add_action('wp_ajax_ppseries_pro_migrate_series_by_ajax', 'ppseries_pro_migrate_series_by_ajax');
add_filter('rest_prepare_taxonomy', 'publishpress_series_remove_gutenberg_series_metabox', 100, 3);


/**
 * Remove series metabox for gutenberg
 *
 * @param object $response
 * @param object $taxonomy
 * @param array $request
 * 
 * @return object $response
 */
function publishpress_series_remove_gutenberg_series_metabox($response, $taxonomy, $request) {
        $context       = ! empty( $request['context'] ) ? $request['context'] : 'edit';
        $taxonomy_name = isset($taxonomy->name) ? $taxonomy->name : false;

        // Context is edit in the editor
        if ($taxonomy_name === ppseries_get_series_slug() && $context === 'edit') {
            $data_response = $response->get_data();
            $data_response['visibility']['show_ui'] = false;
            $response->set_data($data_response);
        }

        return $response;
}

function ppseries_pro_migrate_series_by_ajax()
{

    //instantiate response default value
    $response['status'] = 'error';
    $response['content'] = '<font color="red">'.__('An error occured', 'organize-series').'</font>';

    $response['status'] = 'success';
    $response['content'] = sprintf(__('%1$s series migrated to new taxonomy', 'organize-series'), $count);

    wp_send_json($response);

}

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
	$plugin_path = PPSERIES_URL;
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

	if ( ( 'edit-tags.php' == $pagenow || 'term.php' == $pagenow  ) && ppseries_get_series_slug() == $_GET['taxonomy'] ) {
		orgSeries_manage_script();
	}

    wp_register_style('pps-admin-common', SERIES_PATH_URL . 'assets/css/pressshack-admin.css', [], ORG_SERIES_VERSION);
	if (is_ppseries_admin_pages()) {
		wp_enqueue_style( 'pps-admin-common' );
	}

}
function orgSeries_admin_assets() {

    wp_register_script( 'pps-admin-js', SERIES_PATH_URL . 'assets/js/admin.js', array( 'jquery' ), ORG_SERIES_VERSION );
    wp_register_style('pps-admin-common', SERIES_PATH_URL . 'assets/css/pressshack-admin.css', [], ORG_SERIES_VERSION);

	if (is_ppseries_admin_pages()) {
		wp_enqueue_style( 'pps-admin-common' );
        wp_enqueue_script( 'pps-admin-js' );
	}
}

function orgSeries_admin_footer() {

	if (is_ppseries_admin_pages()) {
	?>
        <div class="pressshack-admin-wrapper ppseries-footer-credit temporary">
	        <footer>
    	        <div class="pp-rating">
	                <a href="https://wordpress.org/support/plugin/organize-series/reviews/#new-post" target="_blank" rel="noopener noreferrer">
	                    <?php 
                        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                        printf(__('If you like %s, please leave us a %s rating. Thank you!', 'organize-series'), '<strong>PublishPress Series</strong>', '<span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-filled"></span>'
		    );
	?>              </a>
	            </div>

        	    <hr>
	            <nav>
	                <ul>
	                    <li><a href="https://publishpress.com/series/" target="_blank" rel="noopener noreferrer" title="<?php esc_attr_e('About PublishPress Series', 'organize-series');?>"><?php esc_html_e('About', 'organize-series');?></a></li>
    	                <li><a href=" https://publishpress.com/knowledge-base/start-series/" target="_blank" rel="noopener noreferrer" title="<?php esc_attr_e('PublishPress Series Documentation', 'organize-series');?>"><?php esc_html_e('Documentation', 'organize-series');?></a></li>
	                    <li><a href="https://publishpress.com/contact" target="_blank" rel="noopener noreferrer" title="<?php esc_attr_e('Contact the PublishPress team', 'organize-series');?>"><?php esc_html_e('Contact', 'organize-series');?></a></li>
	                    <li><a href="https://twitter.com/publishpresscom" target="_blank" rel="noopener noreferrer"><span class="dashicons dashicons-twitter"></span></a></li>
	                    <li><a href="https://facebook.com/publishpress" target="_blank" rel="noopener noreferrer"><span class="dashicons dashicons-facebook"></span></a></li>
	                </ul>
	            </nav>

    	        <div class="pp-pressshack-logo">
	                <a href="https://publishpress.com" target="_blank" rel="noopener noreferrer">
            	        <img src="<?php echo esc_url(SERIES_PATH_URL . 'assets/images/publishpress-logo.png') ;?>" />
	                </a>
	            </div>
	        </footer>
        </div>
        <div class="clear"></div>
	<?php
    }
}
function orgSeries_manage_script() {
    wp_enqueue_media();
	wp_enqueue_script( 'thickbox' );
	wp_enqueue_script('media-upload');
	wp_enqueue_script('orgseries_scripts', SERIES_PATH_URL . 'js/orgseries_scripts.js', array('jquery', 'thickbox', 'media-upload'), ORG_SERIES_VERSION, true);
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

	$name = sanitize_text_field($_POST['newseries']);

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
	$data[$seriesid] = isset($_POST['post_series']) ? sanitize_text_field($_POST['post_series']) : '';
	$data[$series_part] = isset($_POST['series_part']) ? sanitize_text_field($_POST['series_part']) : '';
	$data[$id] = isset($_POST['series_post_id']) ? sanitize_text_field($_POST['series_post_id']) : '';
	$data[$is_series_save] = isset($_POST['is_series_save']) ? sanitize_text_field($_POST['is_series_save']) : 0;
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
	if(isset( $post ) && is_object($post)){
		$post_ID = $post->ID;
	}else if (isset( $postdata ) && is_object($postdata)){
		$post_ID = $post->ID;
	}else {
		$post_ID = false;
	}

	if ( empty( $checked_series ) ) {
		if ( $post_ID ) {
			$checked_series = wp_get_post_series( $post_ID );
			$checked_series = is_array($checked_series) ? $checked_series : array();

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

		$unsorted_result = $result;
        usort( $result, '_usort_series_by_name' );
        $result = apply_filters(
            'get_series_list',
            $result,
            $unsorted_result
        );

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
		echo '<li id="series-0"><label for ="in-series-0" class="selectit"><input value="0" type="radio" name="post_series" id="in-series-0" checked="checked" /> <span class="li-series-name">' . esc_html__('Not part of a series', 'organize-series') . '</span></label></li>';
		foreach ( $series as $serial ) {
            $series_order_link = admin_url('edit.php?page=manage-issues&action=part&series_ID');
			echo '<li id="series-'. esc_attr($serial['series_ID']) .'">
                    <label for="in-series-'. esc_attr($serial['series_ID']) . '" class="selectit">
                        <input value="' .  esc_attr($serial['series_ID']) .  '" type="radio" name="post_series" id="in-series-' .  esc_attr($serial['series_ID']) .  '"' . ($serial['checked'] ? ' checked="checked"' : '' ) .  '/> 
                        <span class="li-series-name">' . esc_html( $serial['ser_name'] ) . "</span>
                        <a class='selected-series-order' style='text-decoration: none;display:none;' href='" . admin_url("edit.php?page=manage-issues&action=part&series_ID=".$serial['series_ID']."") . "' target='blank'>
                             ". __('Series Order', 'organize-series') ."
                             <span class='dashicons dashicons-external'></span>
                        </a>
                        
                    </label>
                </li>";

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
	$org_opt = $orgseries->settings;

    $metabox_show_series_part = isset($org_opt['metabox_show_series_part']) ? (int)$org_opt['metabox_show_series_part'] : 0;
    $metabox_show_post_title_in_widget = isset($org_opt['metabox_show_post_title_in_widget']) ? (int)$org_opt['metabox_show_post_title_in_widget'] : 0;
    $metabox_show_add_new = isset($org_opt['metabox_show_add_new']) ? (int)$org_opt['metabox_show_add_new'] : 0;
    $series_list = get_series_list(0);
	?>
    <div class="series-metadiv">
        <div class="tabs-panel">
	<p id="jaxseries">
    <span id="ajaxseries" style="<?php echo ($metabox_show_add_new === 0) ? 'display: none;' : ''; ?>"><input type="text" name="newseries" id="newseries" size="16" autocomplete="off"/><input type="button" name="Button" class="add:serieschecklist:jaxseries button" id="seriesadd" value="<?php echo esc_attr(__('Add New', 'organize-series')); ?>" /><input type="hidden"/><input type="hidden"/></span><span id="series-ajax-response"></span><span id="add-series-nonce" class="hidden"><?php echo wp_create_nonce('add-series-nonce'); ?></span>
    </p>
		<span id="series-ajax-response"></span>
        
        <?php if (is_array($series_list) && count($series_list) > 1) : ?>
            <div class="editor-series-search">
                <label for="editor-series-search-input"><?php esc_html_e('Search series', 'organize-series'); ?></label>
                <input class="editor-series-search-input components-text-control__input" id="editor-series-search-input" type="text">
            </div>
        <?php endif; ?>

		<ul id="serieschecklist" class="list:series serieschecklist categorychecklist form-no-clear">
				<?php write_series_list($series_list); ?>
		</ul>

        <div class="series-part-wrap" style="<?php echo ($metabox_show_series_part === 0) ? 'display: none;' : ''; ?>">
            <span id="seriespart">
                <strong><?php esc_html_e('Series Part:', 'organize-series'); ?></strong>
                <input class="small-text pp-series-part-input" min="1" type="number" name="series_part[<?php echo isset($ser_id[0]) ? esc_attr($ser_id[0]) : 0; ?>]" id="series_part" size="5" value="<?php echo esc_attr(get_post_meta($id, SERIES_PART_KEY, true)); ?>" oninput="this.value = 
 !!this.value && Math.abs(this.value) >= 0 ? Math.abs(this.value) : null" />
            </span>
            <p id="part-description" class="howto">
                <?php esc_html_e('If you leave this blank, this post will automatically be added to the end of the series.', 'organize-series'); ?>
            </p>
        </div>

        <div class="series-metabox-post-title-in-widget" style="<?php echo ($metabox_show_post_title_in_widget === 0) ? 'display: none;' : ''; ?>">
            <strong> <?php esc_html_e('Post title in widget:', 'organize-series'); ?></strong>
            <p id="part-description" class="howto">
                <?php esc_html_e('A short title of this post that will be used in the Series widget. Leave blank to use the full title.', 'organize-series'); ?>
            </p>
            <input type="text" name="serie_post_shorttitle[<?php echo isset($ser_id[0]) ? esc_attr($ser_id[0]) : 0; ?>]" id="serie_post_shorttitle" size="30" value="<?php echo esc_attr(get_post_meta($id, SPOST_SHORTTITLE_KEY, true)); ?>"/>
        </div>

		<input type="hidden" name="is_series_save" value="1" />
    </div>
    </div>
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
	$defaults[ppseries_get_series_slug()] = __('Series', 'organize-series');
	return $defaults;
}

function orgSeries_custom_column_action($column_name, $id) {
	global $orgseries;
	$seriesid = null;
	$series_part = null;
	$series_name = null;
	$column_content = null;
	$post_types = apply_filters('orgseries_posttype_support', array('post'));

	if ($column_name == ppseries_get_series_slug()) {
		$column_content .= '<div class="series_column">';
		$column_content .= '<input type="hidden" name="is_series_save" value="1" />';
		if ( $series = get_the_series($id, false) ) {
			$seriesid = $series[0]->term_id;
			$series_name = $series[0]->name;
			$series_link = admin_url("edit.php?page=manage-issues&action=part&series_ID=".$seriesid."");
			$series_part = get_post_meta($id, SERIES_PART_KEY, TRUE);
			$count = $series[0]->count;

			$draft_posts = get_posts( array(
				'post_type'	=> $post_types,
				'post_status' => array('draft', 'future', 'pending'),
				'taxonomy'	=> ppseries_get_series_slug(),
				'term'	=> $series_name
			) );
			$count_draft_posts = is_array($draft_posts) ? count($draft_posts) : 0;
			$drafts_included = '';
			if($count_draft_posts != 0){
				$all_serie_posts = $count_draft_posts+$count;
				$drafts_included = "($all_serie_posts)";
			}

			$post_status = get_post_status($id);

			if ($series && in_array( $post_status, array( 'publish', 'private' ) ) ) {
                if(empty(trim($series_part))){
                    $column_content .= sprintf(__('<a href="%1$s" title="%2$s">%3$s</a> - (Currently has no Part number)', 'organize-series'), $series_link, $series_name, $series_name);

                }else{
                    $column_content .= sprintf(__('Part %1$s of %2$s%6$s in the series <br/><a href="%3$s" title="%4$s">%5$s</a>', 'organize-series'), $series_part, $count, $series_link, $series_name, $series_name, $drafts_included);
                }
					$column_content .= '<div class="hidden" id="inline_series_' . $id . '"><div class="series_inline_edit">'.$seriesid.'</div><div class="series_inline_part">'.$series_part.'</div><div class="series_post_id">'.$id.'</div><div class="series_inline_name">'.$series_name.'</div></div>';
				} else {
					if(empty(trim($series_part))){
						$column_content .= sprintf(__('<a href="%1$s" title="%2$s">%3$s</a> - (Currently has no Part number)', 'organize-series'), $series_link, $series_name, $series_name);
					}else{
						$column_content .= sprintf(__('<a href="%1$s" title="%2$s">%3$s</a> - (currently set as Part %4$s)', 'organize-series'), $series_link, $series_name, $series_name, $series_part);
					}
					$column_content .= '<div class="hidden" id="inline_series_' . $id . '"><div class="series_inline_edit">'.$seriesid.'</div><div class="series_inline_part">'.$series_part.'</div><div class="series_post_id">'.$id.'</div><div class="series_inline_name">'.$series_name.'</div></div>';
				}
		} else {
			$column_content .= '<div class="hidden" id="inline_series_' . $id . '"><div class="series_inline_edit">'.$seriesid.'</div><div class="series_inline_part">'.$series_part.'</div><div class="series_post_id">'.$id.'</div><div class="series_inline_name">'.$series_name.'</div></div>';
			$column_content .= '<em>'. esc_html__('No Series', 'organize-series').'</em>';
		}
		$column_content .= '</div>';
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $column_content;
	}
}

function orgSeries_custom_manage_posts_filter() {
	global $orgseries;
	$series_name = '';
	if (isset($_GET[SERIES_QUERYVAR])) $series_name = sanitize_text_field($_GET[SERIES_QUERYVAR]);

	wp_dropdown_series('show_option_all='. esc_attr__('View all series', 'organize-series').'&hide_empty=0&show_count=0&selected='.$series_name);
}

function add_series_management_link() {
	global $orgseries;
	$link = get_option('siteurl') . '/wp-admin/edit.php?page=' .  SERIES_DIR . '/orgSeries-manage.php';
	?>
	<li><a href="<?php echo esc_url($link); ?>"><?php esc_html_e('Manage All Series', 'organize-series'); ?></a></li>
	<?php
}

function add_series_to_right_now() {
	global $orgseries;
	$num_series = wp_count_terms(ppseries_get_series_slug());
	$num = number_format_i18n( $num_series );
	$text = _n( 'Series', 'Series', $num_series, 'organize-series' );
	$manage_link = get_option('siteurl') . '/wp-admin/edit-tags.php?taxonomy=' . SERIES_QUERYVAR;
	if ( current_user_can( 'manage_series' ) ) {
		$series_num = "<a href='". esc_url($manage_link) ."'>$num</a>";
		$series_text = "<a href='". esc_url($manage_link). "'>$text</a>";
	}

	echo '<tr>';
    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo '<td class="first b b-tags">'.$series_num.'</td>';
    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo '<td class="t tags">' . $series_text . '</td>';
	echo '<td></td><td></td></tr>';
}
?>
