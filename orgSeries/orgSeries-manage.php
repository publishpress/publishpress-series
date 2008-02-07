<?php
//This file contains all the code related to managing the series the user has created (similar to the category management interface).  A lot of this code has been mirrored from the core categories.php file in WordPress.

$title = __('Series');
$parent_file = 'edit.php';

wp_reset_vars(array('action', 'series'));

switch($action) {
	
case 'addseries':
	
	check_admin_referer('addseries');
	
	if ( !current_user_can('manage_series') ) 
		wp_die(__('Cheatin&#8217; uh?'));
		
	if( wp_insert_series($_POST) ) { //TODO - create (or see if I've already written it and compare) the wp_insert_series method (look up wp_insert_category)
		wp_redirect('orgSeries-manage.php?message=1#addseries'); //TODO - don't think this will work...see post-teaser and header( ) for alternate option
	} else {
		wp_redirect('orgSeries-manage.php?message=4#addseries');
	}
	exit;
break;

case 'delete':
	$series_ID = (int) $_GET['series_ID'];
	check_admin_referer('delete-series_' . $series_ID); //TODO - same as Line 15
	
	if ( !current_user_can('manage_series') )
		wp_die(__('Cheatin&#8217; uh?'));
		
	$series_name = get_series_name($series_ID);
	
	wp_delete_series($series_ID); // TODO - create the wp_delete_series method (or see if I've already written it and compare) (look up wp_delete_category)  ALSO - will have to automatically delete all the post associations from the series and the relevant post-order metas with each post.
	
	wp_redirect('orgSeries-manage.php?message=2'); //TODO - see LINE 19
	exit;
break;

case 'edit':
	
	//require_once('admin-header.php');  TODO - DON'T THINK I NEED THIS.
	$series_ID = (int) $_GET['series_ID'];
	$series = get_series_to_edit($series_ID);
	include(get_settings('siteurl') . '/wp-content/plugins/orgSeries/edit-series-form.php'); //TODO write the edit-series-form.php file (see edit-category-form.php) -- DON'T forget to add to this form the series icon information.
	
break;

case 'editedseries':
	$series_ID = (int) $_POST['series_ID'];
	check_admin_referer('update-series_' . $series_ID);
	
	if ( !current_user_can('manage_series') )
		wp_die(__('Cheatin&#8217; uh?'));
	
	if ( wp_update_series($_POST) ) // TODO check to see that wp_update_series I've already written matches the wp_update_category code and that it does all the necessary updates.
		wp_redirect('orgSeries-manage.php?message=3');
	else
		wp_redirect('orgSeries-manage.php?message=5');
	
	exit;
break;

default:

wp_enqueue_script( 'admin-series' );  //I think I would have to use the add_action('wp_ajax_... action instead or the add_action('admin_header... //see the code for adding javascript to the post-edit screen adding series stuff.  NOW...also notice I need to write a series.js script different from the existing one I have to match the categories.js script except applied to series of course.
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
	series-rows(); //TODO is this function written?  if not see cat_rows() and do similar -- DON'T forget to add a column for the series icon.
?>
	</tbody>
</table>

</div>

<?php if ( current_user_can('manage_series') ) : ?>
<div class="wrap">
<p><?php printf(__('<strong>Note:</strong><br />Deleting a series will also disassociate all posts that were a part of that series.')) ?></p>
</div>

<?php include('edit-series-form.php'); ?>
<?php endif; ?>

<?php
break;
}

//include('admin-footer.php');  TODO - don't think this will be necessary.  BUT will have to add orgSeries-manage.php to an add-action() related to the manage section of the admin site?
?>	