<?php
/* This page hooks into the Manage Series page that was automatically added by WordPress custom-taxonomy
*/

//hook into the edit columns on "manage series" page
add_filter('manage_edit-'.ppseries_get_series_slug().'_columns', 'manage_series_columns');
add_filter('manage_'.ppseries_get_series_slug().'_custom_column', 'manage_series_columns_action',1,3);
add_action(''.ppseries_get_series_slug().'_edit_form_fields','edit_series_form_fields', 10,2);
add_action(''.ppseries_get_series_slug().'_add_form_fields', 'add_series_form_fields', 10);
//hooking into insert_term, update_term and delete_term
add_action('created_'.ppseries_get_series_slug().'', 'wp_insert_series', 10, 2);
add_action('edited_'.ppseries_get_series_slug().'', 'wp_update_series', 10, 2);
add_action('delete_'.ppseries_get_series_slug().'', 'wp_delete_series', 10, 2);
add_action('wp_ajax_pp_series_reordering_terms', 'ajax_pp_series_reordering_terms');

if ( ! empty( $_REQUEST['taxonomy'] ) && in_array( $_REQUEST['taxonomy'], [ppseries_get_series_slug(), 'series_group'], true ) ) {
    add_action( 'load-edit-tags.php', 'pp_series_edit_tags' );
}
add_filter('terms_clauses', 'pp_series_terms_clauses', 10, 3);
add_filter('get_terms_orderby', 'pp_series_get_terms_orderby', 1, 2);

function pp_series_edit_tags() {
    add_action('admin_print_scripts-edit-tags.php', 'pp_series_term_enqueue_scripts');
    add_action('admin_head-edit-tags.php', 'pp_series_term_admin_head');
    add_action('admin_head-edit-tags.php', 'pp_series_term_help_tab');
    add_action( 'quick_edit_custom_box', 'pp_series_quick_edit_term_order', 10, 3 );
}

function pp_series_term_enqueue_scripts() {
    wp_enqueue_script('pp-series-term-order-quick-edit', SERIES_PATH_URL . 'js/term-quick-edit.js', array( 'jquery' ), ORG_SERIES_VERSION, true);
    wp_enqueue_script('pp-series-term-order-reorder', SERIES_PATH_URL . 'js/term-reorder.js', array( 'jquery-ui-sortable' ), ORG_SERIES_VERSION, true);

    wp_localize_script('pp-series-term-order-reorder', 'orderL10n', array(
        'nonce' => wp_create_nonce('series-order-nonce'),
    ));
}

function pp_series_quick_edit_term_order($column_name = '', $screen = '', $name = '') {

    // Bail if not the `order` column on the `edit-tags` screen for a visible taxonomy
    if ( ( 'order' !== $column_name ) || ( 'edit-tags' !== $screen ) || ! in_array( $name, [ppseries_get_series_slug(), 'series_group'], true ) ) {
        return false;
    } ?>

    <fieldset>
        <div class="inline-edit-col">
            <label>
                <span class="title"><?php esc_html_e( 'Order', 'organize-series' ); ?></span>
                <span class="input-text-wrap">
                    <input type="number" pattern="[0-9.]+" class="ptitle" name="order" value="" size="11">
                </span>
            </label>
        </div>
    </fieldset>

    <?php
}

function pp_series_term_help_tab() {

    get_current_screen()->add_help_tab(array(
        'id'      => 'wp_term_order_help_tab',
        'title'   => __( 'Term Order', 'organize-series' ),
        'content' => '<p>' . __( 'To reposition an item, drag and drop the row by "clicking and holding" it anywhere and moving it to its new position.', 'organize-series' ) . '</p>',
    ) );
}

function pp_series_term_admin_head() {
    ?>

    <style type="text/css">
        .column-order {
            text-align: center;
            width: 74px;
        }

        .wp-list-table .ui-sortable tr:not(.no-items) {
            cursor: move;
        }

        .striped.dragging > tbody > .ui-sortable-helper ~ tr:nth-child(even) {
            background: #f9f9f9;
        }

        .striped.dragging > tbody > .ui-sortable-helper ~ tr:nth-child(odd) {
            background: #fff;
        }

        .wp-list-table .to-updating tr,
        .wp-list-table .ui-sortable tr.inline-editor {
            cursor: default;
        }

        .wp-list-table .ui-sortable-placeholder {
            outline: 1px dashed #bbb;
            background: #f1f1f1 !important;
            visibility: visible !important;
        }
        .wp-list-table .ui-sortable-helper {
            background-color: #fff !important;
            outline: 1px solid #bbb;
            box-shadow: 0 3px 6px rgba(0, 0, 0, 0.175);
        }
        .wp-list-table .ui-sortable-helper .row-actions {
            visibility: hidden;
        }
        .to-row-updating .check-column {
            background: url('<?php echo admin_url( '/images/spinner.gif' );?>') 10px 9px no-repeat;
        }
        @media print,
        (-o-min-device-pixel-ratio: 5/4),
        (-webkit-min-device-pixel-ratio: 1.25),
        (min-resolution: 120dpi) {
            .to-row-updating .check-column {
                background-image: url('<?php echo admin_url( '/images/spinner-2x.gif' );?>');
                background-size: 20px 20px;
            }
        }
        .to-row-updating .check-column input {
            visibility: hidden;
        }

    </style>

    <?php
}

// note following function WILL NOT delete the actual image file from the server.  I don't think it's needed at this point.
function wp_delete_series($series_ID, $taxonomy_id) {
	global $wpdb;
	seriesicons_delete($series_ID);
	wp_reset_series_order_meta_cache('',$series_ID,TRUE);
}

function wp_insert_series($series_id, $taxonomy_id) {
	global $_POST;
	$series_icon_loc = '';

	extract($_POST, EXTR_SKIP);
	$series_icon = isset($_POST['series_icon_loc']) ? sanitize_text_field($_POST['series_icon_loc']) : null;

	if ( isset($series_icon) || $series_icon != '' ) {
		$build_path = seriesicons_url();
		$series_icon = str_replace($build_path, '', $series_icon);
	}

	$series_icon = seriesicons_write($series_id, $series_icon);
}

function wp_update_series($series_id, $taxonomy_id) {
	global $_POST;
	extract($_POST, EXTR_SKIP);
	if ( empty($series_icon_loc) ) $series_icon_loc = '';
	if ( empty($delete_image) ) $delete_image = false;

	$series_icon = $series_icon_loc;

	if ( !empty($series_icon) || $series_icon != '' ) {
		$build_path = seriesicons_url();
		$series_icon = str_replace($build_path, '', $series_icon);

	}

	if ($delete_image) {
		seriesicons_delete(absint($series_id));
	} else {
		$series_icon = seriesicons_write(absint($series_id), sanitize_text_field($series_icon));
	}
}

function manage_series_columns($columns) {
	global $orgseries, $pagenow;
	$columns['series_order'] = __('Series Order', 'organize-series');
	$columns['series_id'] = __('ID', 'organize-series');
	return $columns;
}

function manage_series_columns_action($content, $column_name, $id) {
	global $orgseries;
	$output = $content;	
	if ($column_name == 'series_order') {
		$output .= '<a href="' . admin_url('edit.php?page=manage-issues&action=part&series_ID='.$id.'') . '" title="' . __('Series Order', 'organize-series') . '">'. __('Series Order', 'organize-series') .' </a>';
	}

	if ($column_name === 'series_id') {
		$output .= $id;
	}
	return $output;
}

function add_series_form_fields($taxonomy) {
	global $orgseries;
	?>
    <div class="form-field form-required">
			<label for="order">
				<?php esc_html_e( 'Order', 'organize-series' ); ?>
			</label>
			<input type="number" pattern="[0-9.]+" name="order" id="order" value="0" size="11">
			<p class="description">
				<?php esc_html_e( 'Set a specific order by entering a number (1 for first, etc.) in this field.', 'organize-series' ); ?>
			</p>
	</div>
	<div class="form-field">
		<div style="float:left;" id="selected-icon"></div>
		<div style="clear:left;"></div>
		<label for="series_icon">
			<input id="series_icon_loc_display" type="text" style="width: 70%;" name="series_icon_loc_display" value="" disabled="disabled" /><input style="float:right; width: 100px;" id="upload_image_button" type="button" value="Select Image" />
			<input id="series_icon_loc" type="hidden" name="series_icon_loc" />
			<p><?php _e('Upload an image for the series.', 'organize-series') ?></p>
		</label>
	</div>
	<?php
}

function edit_series_form_fields($series, $taxonomy) {
	global $orgseries;
	$series_icon = get_series_icon('fit_width=100&fit_height=100&link=0&expand=true&display=0&series='.$series->term_id);
	$icon_loc = series_get_icons($series->term_id);
	if ($icon_loc || $icon_loc != ''){
		$series_icon_loc = seriesicons_url() . $icon_loc;
    }else{
	    $series_icon_loc = '';
    }
	?>
    <tr class="form-field">
		<th scope="row" valign="top">
			<label for="order">
				<?php esc_html_e( 'Order', 'organize-series' ); ?>
			</label>
		</th>
		<td>
		<input name="order" id="order" type="text" value="<?php echo $series->term_order; ?>" size="11" />
			<p class="description">
				<?php esc_html_e( 'Terms are usually ordered alphabetically, but you can choose your own order by entering a number (1 for first, etc.) in this field.', 'organize-series' ); ?>
			</p>
		</td>
	</tr>

			<tr valign="top">
				<?php if ( $series->term_id != '' ) { ?>
				<th scope="row"><?php _e('Current series icon:', 'organize-series'); ?></th><?php } ?>
				<td>
					<?php if ($series_icon != '') {
                            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							echo $series_icon;
						} else {
							echo '<p>'. esc_html__('No icon currently', 'organize-series') .'</p>';
						}
					 ?>
					<div id="selected-icon"></div>
				</td>
			</tr>
			<?php if ( $series_icon != '' ) { ?>
			<tr>
				<th></th>
				<td>
				<p style="width: 50%;"><input style="margin-top: 0px;" name="delete_image" id="delete_image" type="checkbox" value="true" />  <?php _e('Delete image? (note: there will not be an image associated with this series if you select this)', 'organize-series'); ?></p>
				</td>
			</tr>
			<?php } ?>
			<tr valign="top">
				<th scope="row"><?php _e('Series Icon Upload:', 'organize-series') ?></th>
				<td><label for="series_icon">
					<input id="series_icon_loc_display" type="text" size="36" name="series_icon_loc_display" value="" disabled="disabled"/>
					<input id="upload_image_button" type="button" value="Select Image" />
					<p><?php _e('Upload an image for the series.', 'organize-series'); ?></p>
					<input id="series_icon_loc" type="hidden" name="series_icon_loc" />
					</label>
				</td>
			</tr>
	<?php
} 

function ajax_pp_series_reordering_terms() {
    global $wpdb;

    $return_data = new stdClass;
    $return_data->status = 'error';
    // Bail if required term data is missing
    if (!isset($_POST['terms']) || !isset($_POST['nonce']) || !isset($_POST['tax']) || !is_array($_POST['terms'])) {
        $return_data->message = __('Invalid form data', 'organize-series');
        die( json_encode( $return_data ) );
    }

    $terms = array_map('sanitize_text_field', $_POST['terms']);
    $nonce = sanitize_text_field($_POST['nonce']);
    $tax = sanitize_text_field($_POST['tax']);

    //bail if invalid nonce
    if (!wp_verify_nonce($nonce, 'series-order-nonce')) {
        $return_data->message = __('Invalid nonce, reload and try again', 'organize-series');
        die( json_encode( $return_data ) );
    }

    // Attempt to get the taxonomy
    $tax = get_taxonomy($tax);

    // Bail if taxonomy does not exist
    if ( empty( $tax ) ) {
        $return_data->message = __('Invalid taxonomy', 'organize-series');
        die( json_encode( $return_data ) );
    }

    // Bail if current user cannot assign terms
    if ( ! current_user_can( $tax->cap->edit_terms ) ) {
        $return_data->message = __('Not enough permission', 'organize-series');
        die( json_encode( $return_data ) );
    }

    $return_data->message = __('Updated successfully', 'organize-series');
    
    foreach($terms as $key => $term_id) {
        $wpdb->update( $wpdb->terms, array('term_order' => ($key + 1)), array('term_id' => $term_id) );

    }
    clean_term_cache($terms);

    die( json_encode( $return_data ) );
}

function pp_series_terms_clauses($clauses, $taxonomies, $args) {

    if (!in_array(ppseries_get_series_slug(), $taxonomies) && !in_array('series_group', $taxonomies)) {
        return $clauses;
    }
    
    if (is_admin()) {
        //return if use orderby columns
        if (isset($_GET['orderby']) && $_GET['orderby'] !=  'term_order') {
            return $clauses;
        }
        if ((!isset($args['ignore_term_order']) ||  (isset($args['ignore_term_order']) && $args['ignore_term_order']  !== true))) {
            $clauses['orderby'] =   'ORDER BY t.term_order';
        }
        
        return $clauses;
    }
    
    if ((!isset($args['ignore_term_order']) ||  (isset($args['ignore_term_order'])  &&  $args['ignore_term_order']  !== true) ) ) {
        $clauses['orderby'] =   'ORDER BY t.term_order';
    }
                
    return $clauses; 
}

function pp_series_get_terms_orderby($orderby, $args)
{
    if (!isset($args['taxonomy'])) {
        return $orderby;
    }
    if (!in_array(ppseries_get_series_slug(), $args['taxonomy']) && !in_array('series_group', $args['taxonomy'])) {
        return $orderby;
    }
    
    if (isset($args['orderby']) && $args['orderby'] == "term_order" && $orderby != "term_order") {
        return "t.term_order";
    }
    
    return $orderby;
}
?>