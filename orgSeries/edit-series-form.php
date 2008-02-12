<?php
if ( ! empty($series_ID) ) {
	$heading = __('Edit Series');
	$submit_text = __('Edit Series &raquo;');
	$form = '<form enctype="multipart/form-data" id="editseries" name="editseries" method="post" action="' . get_settings('siteurl') . '/wp-content/plugins/orgSeries/orgSeries-manage.php">'; 
	$action = 'editedseries';
	$nonce_action = 'update-series_' . $series_ID;
	do_action('edit_series_form_pre', $series); 
} else {
	$addcheck = TRUE;
	$heading = __('Add Series');
	$submit_text = __('Add Series &raquo;');
	$form = '<form id="addseries" name="addseries" method="post" action="' . get_settings('siteurl') . '/wp-content/plugins/orgSeries/orgSeries-manage.php">'; 
	$action = 'addseries';
	$nonce_action = 'series-add';
	do_action('add_series_form_pre', $series); 
}
?>

<div class="wrap">
<h2><?php echo $heading ?></h2>
<div id="ajax-response"></div>
<?php echo $form ?>
<input type="hidden" name="action" value="<?php echo $action ?>" />
<input type="hidden" name="series_ID" value="<?php echo $series->term_id; ?>" />
<?php wp_nonce_field($nonce_action); ?>
	<table class="editform" width="100%" cellspacing="2" cellpadding="5">
		<tr>
			<th width="33%" scope="row" valign="top"><label for="series_name"><?php _e('Series name:') ?></label></th>
			<td width="67%"><input name="series_name" id="series_name" type="text" value="<?php echo attribute_escape($series->name); ?>" size="40" /></td>
		</tr>
		<tr>
			<th scope="row" valign="top"><label for="series_nicename"><?php _e('Series slug:') ?></label></th>
			<td><input name="series_nicename" id="series_nicename" type="text" value="<?php echo attribute_escape($series->slug); ?>" size="40" /></td>
		</tr>
		<tr>
			<th scope="row" valign="top"><label for="series_description"><?php _e('Description: (optional)') ?></label></th>
			<td><textarea name="series_description" id="series_description" rows="5" cols="50" style="width: 97%;"><?php echo wp_specialchars($series->description); ?></textarea></td>
		</tr>
		<?php if (!$addcheck) { ?>
		<tr>
			<th scope="row" valign="top"><label for="series_icon"><?php _e('Series Icon:') ?></label></th>
			<td><input name="series_icon" id="series_icon" type="file" /></td>
		</tr>
		<?php } ?>
	</table>
<p class="submit"><input type="submit" name="submit" value="<?php echo $submit_text ?>" /></p>
<?php do_action('edit_series_form', $series);  ?>
</form>
</div>