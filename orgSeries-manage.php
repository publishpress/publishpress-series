<?php
//This file contains all the code related to managing the series the user has created (similar to the category management interface).  A lot of this code has been mirrored from the core categories.php file in WordPress.

if ( file_exists('orgSeries_includes.php') )
	require_once('orgSeries_includes.php');

wp_reset_vars(array('action','series_ID'));

switch($action) {
	
case 'addseries':
	
	check_admin_referer('series-add');
	
	if ( !current_user_can('manage_series') ) 
		wp_die(__('Cheatin&#8217; uh?'));
	
	if( wp_insert_series($_POST) ) { 
		wp_redirect('../../../wp-admin/edit.php?page=' . SERIES_DIR . '/orgSeries-manage.php&message=1#addseries'); 
	} else {
		wp_redirect('../../../wp-admin/edit.php?page=' . SERIES_DIR . '/orgSeries-manage.php&message=4#addseries');
	}
	exit;
break;

case 'delete':
	$series_ID = (int) $_GET['series_ID'];
	check_admin_referer('delete-series_' . $series_ID); 
	
	if ( !current_user_can('manage_series') )
		wp_die(__('Cheatin&#8217; uh?'));
		
	wp_delete_series($series_ID);
	
	wp_redirect(get_option( 'siteurl' ) . '/wp-admin/edit.php?page=' . SERIES_DIR . '/orgSeries-manage.php&message=2'); 
	exit;
break;

case 'edit':
	
	$series_ID = (int) $_GET['series_ID'];
	$series = get_series_to_edit($series_ID);
	$series_icon = get_series_icon('fit_width=100&fit_height=100&link=0&expand=true&display=0&series='.$series_ID);
	$series_icon_loc = seriesicons_url() . series_get_icons($series_ID);
	include( '../wp-content/plugins/' . SERIES_DIR .'/edit-series-form.php'); 
	
break;

case 'editedseries':
	
	$series_ID = (int) $_POST['series_ID'];
	check_admin_referer('update-series_' . $series_ID);
	
	if ( !current_user_can('manage_series') )
		wp_die(__('Cheatin&#8217; huh?'));
	
	if ( wp_update_series($_POST) ) 
			wp_redirect(get_option('siteurl') . '/wp-admin/edit.php?page=' . SERIES_DIR . '/orgSeries-manage.php&message=3');
	else
		wp_redirect(get_option('siteurl') . '/wp-admin/edit.php?page=' . SERIES_DIR . '/orgSeries-manage.php&message=5');
	
	exit;
break;

default:

$messages[1] = __('Series added.');
$messages[2] = __('Series deleted.');
$messages[3] = __('Series updated.');
$messages[4] = __('Series not added.');
$messages[5] = __('Series not updated.');
?>

<?php if (isset($_GET['message'])) : ?>
<div id="message" class="updated"><p><?php echo $messages[$_GET['message']]; ?></p></div>
<?php $_SERVER['REQUEST_URI'] = remove_query_arg(array('message'), $_SERVER['REQUEST_URI']); ?>
<?php endif; ?>

<div class="wrap">

<?php if ( current_user_can('manage_series') ) : ?>
	<h2><?php printf(__('Manage Series (<a href="%s">add new</a>)'), '#addseries') ?></h2>
<?php else : ?>
	<h2><?php _e('Series') ?></h2>
<?php endif; ?>
<div id="col-container">
<div id="col-right">
<table class="widefat">
	<thead>
	<tr>
		<th scope="col" style="text-align: center"><?php _e('ID') ?></th>
		<th scope="col"><?php _e('Name') ?></th>
		<th scope="col"><?php _e('Description') ?></th>
		<th scope="col" width="90" style="text-align: center"><?php _e('Posts') ?></th>
		<th scope="col" width="50" style="text-align: center"><?php _e('Icon') ?></th>
		<th colspan="2" style="text-align: center"><?php _e('Action') ?></th>
	</tr>
	</thead>
	<tbody id="the-list">
<?php
	series_rows(); 
?>
	</tbody>
</table>
</div>

<?php if ( current_user_can('manage_series') ) : ?>
<div class="wrap">
<p><?php printf(__('<strong>Note:</strong><br />Deleting a series will also disassociate all posts that were a part of that series.<br /><strong>Also: </strong><br />You add series icons to a series by clicking the edit link.')) ?></p>
</div>

<?php include('../wp-content/plugins/' . SERIES_DIR .'/edit-series-form.php'); ?>
<?php endif; ?>

<?php
break;
}

?>