<?php

function org_series_init($reset = false) {
	if (!$reset) $settings = get_option('org_series_options');
	
	if (!($is_initialized=get_option('org_series_is_initialized'))
		|| empty ($settings)
		|| $reset) {
		$init_settings = array(
	//these options are not optional and must be set for the plug-in to work as expected.
		'auto_tag_postlist_toggle' => 1, //sets the auto-tag insertions for the post-list box for posts that are part of series
		'auto_tag_seriesmeta_toggle' =>1, //sets the auto-tag insertions for the series-meta information in posts that are part of a series.
		'series_cats' => 1, //sets the main category that is used to contain all the series. 
		'custom_css' => 1, //toggles whether the custom .css file included with this plugin will be used or if a users own .css (added to the style.css in the theme directory) will be used.
	
	//The following options will affect the display of elements in series related pages. NOTE: "cat_icon... options will only apply when the category_icon plugin is enabled.
		//...cat_page = the display for the main series category page (that displays all the series that have been written). 
		//...series_page = the individual series display page (which lists all the posts in the selected series).
		//...post_page = the single() page for a post that is part of a series.
		'cat_icon_width_cat_page' => 200, 
		'text_chk_cat_page' => 1, //1=on, 0=off (title and description of series)
		'cat_title_chk_cat_page' => 0, //1=yes, 0=no
		'cat_icon_chk_cat_page' => 0, //1=yes, 0=no
		'cat_description_cat_page' => 0, //1=yes, 0=no
		'before_cat_icon_cat_page' => '<div class="imgset">',
		'after_cat_icon_cat_page' => '</div>',
		'before_catlist-content_cat_page' => '<div class="catlist-content">',
		'after_catlist-content_cat_page' => '</div>',
		'beforetitle_cat_page' => '<h2>',
		'aftertitle_cat_page' => '</h2>',
		'beforedescript_cat_page' => '<p>',
		'afterdescript_cat_page' => '</p>',
		'beforedisplay_cat_page' => '<div class="catlist-box">',
		'afterdisplay_cat_page' => '</div>',
		'paging_cat_page' => 1, //1=on, 0=off
		'perpage_cat_page' => 5,
		'before_series_title_post_page' => '<h2 class="series-title">',
		'after_series_title_post_page' => '</h2>',
		'before_post_title_list_post_page' => '<ul class="catlist-ul">',
		'after_post_title_list_post_page' => '</ul>',
		'before_title_post_page' => '<li class="catlist-li">',
		'after_title_post_page' => '</li>',
		'series_intro_text_post_page' => '<p>This is part of the series:</p>',
		'beforelistbox_post_page' => '<div class="seriesbox">',
		'afterlistbox_post_page' => '</div>',
		'cat_icon_width_post_page' => 100,
		'text_chk_post_page' => 1, //1=on, 0=off (title and description of series)
		'cat_icon_chk_post_page' => 1, //1=yes, 0=no
		'cat_title_chk_post_page' => 0, //1=yes, 0=no
		'cat_description_cat_post_page' => 0, //1=yes, 0=no
		'cat_before_title_post_page' => '<h3 class="cat-title-post-page">',
		'cat_after_title_post_page' => '</h3>',
		'cat_before_description_post_page' => '<p class="cat-description">',
		'cat_after_description_post_page' => '</p>',
		'before_series_meta' => '<div class="seriesmeta">',
		'after_series_meta' => '</div>',
		'series_meta_word' => 'entry');
		
		if (!empty($settings)) {
			$newSettings = array_merge($init_settings, $settings);
		} else {
			$newSettings = $init_settings;
		}
		
		$newSettings['last_modified'] = gmdate("D, d M Y H:i:s",time());
		//check_admin_referer('organize-series-reset-options');  NOT USING FOR NOW.
		update_option('org_series_is_initialized', 1, 'Organize Series Plugin has been initialized');
		update_option('org_series_options', $newSettings, 'Array of options for the Organize Series plugin');
		
		if ($is_initialized=get_option('org_series_is_initialized')) { ?>
			<div class="updated"><p><strong>The Organize Series plugin has been initialized. However for the plugin to function properly you must set the category that contains all your various series (which are subcategories of that main category)</strong></p></div>
		<?php }
		return;
	}
}

function org_series_option_update() {
	global $wpdb;
	$settings = get_option('org_series_options');
	$settings['last_modified'] = gmdate("D, d M Y H:i:s", time());
	$settings['auto_tag_postlist_toggle'] = isset($_POST['auto_tag_postlist_toggle']) ? 1 : 0;
	$settings['auto_tag_seriesmeta_toggle'] = isset($_POST['auto_tag_seriesmeta_toggle']) ? 1 : 0;
	$settings['custom_css'] = isset($_POST['custom_css']) ? 1 : 0;
	if( isset($_POST['series_cats']) ) $settings['series_cats'] = $_POST['series_cats'];
	if( isset($_POST['cat_icon_width_cat_page']) ) $settings['cat_icon_width_cat_page'] = $_POST['cat_icon_width_cat_page'];
	$settings['text_chk_cat_page'] = isset($_POST['text_chk_cat_page']) ? 1 : 0;
	$settings['cat_icon_chk_cat_page'] = isset($_POST['cat_icon_chk_cat_page']) ? 1 : 0;
	$settings['cat_title_chk_cat_page'] = isset($_POST['cat_title_chk_cat_page']) ? 1 : 0;
	$settings['cat_icon_chk_cat_page'] = isset($_POST['cat_icon_chk_cat_page']) ? 1 : 0;
	if( isset($_POST['before_catlist-content_cat_page']) )  $settings['before_catlist-content_cat_page'] = urldecode($_POST['before_catlist-content_cat_page']);
	if( isset($_POST['after_catlist-content_cat_page']) )  $settings['after_catlist-content_cat_page'] = urldecode($_POST['after_catlist-content_cat_page']);
	if( isset($_POST['after_cat_icon_cat_page']) )  $settings['after_cat_icon_cat_page'] = urldecode($_POST['after_cat_icon_cat_page']);
	if( isset($_POST['before_cat_icon_cat_page']) )  $settings['before_cat_icon_cat_page'] = urldecode($_POST['before_cat_icon_cat_page']);
	$settings['cat_description_cat_page'] = isset($_POST['cat_description_cat_page']) ? 1 : 0;
	if( isset($_POST['beforetitle_cat_page']) )  $settings['beforetitle_cat_page'] = urldecode($_POST['beforetitle_cat_page']);
	if( isset($_POST['aftertitle_cat_page']) )  $settings['aftertitle_cat_page'] = urldecode($_POST['aftertitle_cat_page']);
	if( isset($_POST['beforedescript_cat_page']) )  $settings['beforedescript_cat_page'] = urldecode($_POST['beforedescript_cat_page']);
	if( isset($_POST['afterdescript_cat_page']) )  $settings['afterdescript_cat_page'] = urldecode($_POST['afterdescript_cat_page']);
	if( isset($_POST['beforedisplay_cat_page']) )  $settings['beforedisplay_cat_page'] = urldecode($_POST['beforedisplay_cat_page']);
	if( isset($_POST['afterdisplay_cat_page']) )  $settings['afterdisplay_cat_page'] = urldecode($_POST['afterdisplay_cat_page']);
	$settings['paging_cat_page'] = isset($_POST['paging_cat_page']) ? 1 : 0;
	if( isset($_POST['perpage_cat_page']) )  $settings['perpage_cat_page'] = $_POST['perpage_cat_page'];
	if( isset($_POST['before_series_title_post_page']) )  $settings['before_series_title_post_page'] = urldecode($_POST['before_series_title_post_page']);
	if( isset($_POST['after_series_title_post_page']) )  $settings['after_series_title_post_page'] = urldecode($_POST['after_series_title_post_page']);
	if( isset($_POST['before_post_title_list_post_page']) )  $settings['before_post_title_list_post_page'] = urldecode($_POST['before_post_title_list_post_page']);
	if( isset($_POST['after_post_title_list_post_page']) )  $settings['after_post_title_list_post_page'] = urldecode($_POST['after_post_title_list_post_page']);
	if( isset($_POST['before_title_post_page']) )  $settings['before_title_post_page'] = urldecode($_POST['before_title_post_page']);
	if( isset($_POST['after_title_post_page']) )  $settings['after_title_post_page'] = urldecode($_POST['after_title_post_page']);
	if( isset($_POST['series_intro_text_post_page']) )  $settings['series_intro_text_post_page'] = urldecode($_POST['series_intro_text_post_page']);
	if( isset($_POST['beforelistbox_post_page']) )  $settings['beforelistbox_post_page'] = urldecode($_POST['beforelistbox_post_page']);
	if( isset($_POST['afterlistbox_post_page']) )  $settings['afterlistbox_post_page'] = urldecode($_POST['afterlistbox_post_page']);
	if( isset($_POST['cat_icon_width_post_page']) )  $settings['cat_icon_width_post_page'] = $_POST['cat_icon_width_post_page'];
	$settings['text_chk_post_page'] = isset($_POST['text_chk_post_page']) ? 1 : 0;
	$settings['cat_icon_chk_post_page'] = isset($_POST['cat_icon_chk_post_page']) ? 1 : 0;
	$settings['cat_title_chk_post_page'] = isset($_POST['cat_title_chk_post_page']) ? 1 : 0;
	$settings['cat_description_cat_post_page'] = isset($_POST['cat_description_cat_post_page']) ? 1 : 0;
	if( isset($_POST['cat_before_title_post_page']) )  $settings['cat_before_title_post_page'] = urldecode($_POST['cat_before_title_post_page']);
	if( isset($_POST['cat_after_title_post_page']) )  $settings['cat_after_title_post_page'] = urldecode($_POST['cat_after_title_post_page']);
	if( isset($_POST['cat_before_description_post_page']) )  $settings['cat_before_description_post_page'] = urldecode($_POST['cat_before_description_post_page']);
	if( isset($_POST['cat_after_description_post_page']) )  $settings['cat_after_description_post_page'] = urldecode($_POST['cat_after_description_post_page']);
	if( isset($_POST['before_series_meta']) )  $settings['before_series_meta'] = urldecode($_POST['before_series_meta']);
	if( isset($_POST['after_series_meta']) )  $settings['after_series_meta'] = urldecode($_POST['after_series_meta']);
	if( isset($_POST['series_meta_word']) )  $settings['series_meta_word'] = urldecode($_POST['series_meta_word']);
	$settings['last_modified'] = gmdate("D, d M Y H:i:s", time());
	//check_admin_referer('organize-series-update-options'); NOT USING FOR NOW
	update_option('org_series_options', $settings, 'Array of options for the Organize Series plugin',1);
	
}

function org_series_admin_page() {
	org_series_init();
	
	if (isset($_POST['submit_option'])) {
		if (isset($_POST['reset_option'])) {
			org_series_init(true);
		} else {
			org_series_option_update(); ?>
			<div class="updated"><p>Organize Series Plugin Options have been updated</p></div> 
			<?php
		}
	}
	$settings = get_option('org_series_options'); ?>
	<script type="text/javascript" src="../wp-includes/js/tw-sack.js"></script>
	<script type="text/javascript" src="list-manipulation.js"></script>
	<script type="text/javascript" src="../wp-includes/js/dbx.js"></script>
	<script type="text/javascript">
	//<![CDATA[
				addLoadEvent( function() {
					var manager = new dbxManager('orgSeries_options_meta');
					
					//create new docking boxes group
					var meta = new dbxGroup(
						'grabit', 		// container ID [/-_a-zA-Z0-9/]
						'vertical', 	// orientation ['vertical'|'horizontal']
						'10', 			// drag threshold ['n' pixels]
						'no',			// restrict drag movement to container axis ['yes'|'no']
						'10', 			// animate re-ordering [frames per transition, or '0' for no effect]
						'yes', 			// include open/close toggle buttons ['yes'|'no']
						'open', 		// default state ['open'|'closed']
						<?php echo "'" . js_escape(__('open')); ?>', 		// word for "open", as in "open this box"
						<?php echo "'" . js_escape(__('close')); ?>', 		// word for "close", as in "close this box"
						<?php echo "'" . js_escape(__('click-down and drag to move this box')); ?>', // sentence for "move this box" by mouse
						<?php echo "'" . js_escape(__('click to %toggle% this box')); ?>', // pattern-match sentence for "(open|close) this box" by mouse
						<?php echo "'" . js_escape(__('use the arrow keys to move this box')); ?>', // sentence for "move this box" by keyboard
						<?php echo "'" . js_escape(__(', or press the enter key to %toggle% it')); ?>',  // pattern-match sentence-fragment for "(open|close) this box" by keyboard
						'%mytitle%  [%dbxtitle%]' // pattern-match syntax for title-attribute conflicts
						);

					var advanced = new dbxGroup(
						'advancedstuff', 		// container ID [/-_a-zA-Z0-9/]
						'vertical', 		// orientation ['vertical'|'horizontal']
						'10', 			// drag threshold ['n' pixels]
						'yes',			// restrict drag movement to container axis ['yes'|'no']
						'10', 			// animate re-ordering [frames per transition, or '0' for no effect]
						'yes', 			// include open/close toggle buttons ['yes'|'no']
						'open', 		// default state ['open'|'closed']
						<?php echo "'" . js_escape(__('open')); ?>', 		// word for "open", as in "open this box"
						<?php echo "'" . js_escape(__('close')); ?>', 		// word for "close", as in "close this box"
						<?php echo "'" . js_escape(__('click-down and drag to move this box')); ?>', // sentence for "move this box" by mouse
						<?php echo "'" . js_escape(__('click to %toggle% this box')); ?>', // pattern-match sentence for "(open|close) this box" by mouse
						<?php echo "'" . js_escape(__('use the arrow keys to move this box')); ?>', // sentence for "move this box" by keyboard
						<?php echo "'" . js_escape(__(', or press the enter key to %toggle% it')); ?>',  // pattern-match sentence-fragment for "(open|close) this box" by keyboard
						'%mytitle%  [%dbxtitle%]' // pattern-match syntax for title-attribute conflicts
						);
				});
				//]]>
				</script>
<div class="wrap" id="orgSeries_div">
	<h2><?php _e('Organize Series Plugin Options'); ?></h2>			
	<div id="poststuff">
	<div id="moremeta">
	<div id="grabit" class="dbx-group">
	<fieldset id="pluginmetadiv" class="dbx-box">
		<h3 class="dbx-handle"><?php _e('Plugin Info') ?></h3>
		<div class="dbx-content">
		<p>Plugin documents (Installation help etc.) can be found <a href="http://www.unfoldingneurons.com/neurotic-plugins/organize_series_wordpress_plugin" title="The Organize Series Plugin page at unfoldingneurons.com">here</a></p>
		<p>If you'd like to donate to <a href="http://www.unfoldingneurons.com" title="Darren Ethier's (author) Blog">me</a> as an expression of thanks for the release of this plugin feel free to do so - and thanks!</p>
		<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_s-xclick" />
<input type="image" src="https://www.paypal.com/en_US/i/btn/x-click-but04.gif" border="0" name="submit" alt="Make payments with PayPal - it's fast, free and secure!" />
<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1" />
<input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----
" />
</form>
		</div>
	</fieldset>
		
		<div id="orgseriesnews">
			<?php include(ABSPATH . 'wp-content/plugins/orgSeries/organize-series-feed.php'); ?>
		</div> <?php /*rss feed related */ ?>
		</div>
	</div>
		<form action="" method="post">
		<?php /*EDITING OUT FOR NOW BECAUSE I HAVE NO IDEA HOW THE NONCE CHECK REALLY WORKS!
		if ( function_exists('wp_nonce_field') )
			wp_nonce_field('organize-series-update-options');
			*/?>
		<input type="hidden" name="submit_option" value="1" />
<div id="advancedstuff" class="dbx-group">
	
	<?php org_series_echo_fieldset_mainsettings($settings); ?>
		
		<?php org_series_echo_series_settings($settings); ?>
		
		<?php org_series_echo_fieldset_series_post_page($settings); ?>
		
		<?php org_series_echo_fieldset_series_meta($settings); ?>
		
</div>	
	<div>
		<p class="submit">
			<input type="submit" name="update_orgseries" value="<?php _e('Update Options') ?>" />
			<input type="submit" onclick='return confirm("Do you really want to reset to default options (all your custom changes will be lost)?");' name="reset_option" value="<?php _e('Reset options to default') ?>" />
			</p>
		</div>
	</form>
		</div>
	
</div>
		
<?php		
}

function org_series_echo_fieldset_mainsettings($settings){
	?>
	<div class="dbx-b-ox-wrapper">
	<fieldset id="main-options" class="dbx-box">
	<div class="dbx-h-andle-wrapper">
	<h3 class="dbx-handle"><?php _e('Main settings for Plugin') ?></h3>
	</div>
	<div class="dbx-c-ontent-wrapper">
	<div class="dbx-content">	
	<p><em>It is important that these options are set when first setting up the plugin or it may not work properly or as expected</em></p>
	<table width="100%" cellspacing="2" cellpadding="5" class="editform">
		<tr>
			<th width="30%" valign="top" scope="row"><label for="series_cats">Series Category ID</label></th>
			<td width="5%">
				<input name="series_cats" id="series_cats" type="text" value="<?php echo $settings['series_cats']; ?>" size="15" />
			</td>
			<td><small>Enter in this field the category id for the category that is your main series category (you can get the category id via the "Manage=>Categories" menu).</small></td>
		</tr>
		<tr>
			<th width="30%" valign="top" scope="row"><label for="auto_tag_postlist_toggle">Post List Auto Tag toggle:</label></th>
			<td width="5%">
				<input name="auto_tag_postlist_toggle" id="auto_tag_postlist_toggle" type="checkbox" value="<?php echo $setttings['auto_tag_postlist_toggle']; ?>" <?php checked('1', $settings['auto_tag_postlist_toggle']); ?> />
			</td>
			<td><small>Checking this will indicate that you would like the plugin to automatically insert the code into your theme for the listing of posts in a series when a post is displayed that is part of a series.  If you want more control over the way your series related posts look then leave this checkbox unchecked and enter in the tags manually (see plugin documents for instructions).</small></td>
		</tr>
		<tr>
			<th width="30%" valign="top" scope="row"><label for="auto_tag_seriesmeta_toggle">Series Meta Auto Tag toggle:</label></th>
			<td width="5%">
				<input name="auto_tag_seriesmeta_toggle" id="auto_tag_seriesmeta_toggle" type="checkbox" value="<?php echo $setttings['auto_tag_seriesmeta_toggle']; ?>" <?php checked('1', $settings['auto_tag_seriesmeta_toggle']); ?> />
			</td>
			<td><small>Checking this will indicate that you would like the plugin to automatically insert the code into your theme to display the series meta information (the title of the series the post belongs to, what part the post is in the series, and the total number of posts in the series) for a post that belong to a series.  If you want more control over the way your series related posts look then leave this checkbox unchecked and enter in the tags manually (see plugin documents for instructions).</small></td>
		</tr>
		<tr>
			<th width="30%" valign="top" scope="row"><label for="custom_css">Custom .css used?:</label></th>
			<td width="5%">
				<input name="custom_css" id="custom_css" type="checkbox" value="<?php echo $setttings['custom_css']; ?>" <?php checked('1', $settings['custom_css']); ?> />
			</td>
			<td><small>Checking this box indicates that the included .css file with this plugin will be used for styling.  If you uncheck this the plugin will <u>not</u> load up the custom .css file. This is useful if you want to add the styling code for the plugin yourself to your themes "style.css" file</small></td>
		</tr>
		</table>
		</div>
	</div>
	</fieldset>
	</div>
		<?php
}

function org_series_echo_series_settings($settings) {
	?>
	<div class="dbx-b-ox-wrapper">
	<fieldset id="series-cats-options" class="dbx-box">
	<div class="dbx-h-andle-wrapper">
	<h3 class="dbx-handle"><?php _e('Settings for the series categories page display') ?></h3>
	</div>
	<div class="dbx-c-ontent-wrapper">
	<div class="dbx-content">
	<table width="100%" cellspacing="2" cellpadding="5" class="editform">
		<tr>
			<th width="30%" valign="top" scope="row"><label for="cat_icon_width_cat_page">Width for category icon:</label></th>
			<td width="5%">
				<input name="cat_icon_width_cat_page" id="cat_icon_width_cat_page" type="text" value="<?php echo $settings['cat_icon_width_cat_page']; ?>" size="15" />
			</td>
			<td><small>Enter the pixel width for the category icon as displayed on the series category listing page. (NOTE: This only applies if you have the category icon plugin enabled.</small></td>
		</tr>
		<tr>
			<th width="30%" valign="top" scope="row"><label for="before_cat_icon_cat_page">HTML tag before category icon:</label></th>
			<td width="5%">
				<input name="before_cat_icon_cat_page" id="before_cat_icon_cat_page" type="text" value="<?php echo htmlspecialchars(stripslashes($settings['before_cat_icon_cat_page'])); ?>" size="15" />
			</td>
			<td><small>Customize the opening tag for the category icon if you wish.</small></td>
		</tr>
		<tr>
			<th width="30%" valign="top" scope="row"><label for="after_cat_icon_cat_page">HTML tag after category icon:</label></th>
			<td width="5%">
				<input name="after_cat_icon_cat_page" id="after_cat_icon_cat_page" type="text" value="<?php echo htmlspecialchars(stripslashes($settings['after_cat_icon_cat_page'])); ?>" size="15" />
			</td>
			<td><small>Customize the closing tag for the category icon if you wish.</small></td>
		</tr>
		<tr>
			<th width="30%" valign="top" scope="row"><label for="before_catlist-content_cat_page">HTML tag before category title and description:</label></th>
			<td width="5%">
				<input name="before_catlist-content_cat_page" id="before_catlist-content_cat_page" type="text" value="<?php echo htmlspecialchars(stripslashes($settings['before_catlist-content_cat_page'])); ?>" size="15" />
			</td>
			<td><small>Customize the opening tag for the section containing the category title and description.</small></td>
		</tr>
		<tr>
			<th width="30%" valign="top" scope="row"><label for="after_catlist-content_cat_page">HTML tag after category title and description:</label></th>
			<td width="5%">
				<input name="after_catlist-content_cat_page" id="after_catlist-content_cat_page" type="text" value="<?php echo htmlspecialchars(stripslashes($settings['after_catlist-content_cat_page'])); ?>" size="15" />
			</td>
			<td><small>Customize the closing tag for the section containing the category title and description.</small></td>
		</tr>
		<tr>
			<th width="30%" valign="top" scope="row"><label for="text_chk_cat_page">Display title and description of series?</label></th>
			<td width="5%">
				<input name="text_chk_cat_page" id="text_chk_cat_page" type="checkbox" value="<?php echo $settings['text_chk_cat_page']; ?>" <?php checked('1', $settings['text_chk_cat_page']); ?> />
			</td>
			<td><small>Check if you want the title and description of the series category to be displayed.  An example where you might <em>not</em> want this checked is if the category icon is all you want displayed (and is self-explanatory).</small></td>
		</tr>
		<tr>
			<th width="30%" valign="top" scope="row"><label for="cat_icon_chk_cat_page">Display the category icon?</label></th>
			<td width="5%">
				<input name="cat_icon_chk_cat_page" id="cat_icon_chk_cat_page" type="checkbox" value="<?php echo $settings['cat_icon_chk_cat_page']; ?>" <?php checked('1', $settings['cat_icon_chk_cat_page']); ?> />
			</td>
			<td><small>Check if you want the category icon for the series category to be displayed.</small></td>
		</tr>
		<tr>
			<th width="30%" valign="top" scope="row"><label for="cat_title_chk_cat_page">Display the series title?</label></th>
			<td width="5%">
				<input name="cat_title_chk_cat_page" id="cat_title_chk_cat_page" type="checkbox" value="<?php echo $settings['cat_title_chk_cat_page']; ?>" <?php checked('1', $settings['cat_title_chk_cat_page']); ?> />
			</td>
			<td><small>Check if you want the series title to be displayed.</small></td>
		</tr>
		<tr>
			<th width="30%" valign="top" scope="row"><label for="cat_description_cat_page">Display the category description?</label></th>
			<td width="5%">
				<input name="cat_description_cat_page" id="cat_description_cat_page" type="checkbox" value="<?php echo $settings['cat_description_cat_page']; ?>" <?php checked('1', $settings['cat_description_cat_page']); ?> />
			</td>
			<td><small>Check if you want the category description for the series category to be displayed.</small></td>
		</tr>
		<tr>
			<th width="30%" valign="top" scope="row"><label for="beforetitle_cat_page">HTML tag to enter before category title:</label></th>
			<td width="5%">
				<input name="beforetitle_cat_page" id="beforetitle_cat_page" type="text" value="<?php echo htmlspecialchars(stripslashes($settings['beforetitle_cat_page'])); ?>" size="15" />
			</td>
			<td><small>This is the HTML markup you would like to be placed after the title of the series category (note: permalinks are enabled already).</small></td>
		</tr>
		<tr>
			<th width="30%" valign="top" scope="row"><label for="aftertitle_cat_page">HTML tag to enter after category title:</label></th>
			<td width="5%">
				<input name="aftertitle_cat_page" id="aftertitle_cat_page" type="text" value="<?php echo htmlspecialchars(stripslashes($settings['aftertitle_cat_page'])); ?>" size="15" />
			</td>
			<td><small>This is the HTML markup you would like to be placed after the title of the series category (note: permalinks are enabled already).</small></td>
		</tr>
		<tr>
			<th width="30%" valign="top" scope="row"><label for="beforedescript_cat_page">HTML tag to enter before category description:</label></th>
			<td width="5%">
				<input name="beforedescript_cat_page" id="beforedescript_cat_page" type="text" value="<?php echo htmlspecialchars(stripslashes($settings['beforedescript_cat_page'])); ?>" size="15" />
			</td>
			<td><small>This is the HTML markup you would like to be placed before the description of the series category.</small></td>
		</tr>
		<tr>
			<th width="30%" valign="top" scope="row"><label for="afterdescript_cat_page">HTML tag to enter after category description:</label></th>
			<td width="5%">
				<input name="afterdescript_cat_page" id="afterdescript_cat_page" type="text" value="<?php echo htmlspecialchars(stripslashes($settings['afterdescript_cat_page'])); ?>" size="15" />
			</td>
			<td><small>This is the HTML markup you would like to be placed after the description of the series category.</small></td>
		</tr>
		<tr>
			<th width="30%" valign="top" scope="row"><label for="beforedisplay_cat_page">HTML tag to enter before the series category "container":</label></th>
			<td width="5%">
				<input name="beforedisplay_cat_page" id="beforedisplay_cat_page" type="text" value="<?php echo htmlspecialchars(stripslashes($settings['beforedisplay_cat_page'])); ?>" size="15" />
			</td>
			<td><small>This is the HTML markup you would like to be placed before the series category "container" (i.e. what each series category "block" looks like.)</small></td>
		</tr>
		<tr>
			<th width="30%" valign="top" scope="row"><label for="afterdisplay_cat_page">HTML tag to enter after the series category "container":</label></th>
			<td width="5%">
				<input name="afterdisplay_cat_page" id="afterdisplay_cat_page" type="text" value="<?php echo htmlspecialchars(stripslashes($settings['afterdisplay_cat_page'])); ?>" size="15" />
			</td>
			<td><small>This is the HTML markup you would like to be placed after the series category "container" (i.e. what each series category "block" looks like.)</small></td>
		</tr>
		<tr>
			<th width="30%" valign="top" scope="row"><label for="paging_cat_page">Turn paging on?</label></th>
			<td width="5%">
				<input name="paging_cat_page" id="paging_cat_page" type="checkbox" value="<?php echo $settings['paging_cat_page']; ?>" <?php checked('1', $settings['paging_cat_page']); ?> />
			</td>
			<td><small>If you would like the series category list page to be paged (when there are more series than what is selected per page) then indicate so here.</small></td>
		</tr>
		<tr>
			<th width="30%" valign="top" scope="row"><label for="perpage_cat_page">How many series category listings per page?</label></th>
			<td width="5%">
				<input name="perpage_cat_page" id="perpage_cat_page" type="text" value="<?php echo $settings['perpage_cat_page']; ?>" size="15" />
			</td>
			<td><small>Insert the number of category listings on the series page you want to be shown (if paging is turned on it will divide the listings into pages if there are more than the number in this field).</small></td>
		</tr>
		</table>
		</div>
		</div>
		</fieldset>
		</div>
		 <?php
}

function org_series_echo_fieldset_series_post_page($settings) {
	?>
	<div class="dbx-b-ox-wrapper">
	<fieldset id="postlist-options" class="dbx-box">
	<div class="dbx-h-andle-wrapper">
	<h3 class="dbx-handle"><?php _e('Settings for the series categories post page display') ?></h3>
	</div>
	<div class="dbx-c-ontent-wrapper">
	<div class="dbx-content">
	<table width="100%" cellspacing="2" cellpadding="5" class="editform">
		<tr>
			<th width="30%" valign="top" scope="row"><label for="before_series_title_post_page">HTML tag to enter before the series title:</label></th>
			<td width="5%">
				<input name="before_series_title_post_page" id="before_series_title_post_page" type="text" value="<?php echo htmlspecialchars(stripslashes($settings['before_series_title_post_page'])); ?>" size="15" />
			</td>
			<td><small>What opening tag do you want before the series title on the post display page?</small></td>
		</tr>
		<tr>
			<th width="30%" valign="top" scope="row"><label for="after_series_title_post_page">HTML tag to enter after the series title:</label></th>
			<td width="5%">
				<input name="after_series_title_post_page" id="after_series_title_post_page" type="text" value="<?php echo htmlspecialchars(stripslashes($settings['after_series_title_post_page'])); ?>" size="15" />
			</td>
			<td><small>What closing tag do you want before the series title on the post display page?</small></td>
		</tr>
		<tr>
			<th width="30%" valign="top" scope="row"><label for="before_post_title_list_post_page">HTML tag to enter before the list of post titles of in the series box:</label></th>
			<td width="5%">
				<input name="before_post_title_list_post_page" id="before_post_title_list_post_page" type="text" value="<?php echo htmlspecialchars(stripslashes($settings['before_post_title_list_post_page'])); ?>" size="15" />
			</td>
			<td><small>On a single post display page a series box is inserted if that post belongs to the series and a list of other posts in the series is listed in the box. You can choose the tags that surround this list (this is the before list tag)</small></td>
		</tr>
		<tr>
			<th width="30%" valign="top" scope="row"><label for="after_post_title_list_post_page">HTML tag to enter after the list of post titles of in the series box:</label></th>
			<td width="5%">
				<input name="after_post_title_list_post_page" id="after_post_title_list_post_page" type="text" value="<?php echo htmlspecialchars(stripslashes($settings['after_post_title_list_post_page'])); ?>" size="15" />
			</td>
			<td><small>On a single post display page a series box is inserted if that post belongs to the series and a list of other posts in the series is listed in the box. You can choose the tags that surround this list (this is the after list tag)</small></td>
		</tr>
		<tr>
			<th width="30%" valign="top" scope="row"><label for="before_title_post_page">HTML tag to enter before the post title of each post listed in the series box:</label></th>
			<td width="5%">
				<input name="before_title_post_page" id="before_title_post_page" type="text" value="<?php echo htmlspecialchars(stripslashes($settings['before_title_post_page'])); ?>" size="15" />
			</td>
			<td><small>On a single post display page a series box is inserted if that post belongs to the series and a list of other posts in the series is listed in the box. You can choose how the title of these posts are displayed.</small></td>
		</tr>
		<tr>
			<th width="30%" valign="top" scope="row"><label for="after_title_post_page">HTML tag to enter after the post title of each post listed in the series box:</label></th>
			<td width="5%">
				<input name="after_title_post_page" id="after_title_post_page" type="text" value="<?php echo htmlspecialchars(stripslashes($settings['after_title_post_page'])); ?>" size="15" />
			</td>
			<td><small>On a single post display page a series box is inserted if that post belongs to the series and a list of other posts in the series is listed in the box. You can choose how the title of these posts are displayed.</small></td>
		</tr>
		<tr>
			<th width="30%" valign="top" scope="row"><label for="series_intro_text_post_page">Introduction text for series box:</label></th>
			<td width="5%">
				<input name="series_intro_text_post_page" id="series_intro_text_post_page" type="text" value="<?php echo htmlspecialchars(stripslashes($settings['series_intro_text_post_page'])); ?>" size="15" />
			</td>
			<td><small>This is the text you want displayed (including html tags) in introducing the fact that this post belongs to a series.</small></td>
		</tr>
		<tr>
			<th width="30%" valign="top" scope="row"><label for="beforelistbox_post_page">HTML tag before series post list box:</label></th>
			<td width="5%">
				<input name="beforelistbox_post_page" id="beforelistbox_post_page" type="text" value="<?php echo htmlspecialchars(stripslashes($settings['beforelistbox_post_page'])); ?>" size="15" />
			</td>
			<td><small>This is the opening tag you want to be before container containing the list of posts in the series.</small></td>
		</tr>
		<tr>
			<th width="30%" valign="top" scope="row"><label for="afterlistbox_post_page">HTML tag after series post list box:</label></th>
			<td width="5%">
				<input name="afterlistbox_post_page" id="afterlistbox_post_page" type="text" value="<?php echo htmlspecialchars(stripslashes($settings['afterlistbox_post_page'])); ?>" size="15" />
			</td>
			<td><small>This is the closing tag you want to be before container containing the list of posts in the series.</small></td>
		</tr>
		<tr>
			<th width="30%" valign="top" scope="row"><label for="cat_icon_width_post_page">Width of Category Icon:</label></th>
			<td width="5%">
				<input name="cat_icon_width_post_page" id="cat_icon_width_post_page" type="text" value="<?php echo $settings['cat_icon_width_post_page']; ?>" size="15" />
			</td>
			<td><small>Enter the width (in pixels) you want for the category icon for the series in the in the post list box.  <em>NOTE:</em> This will only apply if you have the category icon plugin enabled.</small></td>
		</tr>
		<tr>
			<th width="30%" valign="top" scope="row"><label for="text_chk_post_page">Show title and description of the series?</label></th>
			<td width="5%">
				<input name="text_chk_post_page" id="text_chk_post_page" type="checkbox" value="<?php echo $settings['text_chk_post_page']; ?>" <?php checked('1', $settings['text_chk_post_page']); ?> />
			</td>
			<td><small>If you would like to show the title and description of the series in the series post list container then check this option.</small></td>
		</tr>
		<tr>
			<th width="30%" valign="top" scope="row"><label for="cat_icon_chk_post_page">Show category icon for the series?</label></th>
			<td width="5%">
				<input name="cat_icon_chk_post_page" id="cat_icon_chk_post_page" type="checkbox" value="<?php echo $settings['cat_icon_chk_post_page']; ?>" <?php checked('1', $settings['cat_icon_chk_post_page']); ?> />
			</td>
			<td><small>Check this if you want the category icon for the series to be shown (applies only if the category icon plugin is installed and enabled).</small></td>
		</tr>
		<tr>
			<th width="30%" valign="top" scope="row"><label for="cat_title_chk_post_page">Show series title?</label></th>
			<td width="5%">
				<input name="cat_title_chk_post_page" id="cat_title_chk_post_page" type="checkbox" value="<?php echo $settings['cat_title_chk_post_page']; ?>" <?php checked('1', $settings['cat_title_chk_post_page']); ?> />
			</td>
			<td><small>Check this if you want the series title to be shown (usually unchecked because the category icon for the series will be sufficient).</small></td>
		</tr>
		<tr>
			<th width="30%" valign="top" scope="row"><label for="cat_description_cat_post_page">Show series description?</label></th>
			<td width="5%">
				<input name="cat_description_cat_post_page" id="cat_description_cat_post_page" type="checkbox" value="<?php echo $settings['cat_description_cat_post_page']; ?>" <?php checked('1', $settings['cat_description_cat_post_page']); ?> />
			</td>
			<td><small>Check this if you want the series description to be shown.</small></td>
		</tr>	
		<tr>
			<th width="30%" valign="top" scope="row"><label for="cat_before_title_post_page">HTML tag before series title:</label></th>
			<td width="5%">
				<input name="cat_before_title_post_page" id="cat_before_title_post_page" type="text" value="<?php echo htmlspecialchars(stripslashes($settings['cat_before_title_post_page'])); ?>" size="15" />
			</td>
			<td><small>Customize the opening tag of the series title here.</small></td>
		</tr>
		<tr>
			<th width="30%" valign="top" scope="row"><label for="cat_after_title_post_page">HTML tag after series title:</label></th>
			<td width="5%">
				<input name="cat_after_title_post_page" id="cat_after_title_post_page" type="text" value="<?php echo htmlspecialchars(stripslashes($settings['cat_after_title_post_page'])); ?>" size="15" />
			</td>
			<td><small>Customize the closing tag of the series title here.</small></td>
		</tr>
		<tr>
			<th width="30%" valign="top" scope="row"><label for="cat_before_description_post_page">HTML tag before series description:</label></th>
			<td width="5%">
				<input name="cat_before_description_post_page" id="cat_before_description_post_page" type="text" value="<?php echo htmlspecialchars(stripslashes($settings['cat_before_description_post_page'])); ?>" size="15" />
			</td>
			<td><small>Customize the opening tag of the series description here.</small></td>
		</tr>
		<tr>
			<th width="30%" valign="top" scope="row"><label for="cat_after_description_post_page">HTML tag after series description:</label></th>
			<td width="5%">
				<input name="cat_after_description_post_page" id="cat_after_description_post_page" type="text" value="<?php echo htmlspecialchars(stripslashes($settings['cat_after_description_post_page'])); ?>" size="15" />
			</td>
			<td><small>Customize the closing tag of the series description here.</small></td>
		</tr>
		</table>
		</div>
		</div>
		</fieldset>
		</div>
		 <?php
}

function org_series_echo_fieldset_series_meta($settings) {
	?>
	<div class="dbx-b-ox-wrapper">
	<fieldset id="seriesmetaoptions" class="dbx-box">
	<div class="dbx-h-andle-wrapper">
	<h3 class="dbx-handle"><?php _e('Settings for the series meta display') ?></h3>
	</div>
	<div class="dbx-c-ontent-wrapper">
	<div class="dbx-content">
	<p>These settings will affect how the series meta information is displayed for each post that is part of a series (meta = what part the post is the series and how many total posts there are in the series)</p>
	<table width = "100%" cellspacing="2" cellpadding="5" class="editform">
		<tr>
			<th width="30%" valign="top" scope="row"><label for="before_series_meta">HTML tag to enter before the meta info:</label></th>
			<td width="5%">
			<input name="before_series_meta" id="before_series_meta" type="text" value="<?php echo htmlspecialchars(stripslashes($settings['before_series_meta'])); ?>" size="15" />
			</td>
			<td><small>What opening tag do you want before the series meta information displayed with each post in a series?</small></td>
		</tr>
		<tr>
			<th width="30%" valign="top" scope="row"><label for="after_series_meta">HTML tag to enter after the meta info:</label></th>
			<td width="5%">
			<input name="after_series_meta" id="after_series_meta" type="text" value="<?php echo htmlspecialchars(stripslashes($settings['after_series_meta'])); ?>" size="15" />
			</td>
			<td><small>What closing tag do you want after the series meta information displayed with each post in a series?</small></td>
		</tr>
		<tr>
			<th width="30%" valign="top" scope="row"><label for="series_meta_word">What word do you want to describe your posts (i.e. post, article, entry?)</label></th>
			<td width="5%">
			<input name="series_meta_word" id="series_meta_word" type="text" value="<?php echo htmlspecialchars(stripslashes($settings['series_meta_word'])); ?>" size="15" />
			</td>
			<td></td>
		</tr>
		</table>
		</div>
		</div>
		</fieldset>
		</div>
		 <?php
}
org_series_admin_page();
?>