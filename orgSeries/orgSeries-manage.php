<?php
//This file contains all the code related to managing the series the user has created (similar to the category management interface).  A lot of this code has been mirrored from the core categories.php file in WordPress.
//TODO - the addseries form on the manage->series page isn't working as expected.  Need to troubleshoot.  Suspect there is something up with the ajax.
require_once('orgSeries_includes.php');

wp_reset_vars(array('action','series'));
//require_once('../admin.php');

switch($action) {
	
case 'addseries':
	
	check_admin_referer('series-add');
	
	if ( !current_user_can('manage_series') ) 
		wp_die(__('Cheatin&#8217; uh?'));
		
	if( wp_insert_series($_POST, $_FILES['series_icon']) ) { 
		wp_redirect('edit.php?page=orgSeries/orgSeries-manage.php&amp;message=1#addseries'); //TODO - don't think this will work...see post-teaser and header( ) for alternate option IF it indeed doesn't work.
	} else {
		wp_redirect('edit.php?page=orgSeries/orgSeries-manage.php&amp;message=4#addseries');
	}
	exit;
break;

case 'delete':
	$series_ID = (int) $_GET['series_ID'];
	check_admin_referer('delete-series_' . $series_ID); //TODO - same as Line 15
	
	if ( !current_user_can('manage_series') )
		wp_die(__('Cheatin&#8217; uh?'));
		
	wp_delete_series($series_ID); 
	wp_redirect('edit.php?page=orgSeries/orgSeries-manage.php&amp;message=2'); //TODO - see LINE 19
	exit;
break;

case 'edit':
	
	$series_ID = (int) $_GET['series_ID'];
	$series = get_series_to_edit($series_ID);
	include( '../wp-content/plugins/orgSeries/edit-series-form.php'); 
	
break;

case 'editedseries':
	
	$series_ID = (int) $_POST['series_ID'];
	check_admin_referer('update-series_' . $series_ID);
	
	if ( !current_user_can('manage_series') )
		wp_die(__('Cheatin&#8217; uh?'));
		
	if ( wp_update_series($_POST, $_FILES['series_icon']) ) 
			wp_redirect(get_settings('siteurl') . '/wp-admin/edit.php?page=orgSeries/orgSeries-manage.php&amp;message=3');
	else
		wp_redirect(get_settings('siteurl') . '/wp-admin/edit.php?page=orgSeries/orgSeries-manage.php&amp;message=5');
	
	exit;
break;

default:

wp_enqueue_script( 'admin-series' );  
//require_once ('admin-header.php'); //TODO: I don't think I need to use this.

$messages[1] = __('Series added.');
$messages[2] = __('Series deleted.');
$messages[3] = __('Series updated.');
$messages[4] = __('Series not added.');
$messages[5] = __('Series not updated.');
?>

<?php if (isset($_GET['message'])) : ?>
<div id="message" class="updated fade"><p><?php echo $messages[$_GET['message']]; ?></p></div>
<?php endif; ?>

<div class="wrap">
<?php if ( current_user_can('manage_series') ) : ?>
	<h2><?php printf(__('Series (<a href="%s">add new</a>)'), '#addseries') ?></h2>
<?php else : ?>
	<h2><?php _e('Series') ?></h2>
<?php endif; ?>
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
	series_rows(); //TODO is this function written?  if not see cat_rows() and do similar -- DON'T forget to add a column for the series icon.
?>
	</tbody>
</table>

</div>

<?php if ( current_user_can('manage_series') ) : ?>
<div class="wrap">
<p><?php printf(__('<strong>Note:</strong><br />Deleting a series will also disassociate all posts that were a part of that series.')) ?></p>
</div>

<?php include('../wp-content/plugins/orgSeries/edit-series-form.php'); ?>
<?php endif; ?>

<?php
break;
}

//include('admin-footer.php');  TODO - don't think this will be necessary.  BUT will have to add orgSeries-manage.php to an add-action() related to the manage section of the admin site?
?>	