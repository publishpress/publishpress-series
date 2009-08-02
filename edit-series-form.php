<?php
if ( ! empty($series_ID) ) {
	$addcheck = false;
	$heading = __('Edit Series');
	$submit_text = __('Edit Series &raquo;');
	$form = '<form enctype="multipart/form-data" id="editseries" name="editseries" method="post" action="' . SERIES_LOC . 'orgSeries-manage.php">'; 
	$action = 'editedseries';
	$nonce_action = 'update-series_' . $series_ID;
	do_action('edit_series_form_pre', $series); 
} else {
	$addcheck = TRUE;
	$heading = __('Add Series');
	$submit_text = __('Add Series &raquo;');
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
	</table>
	<?php if (!$addcheck) { ?>	
	<table class="form-table">	
		<tr>
		<th scope="row">
			<td><?php if ($series_icon != '') echo 'Current series icon: ' . $series_icon; ?></td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="series_icon"><?php _e('Series Icon:') ?></label></th>
			<td><div name="series_image_url_display" id="series_image_url_display" style="width:500px; background-color:#FFFFFF; padding:3px; border:#c6d9e9 1px solid; font-family:Verdana, Arial, Helvetica, sans-serif; font-size:13px"><?php if ($series_icon_loc != '') echo $series_icon_loc; ?></div>
			<img src='images/media-button-image.gif' alt='Add photos from your media' /> <a href="media-upload.php?TB_iframe=true&amp;type=image&amp;tab=library&amp;height=500&amp;width=640" class="thickbox" title='Add an Image'><strong>Click here to add photos from your media</strong></a>
			</td>
		</tr>
	<input type="hidden" name="series_icon_loc" id="series_icon_loc" value="" />	
	<?php do_action('edit_series_form', $series);  ?>
	</table>
	<?php } ?>
	<input type="submit" class="button-button-primary" value="<?php echo $submit_text ?>" onclick="jQuery('#series_icon_loc').val(image_url_collection); return true;" />
</form>

<textarea class='' rows='0' cols='0' name='content' tabindex='2' id='content' onfocus="image_url_add()" style="width:1px; height:1px; padding:0px; border:none"></textarea>
    <script type="text/javascript">edCanvas = document.getElementById('content');</script>
	
</div>
<?php
?>