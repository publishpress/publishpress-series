<?php
global $org_domain;
if ( ! empty($series_ID) ) {
	$addcheck = false;
	$heading = __('Edit Series', $org_domain);
	$submit_text = __('Edit Series &raquo;', $org_domain);
	$form = '<form enctype="multipart/form-data" id="editseries" name="editseries" method="post" action="' . SERIES_LOC . 'orgSeries-manage.php">'; 
	$action = 'editedseries';
	$nonce_action = 'update-series_' . $series_ID;
	do_action('edit_series_form_pre', $series); 
} else {
	$addcheck = TRUE;
	$heading = __('Add Series', $org_domain);
	$submit_text = __('Add Series &raquo;', $org_domain);
	$form = '<form id="addseries" name="addseries" method="post" action="' . SERIES_LOC . 'orgSeries-manage.php">'; 
	$action = 'addseries';
	$nonce_action = 'series-add';
	$series = '';
	do_action('add_series_form_pre', $series); 
}

?>
<div id="col-left">
<h2><?php echo $heading ?></h2>
<div id="ajax-response"></div>
<?php echo $form ?>
<input type="hidden" name="action" value="<?php echo $action ?>" />
<input type="hidden" name="series_ID" value="<?php echo $series->term_id; ?>" />
<input type="hidden" id="series_icon_get" value="<?php echo $series_icon_loc ?>" />
<?php wp_nonce_field($nonce_action); ?>
	<table class="editform" width="100%" cellspacing="2" cellpadding="5">
		<tr>
			<th width="33%" scope="row" valign="top"><label for="series_name"><?php _e('Series name:', $org_domain) ?></label></th>
			<td width="67%"><input name="series_name" id="series_name" type="text" value="<?php echo attribute_escape($series->name); ?>" size="40" /></td>
		</tr>
		<tr>
			<th scope="row" valign="top"><label for="series_nicename"><?php _e('Series slug:', $org_domain) ?></label></th>
			<td><input name="series_nicename" id="series_nicename" type="text" value="<?php echo attribute_escape($series->slug); ?>" size="40" /></td>
		</tr>
		<tr>
			<th scope="row" valign="top"><label for="series_description"><?php _e('Description: (optional)', $org_domain) ?></label></th>
			<td><textarea name="series_description" id="series_description" rows="5" cols="50" style="width: 97%;"><?php echo wp_specialchars($series->description); ?></textarea></td>
		</tr>
	</table>
		
	<table class="form-table">	
		<tr valign="top">
		
		<?php if ($series_icon != '') { ?>
		<th scope="column"><?php _e('Current series icon:', $org_domain); ?></th><?php } ?>
		<td>
		<?php if ($series_icon != '') echo $series_icon; ?>
		<div style="float:right;" id="selected-icon"></div>
		</td>
		</tr>
		<?php if (!$addcheck && $series_icon != '') { ?>
		<tr style="width:100%">
			<th><input name="delete_image" id="delete_image" type="checkbox" value="true" /></th>
			<td style="width: 100%">
			<?php _e('Delete image? (note: there will not be an image associated with this series if you select this)', $org_domain); ?>
			</td>
		</tr>
		<?php } ?>
		<tr valign="top">
			<th scope="row"><label for="series_icon"><?php _e('Series Icon:', $org_domain) ?></label></th>
			<td><div name="series_image_url_display" id="series_image_url_display" style="width:350px; background-color:#FFFFFF; padding:3px; border:#c6d9e9 1px solid; font-family:Verdana, Arial, Helvetica, sans-serif; font-size:13px"><?php if ($series_icon_loc != '') echo $series_icon_loc; else _e('No Series Icon selected', $org_domain); ?></div>
			<img src='images/media-button-image.gif' alt='Add photos from your media' /> <a href="media-upload.php?TB_iframe=true&amp;type=image&amp;tab=library&amp;height=500&amp;width=640" class="thickbox" title='Add an Image'><strong><?php _e('Click here to add/change your series icon', $org_domain); ?></strong></a><br />
			<small><?php _e('Note: To choose image click the "insert into post" button in the media uploader', $org_domain);?></small>
			</td>
		</tr>
	<input type="hidden" name="series_icon_loc" id="series_icon_loc" value="" />	
	<?php do_action('edit_series_form', $series);  ?>
	</table>
	
	<input type="submit" class="button-button-primary" value="<?php echo $submit_text ?>" onclick="jQuery('#series_icon_loc').val(image_url_collection); return true;" />
</form>

<textarea class='' rows='0' cols='0' name='content' tabindex='2' id='content' onfocus="image_url_add()" style="width:1px; height:1px; padding:0px; border:none"></textarea>
    <script type="text/javascript">edCanvas = document.getElementById('content');</script>
	
</div>
<?php
?>