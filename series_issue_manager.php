<?php
/*
Plugin Name: Series Issue Manager 
Plugin URI: http://unfoldingneurons.com/neurotic-plugins/organize-series-wordpress-plugin/
Description: Allows an editor to publish an "issue", which is to say, all pending posts with a given series. Until a series is published, all posts with that series will remain in the pending state.  Credit really needs to go to  <a href="http://xplus3.net">Jonathan Brinley</a> for his original Issue Manage plugin because all I did was modify it for use with series rather than categories.  Also NOTE that this REQUIRES Organize Series to be installed or a lot of things could go wrong...
Version: 2.1.3
Author: Darren Ethier 
Author URI: http://unfoldingneurons.com
*/
  
function series_issue_manager_manage_page(  ) {
  global $org_domain;
  if ( function_exists('add_submenu_page') ) {
    $page = add_submenu_page( 'edit.php', __('Manage Series Issues',$org_domain), __('Series Issues',$org_domain), 'publish_posts', 'manage-issues', 'series_issue_manager_admin' );
    add_action("admin_print_scripts-$page", 'series_issue_manager_scripts');
  }
}
function series_issue_manager_admin(  ) {
  $published = get_option( 'im_published_series' );
  $unpublished = get_option( 'im_unpublished_series' );
  $series = get_series( 'orderby=name&hide_empty=0' );
  
  // Make sure the options exist
  if ( $published === FALSE ) { $published = array(); update_option( 'im_published_series', $published ); }
  if ( $unpublished === FALSE ) { $unpublished = array(); update_option( 'im_unpublished_series', $unpublished ); }
  
  // See if we have GET parameters
  $series_ID = isset($_GET['series_ID'])?$_GET['series_ID']:null;
  $action = isset($_GET['action'])?$_GET['action']:null;
    
  if ( $series_ID ) {
    $series_ID = (int)$series_ID;
    switch($action) {
      case "list":
        include_once('series_im_article_list.php');
        break;
      case "publish":
        $post_IDs = isset($_GET['posts'])?$_GET['posts']:null;
        $pub_time['mm'] = isset($_GET['mm'])?$_GET['mm']:null;
        $pub_time['jj'] = isset($_GET['jj'])?$_GET['jj']:null;
        $pub_time['aa'] = isset($_GET['aa'])?$_GET['aa']:null;
        $pub_time['hh'] = isset($_GET['hh'])?$_GET['hh']:null;
        $pub_time['mn'] = isset($_GET['mn'])?$_GET['mn']:null;
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

function series_issue_manager_publish( $series_ID, $post_IDs, $pub_time, &$published, &$unpublished ) {
  // take the series out of the unpublished list
  $key = array_search( $series_ID, $unpublished );
  if ( FALSE !== $key ) {
    array_splice( $unpublished, $key, 1 );
    update_option( 'im_unpublished_series', $unpublished );
  }
  if ( !in_array( $series_ID, $published ) ) {
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
    foreach ( explode(',',$post_IDs) as $post_ID ) {
      $post_ID = (int)$post_ID;
      $post = get_post( $post_ID );
      // set the date to about the appropriate time, keeping a small gap so posts stay in order
      wp_update_post( array(
        'ID' => $post->ID,
        'post_date' => date( 'Y-m-d H:i:s', $publish_at-($counter+1) ),
        'post_date_gmt' => '',
        'post_status' => 'publish'
      ) );
	  wp_set_post_series( $post_ID,'',$series_ID );
      $counter++;
    }
  }
}

function series_issue_manager_unpublish( $series_ID, &$published, &$unpublished ) {
  // take the series out of the published list
  $key = array_search( $series_ID, $published );
  if ( FALSE !== $key ) {
    array_splice( $published, $key, 1 );
    update_option( 'im_published_series', $published );
  }
  if ( !in_array( $series_ID, $unpublished ) ) {
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
	  wp_set_post_series( $post, '', $series_ID);
    }
  }
}

function series_issue_manager_publish_intercept( $post_ID ) {
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
    wp_update_post( array(
      'ID' => $post_ID,
      'post_status' => 'pending'
    ) );
  }
}

function series_issue_manager_activation(  ) {
  // if option records don't already exist, create them
  if ( !get_option( 'im_published_series' ) ) {
    add_option( 'im_published_series', array() );
  }
  if ( !get_option( 'im_unpublished_series' ) ) {
    add_option( 'im_unpublished_series', array() );
  }
}
function series_issue_manager_deactivation(  ) {
  // they don't have to exist to be deleted
  delete_option( 'im_published_series' );
  delete_option( 'im_unpublished_series' );
}
function series_issue_manager_scripts(  ) {
  wp_enqueue_script( "series_jquery-ui-sortable", path_join(WP_PLUGIN_URL, basename( dirname( __FILE__ ) )."/js/series_jquery-ui-sortable-1.5.2.js"), array( 'jquery' ), '1.5.2' );
  wp_enqueue_script( "series_im_sort_articles", path_join(WP_PLUGIN_URL, basename( dirname( __FILE__ ) )."/js/series_im_sort_articles.js"), array( 'jquery' ) );
}

add_action('admin_menu', 'series_issue_manager_manage_page');
add_action('publish_post', 'series_issue_manager_publish_intercept');


// Register hooks for activation/deactivation.
register_activation_hook( __FILE__, 'series_issue_manager_activation' );
register_deactivation_hook( __FILE__, 'series_issue_manager_deactivation' );
//TODO - need to figure out why post status isn't being changed to published (I think it has something to do with not finding the series_ID in the logical checks.  Also will require all the update series code to be added in with the publish stuff because it isn't calling the publish hook that orgseries is hooked into.