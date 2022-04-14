<?php
/*
Plugin Name: PublishPress Series Publisher
Plugin URI: http://organizeseries.com
Description: Allows an editor to publish an "issue", which is to say, all pending posts with a given series. Until a series is published, all posts with that series will remain in the pending state.  Credit really needs to go to  <a href="http://xplus3.net">Jonathan Brinley</a> for his original Issue Manage plugin because all I did was modify it for use with series rather than categories.  Also NOTE that this REQUIRES PublishPress Series to be installed.
Version: 2.2.5.rc.000
Author: Darren Ethier
Author URI: http://organizeseries.com
*/

if (!defined('OS_PUBLISHER_VERSION')) {
  define('OS_PUBLISHER_VERSION', '2.2.5.rc.000');
}


if (!function_exists('series_issue_manager_manage_page')) {
function series_issue_manager_manage_page() {
  if ( function_exists('add_submenu_page') ) {
    $page = add_submenu_page( 'edit.php', __('Manage Series Issues','organize-series'), __('Publish Series','organize-series'), 'publish_posts', 'manage-issues', 'series_issue_manager_admin' );
    add_action("admin_print_scripts-$page", 'series_issue_manager_scripts');
  }
}
}

if (!function_exists('series_issue_manager_admin')) {
function series_issue_manager_admin() {
  $published = get_option( 'im_published_series' );
  $unpublished = get_option( 'im_unpublished_series' );
  $series = get_series( 'orderby=name&hide_empty=0' );

  // Make sure the options exist
  if ( $published === FALSE ) { $published = array(); update_option( 'im_published_series', $published ); }
  if ( $unpublished === FALSE ) { $unpublished = array(); update_option( 'im_unpublished_series', $unpublished ); }

  // See if we have GET parameters
  $series_ID = isset($_GET['series_ID'])? (int)$_GET['series_ID']:null;
  $action = isset($_GET['action'])? sanitize_text_field($_GET['action']):null;

  if ( $series_ID ) {
    $series_ID = (int)$series_ID;
    switch($action) {
      case "part":
        include_once('series_im_article_part.php');
        break;
      case "order":
        $post_IDs = isset($_GET['posts'])?sanitize_text_field($_GET['posts']):null;
        if ( $post_IDs ) series_issue_manager_part($series_ID, $post_IDs);
        include_once('series_im_admin_main.php');
        break;
        case "publish":
          $post_IDs = isset($_GET['posts'])?sanitize_text_field($_GET['posts']):null;
          $pub_time['mm'] = isset($_GET['mm'])?sanitize_text_field($_GET['mm']):null;
          $pub_time['jj'] = isset($_GET['jj'])?sanitize_text_field($_GET['jj']):null;
          $pub_time['aa'] = isset($_GET['aa'])?sanitize_text_field($_GET['aa']):null;
          $pub_time['hh'] = isset($_GET['hh'])?sanitize_text_field($_GET['hh']):null;
          $pub_time['mn'] = isset($_GET['mn'])?sanitize_text_field($_GET['mn']):null;
          if ( $post_IDs ) series_issue_manager_publish($series_ID, $post_IDs, $pub_time, $published, $unpublished);
          include_once('series_im_admin_main.php');
          break;
      case "unpublish":
        series_issue_manager_unpublish($series_ID, $published, $unpublished);
        include_once('series_im_admin_main.php');
        break;
      case "ignore":
        // stop tracking the series_ID
        $key = array_search($series_ID, $published);
        if ( FALSE !== $key ) {
          array_splice($published, $key, 1);
          update_option( 'im_published_series', $published );
        }
        $key = array_search($series_ID, $unpublished);
        if ( FALSE !== $key ) {
          array_splice($unpublished, $key, 1);
          update_option( 'im_unpublished_series', $unpublished );
        }
        include_once('series_im_admin_main.php');
        break;
      default:
        include_once('series_im_admin_main.php');
        break;
    }
  } else {
    include_once('series_im_admin_main.php');
  }
}
}

if (!function_exists('series_issue_manager_part')) {
function series_issue_manager_part( $series_ID, $post_IDs) {


    //delete all series part
    foreach ( explode(',',$post_IDs) as $post_ID ) {
        $part_key = apply_filters('orgseries_part_key', SERIES_PART_KEY, $series_ID);
		delete_post_meta( $post_ID, $part_key);
        add_post_meta($post_ID, $part_key, '');
    }

    // $post_IDs should have all pending posts' IDs in the series
    $counter = 0;
    $current_sn = 0;
    foreach ( explode(',',$post_IDs) as $post_ID ) {
        $current_sn++;
      $post_ID = (int)$post_ID;
      $post = get_post( $post_ID );
      publisher_wp_set_post_series( $post,true,$post_ID, $series_ID, false );
      $counter++;
    }
}
}

if (!function_exists('series_issue_manager_publish')) {
function series_issue_manager_publish( $series_ID, $post_IDs, $pub_time, &$published, &$unpublished ) {

  // take the series out of the unpublished list
  $key = array_search( $series_ID, $unpublished );
  if ( FALSE !== $key ) {
    array_splice( $unpublished, $key, 1 );
    update_option( 'im_unpublished_series', $unpublished );
  }
  //if ( !in_array( $series_ID, $published ) ) 
  {
    // add to the published list
    $published[] = $series_ID;
    sort($published);
    update_option( 'im_published_series', $published );

    // see if we have a valid publication date/time
    $publish_at = strtotime( $pub_time['aa'].'-'.$pub_time['mm'].'-'.$pub_time['jj'].' '.$pub_time['hh'].':'.$pub_time['mn'] );

    if ( !$publish_at ) {
      $publish_at = strtotime(current_time('mysql'));
    }

    // $post_IDs should have all pending posts' IDs in the series
    $counter = 0;
    $current_sn = 0;
    foreach ( explode(',',$post_IDs) as $post_ID ) {
        $current_sn++;
      $post_ID = (int)$post_ID;
      $post = get_post( $post_ID );
      // set the date to about the appropriate time, keeping a small gap so posts stay in order
      wp_update_post( array(
        'ID' => $post->ID,
        'post_date' => date( 'Y-m-d H:i:s', $publish_at-($counter+1) ),
        'post_date_gmt' => '',
        'post_status' => 'publish'
      ) );

      if($publish_at > strtotime(current_time('mysql'))) {
        // scheduled
	    publisher_wp_set_post_series( $post,true,$post_ID, $series_ID, false, $current_sn );
      }else{
        publisher_wp_set_post_series( $post,true,$post_ID, $series_ID, false );
      }
      $counter++;
    }
  }
}
}

if (!function_exists('series_issue_manager_unpublish')) {
function series_issue_manager_unpublish( $series_ID, &$published, &$unpublished ) {
  // take the series out of the published list
  $key = array_search( $series_ID, $published );
  if ( FALSE !== $key ) {
    array_splice( $published, $key, 1 );
    update_option( 'im_published_series', $published );
  }
  //if ( !in_array( $series_ID, $unpublished ) ) 
  {
    // add to the unpublished list
    $unpublished[] = $series_ID;
    sort( $unpublished );
    update_option( 'im_unpublished_series', $unpublished );

    // change all published posts in the series to pending
	$posts = get_objects_in_term($series_ID, 'series');
    foreach ( $posts as $post ) {
      wp_update_post( array(
        'ID' => $post,
        'post_status' => 'pending'
      ) );
	  publisher_wp_set_post_series($post, true, $post, $series_ID, true);
    }
  }
}
}


if (!function_exists('publisher_wp_set_post_series')) {
    function publisher_wp_set_post_series($post, $update, $post_ID = 0, $series_id = array(), $remove_part = false, $part = false)
    {
        $post_ID = (int) $post_ID;
        $old_series = wp_get_post_series($post_ID);
        $old_series = is_array($old_series) ? $old_series : array();
        $post_series = is_array($series_id) ? $series_id : array($series_id);
        $post_series = os_strarr_to_intarr($post_series);

        if ( empty($post_series) || (count($post_series) >= count($old_series)) ) {
            $match = false;
        } else {
            $match = array_diff($old_series, $post_series);
        }

	    if (empty($post_series) || ( count($post_series) == 1 && $post_series[0] == 0 ) ) 
        {
            $post_series = array();
        }

		$p_ser_edit = $post_series;

        if ( empty($post_series) ) {
            foreach ( $old_series as $o_ser ) {
                $part_key = apply_filters('orgseries_part_key', SERIES_PART_KEY, $o_ser);
                delete_post_meta( $post_ID, $part_key);
            }
        }

        foreach ( $old_series as $os_id ) {
            if ( !in_array($os_id, $post_series) ) {
                wp_delete_post_series_relationship($post_ID, $os_id);
                }
        }

        if ( !empty($match) && $match ) {
            foreach ($match as $part_reset_id) {
                wp_reset_series_order_meta_cache($post_ID, $part_reset_id);
            }
        }

        $success = wp_set_object_terms($post_ID, $post_series, ppseries_get_series_slug());

        if (empty($p_ser_edit)) {
            return; //let's get out we've done everything we need to do.
        }

        if ( $success ) {
            foreach ( $p_ser_edit as $ser_id ) {
                if ($remove_part) {
                    $s_pt = '';
                }else{
                    $s_pt = wp_series_part($post_ID, $ser_id);
                }
                
                if($part){

                    $series_part_key = apply_filters('orgseries_part_key', SERIES_PART_KEY, $ser_id);
                    $s_pt = $part;
                    delete_post_meta($post_ID, $series_part_key);
                    add_post_meta($post_ID, $series_part_key, $s_pt);

                }else{
                    if ($remove_part) 
                    {
                        $series_part_key = apply_filters('orgseries_part_key', SERIES_PART_KEY, $ser_id);
                        delete_post_meta($post_ID, $series_part_key);
                    }else {
                        set_series_order($ser_id, $post_ID, $s_pt, true);
                    }   
                }
            }
    
            return;
        } else {
            return FALSE;
        }

    }
}

if (!function_exists('series_issue_manager_publish_intercept')) {
function series_issue_manager_publish_intercept( $post_ID, $post ) {
    /*
  $unpublished = get_option( 'im_unpublished_series' );
  $publishable = TRUE;
  // check if post is in an unpublished series

  foreach ( get_the_series($post_ID) as $series ) {
	 if ( in_array( $series->term_id, $unpublished ) ) {
      $publishable = FALSE;
      break;
    }
  }
  // if post is in an unpublished series, change its status to 'pending' instead of 'publish'
  if ( !$publishable ) {
    if ($post->post_status != 'publish') return;

	wp_update_post( array(
      'ID' => $post_ID,
      'post_status' => 'pending'
    ) );
  }
  return;*/
}
}


if (!function_exists('series_issue_manager_scripts')) {
function series_issue_manager_scripts(  ) {
    
  wp_enqueue_script( "series_im_sort_articles", plugin_dir_url(__FILE__)."/js/series_im_sort_articles.js", array( 'jquery-ui-sortable' ), ORG_SERIES_VERSION, TRUE );
  wp_enqueue_style( 'series_im_sort_articles',  plugin_dir_url(__FILE__) . '/css/series_im_sort_articles.css', array(), ORG_SERIES_VERSION, 'all' );
}
}

if (!function_exists('series_issue_manager_add_series_form')) {
function series_issue_manager_add_series_form() {
	$published = get_option( 'im_published_series' );
	$unpublished = get_option( 'im_unpublished_series' );
	?>
        <div class="form-field">
			<label for="series_publish">
			<p><?php _e('Create as unpublished:', 'organize-series') ?>
				<input style="float:left; width: 20px;" name="series_publish" id="series_publish" type="checkbox" value="unpublish" />
			</p>
				<p><?php _e('When checked, all posts you assign to this series will remain unpublished until you publish the entire series.', 'organize-series'); ?></p>
			</label>
        </div>
   <?php
}
}


if (!function_exists('series_issue_set_publish_status')) {
function series_issue_set_publish_status($series_id, $taxonomy_id) {
	global $_POST;
	extract($_POST, EXTR_SKIP);
	//If "Unpublish" is selected, put series Id into Unpublished array so that new posts in this
	 //Series are not accidentally published
	if ( !isset($series_publish) ) $series_publish = null;
	if ($series_publish == 'unpublish') {
		$unpublished = get_option( 'im_unpublished_series' );

		  if ( !in_array( $series_id, $unpublished ) ) {
			// add to the unpublished list
			$unpublished[] = $series_id;
			sort( $unpublished );
			update_option( 'im_unpublished_series', $unpublished );
		}
	}
}
}



/**
 * Secondary admin notices function for use with admin_notices hook.
 *
 * Constructs admin notice HTML.
 *
 * @param string $message Message to use in admin notice. Optional. Default empty string.
 * @param bool $success Whether or not a success. Optional. Default true.
 * @return mixed
 */
function pps_publisher_admin_notices_helper($message = '', $success = true)
{

    $class   = [];
    $class[] = $success ? 'updated' : 'error';
    $class[] = 'notice is-dismissible';

    $messagewrapstart = '<div id="message" class="' . esc_attr(implode(' ', $class)) . '"><p>';

    $messagewrapend = '</p></div>';

    $action = '';

    /**
     * Filters the custom admin notice for pps_publisher.
     *
     *
     * @param string $value Complete HTML output for notice.
     * @param string $action Action whose message is being generated.
     * @param string $message The message to be displayed.
     * @param string $messagewrapstart Beginning wrap HTML.
     * @param string $messagewrapend Ending wrap HTML.
     */
    return apply_filters('pps_publisher_admin_notice', $messagewrapstart . $message . $messagewrapend, $action, $message,
        $messagewrapstart, $messagewrapend);
}


function pps_publisher_unpublished_success_message_admin_notice()
{
    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    echo pps_publisher_admin_notices_helper(
        esc_html__('The posts in your series were successfully unpublished.', 'organize-series')
    );
}


function pps_publisher_order_success_message_admin_notice()
{
    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    echo pps_publisher_admin_notices_helper(
        esc_html__('Congratulations. Your series order was updated successfully.', 'organize-series')
    );
}


function pps_publisher_published_success_message_admin_notice()
{

    $pub_time['mm'] = isset($_GET['mm'])?sanitize_text_field($_GET['mm']):null;
    $pub_time['jj'] = isset($_GET['jj'])?sanitize_text_field($_GET['jj']):null;
    $pub_time['aa'] = isset($_GET['aa'])?sanitize_text_field($_GET['aa']):null;
    $pub_time['hh'] = isset($_GET['hh'])?sanitize_text_field($_GET['hh']):null;
    $pub_time['mn'] = isset($_GET['mn'])?sanitize_text_field($_GET['mn']):null;
    
    // see if we have a valid publication date/time
    $publish_at = strtotime( $pub_time['aa'].'-'.$pub_time['mm'].'-'.$pub_time['jj'].' '.$pub_time['hh'].':'.$pub_time['mn'] );

    if ( !$publish_at ) {
      $publish_at = strtotime(current_time('mysql'));
    }

    if($publish_at > strtotime(current_time('mysql'))) {
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo pps_publisher_admin_notices_helper(
            esc_html__('Congratulations. Your series was scheduled successfully.', 'organize-series')
        );
    }else{
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo pps_publisher_admin_notices_helper(
            esc_html__('Congratulations. Your series was published successfully.', 'organize-series')
        );
    }
}

function pps_publisher_filter_removable_query_args_unpublish(array $args)
{
    return array_merge($args, [
        'action',
        'series_ID',
    ]);
}

function pps_publisher_filter_removable_query_args_publish(array $args)
{
    return array_merge($args, [
        'action',
        'series_ID',
        'posts',
        'mm',
        'jj',
        'aa',
        'hh',
        'mn',
        'publish'
    ]);
}

function ppseries_publisher_admin_init(){
    if (isset($_GET['page']) && $_GET['page'] === 'manage-issues' && isset($_GET['action']) && $_GET['action'] === 'unpublish') {
      add_action('admin_notices', "pps_publisher_unpublished_success_message_admin_notice");
      add_filter('removable_query_args', 'pps_publisher_filter_removable_query_args_unpublish');
    }elseif (isset($_GET['page']) && $_GET['page'] === 'manage-issues' && isset($_GET['action']) && $_GET['action'] === 'publish') {
      add_action('admin_notices', "pps_publisher_published_success_message_admin_notice");
      add_filter('removable_query_args', 'pps_publisher_filter_removable_query_args_publish');
    }elseif (isset($_GET['page']) && $_GET['page'] === 'manage-issues' && isset($_GET['action']) && $_GET['action'] === 'order') {
      add_action('admin_notices', "pps_publisher_order_success_message_admin_notice");
      add_filter('removable_query_args', 'pps_publisher_filter_removable_query_args_publish');
    }
}

add_action(''.ppseries_get_series_slug().'_add_form_fields', 'series_issue_manager_add_series_form');
add_action('admin_menu', 'series_issue_manager_manage_page');
//add_filter('save_post', 'series_issue_manager_publish_intercept',3,2);
add_action('created_'.ppseries_get_series_slug().'','series_issue_set_publish_status',2,2);
add_action('admin_init', 'ppseries_publisher_admin_init');