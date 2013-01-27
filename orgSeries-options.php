<?php
/**
* This file contains all the necessary code for the Organize Series Options page.
*
* @package Organize Series
* @since 2.2
*/

add_action('admin_menu', 'orgseries_create_options');

//let's add a settings link on the WordPress plugin page
add_filter('plugin_action_links', 'inject_orgseries_settings_link', 10, 2 );


/**
 * Add Settings link to plugins.
 */
 function inject_orgseries_settings_link($links, $file) {
	static $this_plugin;
	global $orgseries;
	if ( !$this_plugin ) $this_plugin = 'organize-series/orgSeries.php';
	 
	if ( $file == $this_plugin ) {
		$settings_link = '<a href="options-general.php?page=orgseries_options_page">'.__("Settings", 'organize-series').'</a>';
		 array_unshift($links, $settings_link);
	}
	return $links;
 }

//add orgSeries to the options submenu and register settings
function orgseries_create_options() {
	global $orgseries;
		
	$page = add_options_page(__('Organize Series Options', 'organize-series'), __('Series Options', 'organize-series'), 'manage_options', 'orgseries_options_page', 'orgseries_option_page');
	add_action('admin_init', 'orgseries_options_init');
	add_action('admin_print_scripts-' . $page, 'orgseries_options_scripts');
}

//validate form values
function orgseries_validate($input) {
	global $orgseries, $wp_rewrite;
	$newinput = array();
	if ( isset($input['reset_option']) && $input['reset_option'] == 1 ) {
		
		if ($reset_options = $orgseries->add_settings(true)) {
			$input = $orgseries->settings;
			$update['updated_output'] = '<div class="updated"><p>'. __('Organize Series Plugin Options have been RESET','organize-series').'</p></div>';
			update_option('orgseries_update_message', $update['updated_output']);
			return $input;
		}
	} else {
		$update['updated_output'] = '<div class="updated"><p>' . __('Organize Series Plugin Options have been updated','organize-series') . '</p></div>';
	}
	//toggles and paging info
	$newinput['auto_tag_toggle'] = isset($input['auto_tag_toggle']) && $input['auto_tag_toggle'] == 1 ? 1 : 0;
	$newinput['auto_tag_nav_toggle'] = ( isset($input['auto_tag_nav_toggle']) && $input['auto_tag_nav_toggle'] == 1 ? 1 : 0 );
	$newinput['auto_tag_seriesmeta_toggle'] = ( isset($input['auto_tag_seriesmeta_toggle']) && $input['auto_tag_seriesmeta_toggle'] == 1 ? 1 : 0 );
	$newinput['custom_css'] = ( isset($input['custom_css']) && $input['custom_css'] == 1 ? 1 : 0 );
	$newinput['series_css_tougle'] = ( isset($input['series_css_tougle']) ? trim(stripslashes($input['series_css_tougle']), 1) : 'default' );
	$newinput['kill_on_delete'] = ( isset($input['kill_on_delete']) && $input['kill_on_delete'] == 1 ? 1 : 0 );
	$newinput['series_toc_url'] = preg_replace('/(^\/)|(\/$)/', '', $input['series_toc_url']);
	$newinput['series_custom_base'] = preg_replace('/(^\/)|(\/$)/', '', $input['series_custom_base']);
	
	$newinput['series_perp_toc'] = trim(preg_replace('/[^0-9]/', '', $input['series_perp_toc']));
	
	if ( strlen($input['series_toc_url']) <= 0 ) $newinput['series_toc_url'] = false;
	$newinput['series_toc_title'] = trim(stripslashes($input['series_toc_title']));
	$newinput['orgseries_api'] = trim($input['orgseries_api']);
	
	//template options
	$newinput['series_post_list_template'] = trim(stripslashes($input['series_post_list_template']));
	$newinput['series_post_list_post_template'] = trim(stripslashes($input['series_post_list_post_template']));
	$newinput['series_post_list_currentpost_template'] = trim(stripslashes($input['series_post_list_currentpost_template']));
	$newinput['series_meta_template'] = trim(stripslashes($input['series_meta_template']));
	$newinput['series_meta_excerpt_template'] = trim(stripslashes($input['series_meta_excerpt_template']));
	$newinput['series_table_of_contents_box_template'] = trim(stripslashes($input['series_table_of_contents_box_template']));
	$newinput['series_post_nav_template'] = trim(stripslashes($input['series_post_nav_template']));
	$newinput['series_nextpost_nav_custom_text'] = trim(stripslashes($input['series_nextpost_nav_custom_text']));
	$newinput['series_prevpost_nav_custom_text'] = trim(stripslashes($input['series_prevpost_nav_custom_text']));
	$newinput['series_posts_orderby'] = trim(stripslashes($input['series_posts_orderby']));
	$newinput['series_posts_order'] = trim(stripslashes($input['series_posts_order']));
	$newinput['latest_series_before_template'] = trim(stripslashes($input['latest_series_before_template']));
	$newinput['latest_series_inner_template'] = trim(stripslashes($input['latest_series_inner_template']));
	$newinput['latest_series_after_template'] = trim(stripslashes($input['latest_series_after_template']));
	
	//series-icon related settings
	$newinput['series_icon_width_series_page'] = (int) $input['series_icon_width_series_page'];
	$newinput['series_icon_width_post_page'] = (int) $input['series_icon_width_post_page'];
	$newinput['series_icon_width_latest_series'] = (int) $input['series_icon_width_latest_series'];
	
	$newinput['last_modified'] = gmdate("D, d M Y H:i:s", time());
	$return_input = apply_filters('orgseries_options', $newinput, $input);
	update_option('orgseries_update_message', $update['updated_output']);
	$wp_rewrite->flush_rules();
	return $return_input;
}

function orgseries_options_scripts() {
	wp_enqueue_script( 'orgseries_options' );
}
//register orgseries options
function orgseries_options_init() {
	$orgseries_options = 'orgseries_options';
	$org_opt = 'org_series_options';
	register_setting($orgseries_options, $org_opt, 'orgseries_validate');
	add_settings_section('series_automation_settings', 'Automation Settings', 'orgseries_main_section', 'orgseries_options_page');
	add_settings_field('series_automation_core_fieldset','<br />Series Automation Core Options', 'series_automation_core_fieldset', 'orgseries_options_page', 'series_automation_settings');
	
	add_settings_section('series_templates_settings', '<br /><br />Template Tag Options', 'orgseries_templates_section', 'orgseries_options_page');
	add_settings_field('series_templates_core_fieldset', 'Series Templates Core Options', 'series_templates_core_fieldset', 'orgseries_options_page', 'series_templates_settings');
	
	add_settings_section('series_icon_settings', '<br /><br />Series Icon Options', 'orgseries_icon_section', 'orgseries_options_page');
	add_settings_field('series_icon_core_fieldset', 'Series Icon Core Options', 'series_icon_core_fieldset', 'orgseries_options_page', 'series_icon_settings');
}

function orgseries_option_page() {
	global $orgseries, $wp_rewrite;
	$wp_rewrite->flush_rules();
	$org_opt = $orgseries->settings;
	$org_update_message = get_option('orgseries_update_message');
	?>
	<div class="wrap">
		<div class="icon32" id="icon-options-general"><br /></div>
		<h2><img src="<?php echo plugins_url('/images/orgseriesicon.png', __FILE__);?>" /><?php _e('  Organize Series Plugin Options', 'organize-series'); ?></h2>
	<?php
	echo $org_update_message;
	update_option('orgseries_update_message','');
	?>
	<div id="poststuff" class="metabox-holder has-right-sidebar">
		<div id="side-info-column" class="inner-sidebar">
			<div id="side-sortables" class="meta-box-sortables ui-sortable">
				
				<div id="plugin-info-div" class="postbox">
					<h3 class="hndle"><span><?php _e('Plugin Info', 'organize-series') ?></span></h3>
					<div class="inside">
					<p><?php _e('Plugin information can be found <a href="http://organizeseries.com" title="The Organize Series Website">here</a>','organize-series'); ?></p>
					<p><?php _e('If you\'d like to donate to <a href="http://www.unfoldingneurons.com" title="Darren Ethier\'s (author) Blog">me</a> as an expression of thanks for the release of this plugin feel free to do so - and thanks!', 'organize-series'); ?></p>
					<p><?php _e('You can also show you like Organize Series by <a href="http://wordpress.org/extend/plugins/organize-series">rating it</a> at wordpress.org', 'organize-series'); ?></p>
					<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
						<input type="hidden" name="cmd" value="_s-xclick" />
						<input type="image" src="https://www.paypal.com/en_US/i/btn/x-click-but04.gif" name="submit" alt="Make payments with PayPal - it's fast, free and secure!" />
						<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1" />
						<input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----
			" />
				</form>
				</div>
			</div>
			<?php if (file_exists(ABSPATH . WPINC . '/feed.php')) { ?>	
			
			<div id="organize-series-feed" class="postbox">	
				<h3 class="handle"><span><?php _e('Organize Series News', 'organize-series'); ?></span></h3>
				<div class="inside">
				<div id="orgseriesnews">
					<?php include(WP_CONTENT_DIR.'/plugins/' . SERIES_DIR .'/orgSeries-feed.php'); ?>
				</div> <?php /*rss feed related */ ?>
				</div>
			</div>
				<?php } ?>
			<!-- ADS HERE -->
			<a href="http://organizeseries.com/download" title="<?php _e('Click here to see all the awesome addons available', 'organize-series'); ?>"><img src="<?php echo plugins_url('/images/OS_Addons_logo.png', __FILE__); ?>" /></a>
			<br />
			<a href="http://organizeseries.com/pricing" title="<?php _e('Click here to find out about the Basic Support Package', 'organize-series'); ?>"><img src="<?php echo plugins_url('/images/OS_BasicSupport_logo.png', __FILE__); ?>" /></a>
			<a href="http://organizeseries.com/translating" title="<?php _e('Click here to find out how you can help with translating Organize Series', 'organize-series'); ?>"><img src="<?php echo plugins_url('/images/help-translate-logo.png', __FILE__); ?>" /></a>
			<br />
			<br />
			<!-- end ads -->
			<div id="token-legend" class="postbox">
				<h3 class="handle"><span><?php _e('Token legend', 'organize-series'); ?></span></h3>
				<div class="inside">
					<p><small><?php _e('The following is a legend of the tokens that are available for use in the custom template fields. These will be replaced with the appropriate values when the plugin runs.', 'organize-series'); ?></small></p>
					<strong>%series_icon%</strong><br />
						<em><?php _e('This will be replaced with the series icon for a series.', 'organize-series'); ?></em><br /><br />
					<strong>%series_icon_linked%</strong><br />
						<em><?php _e('Same as %series_icon% except that the series icon will be linked to the series page','organize-series'); ?></em><br /><br />
					<strong>%series_list%</strong><br />
						<em><?php _e('This token is for use with the orgSeries widget only - it references where you want the list of series titles to be inserted and requires that the template for each series title be also set.', 'organize-series'); ?></em><br /><br />
					<strong>%series_title%</strong><br />
						<em><?php _e('This will be replaced with the title of a series', 'organize-series'); ?></em><br /><br />
					<strong>%series_title_linked%</strong><br />
						<em><?php _e('Same as %series_title% except that it will also be linked to the series page', 'organize-series'); ?></em><br /><br />
					<strong>%post_title_list%</strong><br />
						<em><?php _e('Is the location token for where the contents of the post list post templates will appear.', 'organize-series'); ?></em><br /><br />
					<strong>%post_title%</strong><br />
						<em><?php _e('Will be replaced with the post title of a post in the series', 'organize-series'); ?></em><br /><br />
					<strong>%post_title_linked%</strong><br />
						<em><?php _e('Will be replaced with the post title of a post in the series linked to the page view of that post.', 'organize-series'); ?></em><br /><br />
					<strong>%previous_post%</strong><br />
						<em><?php _e('Will be replaced by the navigation link for the previous post in a series. The text will be whatever is included in the \'Custom Previous Post Navigation Text\' field. If that field is empty then the text will be the title of the post', 'organize-series'); ?></em><br /><br />
					<strong>%next_post%</strong><br />
						<em><?php _e('Will be replaced by the navigation link for the next post in a series. The text will be whatever is included in the \'Custom Next Post Navigation Text\' field. If that field is empty then the text will be the title of the post', 'organize-series'); ?></em><br /><br />
					<strong>%postcontent%</strong><br />
						<em><?php _e('Use this tag either before or after the rest of the template code.  It will indicate where you want the content of a post to display.', 'organize-series'); ?></em><br /><br />
					<strong>%series_part%</strong><br />
						<em><?php _e('Will display what part of a series the post is', 'organize-series'); ?></em><br /><br />
					<strong>%total_posts_in_series%</strong><br />
						<em><?php _e('Will display the total number of posts in a series', 'organize-series'); ?></em><br /><br />
					<strong>%series_description%</strong><br />
						<em><?php _e('Will display the description for the series', 'organize-series'); ?></em>
					
					<?php do_action('orgseries_token_description'); ?>
				</div>
			</div>
			</div>
		</div>
		<div id="post-body" class="has-sidebar">
			<div id="post-body-content" class="has-sidebar-content">
				<form action="options.php" method="post" id="series_options" name="series_options">
				<?php settings_fields('orgseries_options'); ?>
				<table class="widefat seriesmanage">
				<tbody id="the-list">
				<tr><td>
				
					<?php do_settings_sections('orgseries_options_page'); ?>
					
				</td></tr>
				</tbody>
				</table>
				<br />
				<?php 
				//$submit_text = __('Do you really want to reset to default options (all your custom changes will be lost)?', 'organize-series'); 
				//$script_text = "javascript:return confirm('".$submit_text."')"
				?>
				<span class="submit">
					<input type="hidden" name="org_series_options[updated_output]" value="" />
					<input type="hidden" name="org_series_options[reset_option]" class="reset_option" value="" /> 
					<input type="submit" name="update_orgseries" value="<?php _e('Update Options', 'organize-series'); ?>" />
					<input type="submit" name="option_reset" value="<?php _e('Reset options to default', 'organize-series'); ?>" />
				</span>
				</form>
				<div id="TBcontent" class="reset_dialog" style="display:none;">
					<p> Clicking Yes will reset the options to the defaults and you will lose all customizations. Or you can click cancel and return.</p>
					<input type="submit" id="TBcancel" value="Cancel" />
					<input type="submit" id="TBsubmit" value="Yes" />
				</div>
		</div>
		</div>
	<br class="clear" />
	</div>
	</div>
	<?php
}

function orgseries_main_section() {
	global $orgseries;
	?>
	<p><?php _e('Choose from the following options for turning on or off automatic insertion of template tags for Organize Series into your blog.  If you wish to have more control over the location of the template tags (you power user you) then deselect as needed.', 'organize-series'); ?></p>
	<?php
}

function orgseries_templates_section() {
	global $orgseries;
	?>
	<p><?php _e('This section is where you tell the plugin how you would like to format the various displays of the series information.  Only play with this if you are familiar with html/css.  Use the "template tokens" to indicate where various series related data should go and/or where the template tag should be inserted (if auto-tag is enabled).', 'organize-series'); ?></p>
	<?php
}

function orgseries_icon_section() {
	global $orgseries;
	?>
	<p><?php _e('This section is for setting the series icon options (note if you do not include one of the %tokens% for series icon in the template settings section then series-icons will not be displayed. All images for series-icons will upload into your default wordpress upload directory.', 'organize-series'); ?></p>
	<?php
}

function series_automation_core_fieldset() {
	global $orgseries;
	$org_opt = $orgseries->settings;
	$org_name = 'org_series_options';
	$series_css_tougle = is_array($org_opt) && isset($org_opt['series_css_tougle']) ? $org_opt['series_css_tougle'] : 'default';
	$series_perp_toc = is_array($org_opt) && isset($org_opt['series_perp_toc']) ? $org_opt['series_perp_toc'] : 10;
	?>
	<div class="metabox-holder">	
		<div class="postbox-container" style="width: 99%;line-height:normal;">
			<div id="topic-toc-settings-automation-core" class="postbox" style="line-height:normal;">
					<div class="inside" style="padding: 10px;">
					<input name="<?php echo $org_name;?>[auto_tag_toggle]" value="1" id="auto_tag_toggle" type="checkbox" <?php checked('1', $org_opt['auto_tag_toggle']); ?> /> <?php _e('Display series post list box?', 'organize-series'); ?>
					<small><em><?php _e('Selecting this will indicate that you would like the plugin to automatically insert the code into your theme for the listing of posts in a series when a post is displayed that is part of a series.  [default=selected]', 'organize-series'); ?></em></small><br /><br />
					<input name="<?php echo $org_name; ?>[auto_tag_nav_toggle]" id="auto_tag_nav_toggle" type="checkbox" value="1" <?php checked('1', $org_opt['auto_tag_nav_toggle']); ?> /> <?php _e('Display series navigation links?', 'organize-series'); ?>
					<small><em><?php _e('Selecting this will indicate that you would like the plugin to automatically insert the code into your theme for the displaying the series navigation links.  [default=selected]', 'organize-series'); ?></em></small><br /><br />
					<input name="<?php echo $org_name; ?>[auto_tag_seriesmeta_toggle]" id="auto_tag_seriesmeta_toggle" type="checkbox" value="1" <?php checked('1', $org_opt['auto_tag_seriesmeta_toggle']); ?> /> <?php _e('Display series meta information with posts?', 'organize-series'); ?>
					<small><em><?php _e('Series meta will include whatever is listed in the Template tag options for the series meta tag (see settings on this page). [default = selected]', 'organize-series'); ?></em></small><br /><br />
					<input name="<?php echo $org_name; ?>[custom_css]" id="custom_css" type="checkbox" value="1" <?php checked('1', $org_opt['custom_css']); ?> /> <?php _e('Use custom .css?', 'organize-series'); ?>
					<small><em><?php _e('Leaving this box checked will make the plugin use the included .css file.  If you uncheck it you will need to add styling for the plugin in your themes "style.css" file. [default = checked]', 'organize-series'); ?></em></small>
					<br />
					&emsp;<em><?php _e('.css style for:', 'organize-series'); ?></em><br />
					&emsp;<input name="<?php echo $org_name; ?>[series_css_tougle]" class="css_style" id="css_dark" type="radio" value="dark" <?php checked('dark', $series_css_tougle); ?> <?php disabled('0', $org_opt['custom_css']) ?> /><?php _e(' dark themes', 'organize-series'); ?> <br />
					&emsp;<input name="<?php echo $org_name; ?>[series_css_tougle]" class="css_style" id="css_light" type="radio" value="light" <?php checked('light', $series_css_tougle); ?> <?php disabled('0', $org_opt['custom_css']) ?> /><?php _e(' light themes', 'organize-series'); ?> <br />
					&emsp;<input name="<?php echo $org_name; ?>[series_css_tougle]" class="css_style" id="css_default" type="radio" value="default" <?php checked('default', $series_css_tougle); ?> <?php disabled('0', $org_opt['custom_css']) ?> /><?php _e(' default .css style', 'organize-series'); ?> <br />
					<br />
					<strong><?php _e('Series Table of Contents URL:', 'organize-series'); ?></strong><br />
					<?php bloginfo('url') ?>/<input type="text" name="<?php echo $org_name; ?>[series_toc_url]" value="<?php echo htmlspecialchars($org_opt['series_toc_url']); ?>" /><br />
					<small><em><?php _e('Enter the path where you want the Series Table of Contents to be shown. NOTE: this ONLY applies when you have "Permalinks" enabled in WordPress.', 'organize-series'); ?></em></small><br /><br />
					
					<strong><?php _e('Series Per Page:', 'organize-series'); ?></strong>
					<input type="text" name="<?php echo $org_name; ?>[series_perp_toc]" style="width:40px" value="<?php echo (int) ($series_perp_toc); ?>" /><br />
					<small><em><?php _e('Set how many series you want per page on the Series TOC Page.', 'organize-series'); ?></em></small><br /><br />
					
					<strong><?php _e('Series Custom Base:', 'organize-series'); ?></strong><br />
					<input type="text" name="<?php echo $org_name; ?>[series_custom_base]" value="<?php echo htmlspecialchars($org_opt['series_custom_base']); ?>" /><br />
					<small><em><?php _e('Set what you want to use as the base for referring to your series structure in permalinks series archive pages. NOTE: This ONLY applies when you have "Permalinks" enabled in WordPress', 'organize-series'); ?></em></small><br /><br />
					<strong><?php _e('Series Table of Contents Title:', 'organize-series'); ?></strong><input type="text" name="<?php echo $org_name; ?>[series_toc_title]" value="<?php echo htmlspecialchars($org_opt['series_toc_title']); ?>" style="width:300px;"/><br />
					<small><em><?php _e('Enter what you want to appear in the browser title when readers are viewing the series table of contents page.', 'organize-series'); ?></em></small><br /> <br />
					<input name="<?php echo $org_name; ?>[series_posts_orderby]" id="series_posts_orderby_part" type="radio" value="meta_value" <?php checked('meta_value', $org_opt['series_posts_orderby']); ?> /><?php _e('order by series part', 'organize-series'); ?>
					<input name="<?php echo $org_name; ?>[series_posts_orderby]" id="series_posts_orderby_date" type="radio" value="post_date" <?php checked('post_date', $org_opt['series_posts_orderby']); ?> /><?php _e('Order by date', 'organize-series'); ?>
					<br />
					<input name="<?php echo $org_name; ?>[series_posts_order]" id="series_posts_order_ASC" type="radio" value="ASC" <?php checked('ASC', $org_opt['series_posts_order']); ?> /><?php _e('Ascending', 'organize-series'); ?>
					<input name="<?php echo $org_name; ?>[series_posts_order]" id="series_posts_order_DESC" type="radio" value="DESC" <?php checked('DESC', $org_opt['series_posts_order']); ?> /><?php _e('Descending', 'organize-series'); ?>
					<br />
					<small><em><?php _e('You can choose what order you want the posts on a series archive page to be displayed.  Default is by date, descending.', 'organize-series'); ?></em></small>
					<br />
					<br />
					<span style="background-color:#ff3366; padding: 5px; padding-bottom: 8px;">
					<input name="<?php echo $org_name; ?>[kill_on_delete]" id="kill_on_delete" type="checkbox" value="1" <?php checked('1', $org_opt['kill_on_delete']); ?> /> <?php _e('Delete all Organize Series related data from the database when deleting this plugin?  (BE CAREFUL!)', 'organize-series'); ?>
					</span>
					<br />
					<br />
					<strong><?php _e('Organize Series API Key', 'organize-series'); ?></strong><input type="text" name="<?php echo $org_name; ?>[orgseries_api]" value="<?php echo trim($org_opt['orgseries_api']); ?>" style="width:300px;"/><br />
					<small><em><?php printf(__('The API key is for users who have purchased a <a href="%s" title="Click Here to read about the packages available">Paid package</a> and/or a <a href="%s" title="Click here to see all addons available">commercial addon</a>. If this is you, you can obtain your API key by logging into <a href="%s">OrganizeSeries.com</a> and you\'ll see it with your user profile.',  'organize-series'), 'http://organizeseries.com/pricing/', 'http://organizeseries.com/download/', 'http://organizeseries.com'); ?></em></small>
					</div>
				</div>
			</div>
		</div>
	<?php
}

function series_templates_core_fieldset() {
	global $orgseries;
	$org_opt = $orgseries->settings;
	$org_name = 'org_series_options';
	?>
	<div class="metabox-holder">	
		<div class="postbox-container" style="width: 99%;line-height:normal;">
			<div id="topic-toc-settings-series-template-core" class="postbox" style="line-height:normal;">
				<div class="inside" style="padding: 10px;">
					<strong><?php _e('Series Post List Template:', 'organize-series'); ?></strong><br />
					<small><?php _e('This affects the list of series in a post on the page of a post belonging to a series [template tag -> wp_postlist_display()]', 'organize-series'); ?></small><br />
					<textarea name="<?php echo $org_name; ?>[series_post_list_template]" id="series_post_list_template" rows="4" class="template"><?php echo htmlspecialchars($org_opt['series_post_list_template']); ?></textarea><br />
					<br />
					<strong><?php _e('Series Post List Post Title Template:', 'organize-series'); ?></strong><br />
					<small><?php _e('Use this to indicate what html tags will surround the post title in the series post list.', 'organize-series'); ?></small><br/>
					<textarea name="<?php echo $org_name; ?>[series_post_list_post_template]" id="series_post_list_post_template" rows="4"  class="template"><?php echo htmlspecialchars($org_opt['series_post_list_post_template']); ?></textarea><br />
					<br />
					<?php do_action('plist_ptitle_template_unpublished') ?>
					<strong><?php _e('Series Post List Current Post Title Template:', 'organize-series'); ?></strong><br />
					<small><?php _e('Use this to style how you want the post title in the post list that is the same as the current post to be displayed.', 'organize-series'); ?></small><br />
					<textarea name="<?php echo $org_name; ?>[series_post_list_currentpost_template]" id="series_post_list_currentpost_template" rows="4"  class="template"><?php echo htmlspecialchars($org_opt['series_post_list_currentpost_template']); ?></textarea><br />
					<br />
					<strong><?php _e('Series Post Navigation Template:', 'organize-series'); ?></strong><br />
					<small><?php _e('Use this to style the Next/Previous post navigation strip on posts that are part of a series. (Don\'t forget to use the %postcontent% token to indicate where you want the navigation to show).', 'organize-series'); ?></small><br />
					<textarea name="<?php echo $org_name; ?>[series_post_nav_template]" id="series_post_nav_template" rows="4"  class="template"><?php echo htmlspecialchars($org_opt['series_post_nav_template']);?></textarea><br />
					<br />
					<input name="<?php echo $org_name; ?>[series_nextpost_nav_custom_text]" id="series_nextpost_nav_custom_text" type="text" value="<?php echo htmlspecialchars($org_opt['series_nextpost_nav_custom_text']); ?>" size="40" /> <?php _e('Custom Next Post Text (if this is left blank, the post title will be used)', 'organize-series'); ?><br />
					<input name="<?php echo $org_name; ?>[series_prevpost_nav_custom_text]" id="series_prevpost_nav_custom_text" type="text" value="<?php echo htmlspecialchars($org_opt['series_prevpost_nav_custom_text']); ?>" size="40" /> <?php _e('Custom previous post navigation text. (if this is left blank, the post title will be used)', 'organize-series'); ?><br />
					<br />
					<strong><?php _e('Series Table of Contents Listings:', 'organize-series'); ?></strong><br />
					<small><?php _e('This will affect how each series is listed on the Series Table of Contents Page (created at plugin init) [template tag -> wp_serieslist_display()]', 'organize-series'); ?></small><br />
					<textarea name="<?php echo $org_name; ?>[series_table_of_contents_box_template]" id="series_table_of_contents_box_template" rows="4"  class="template"><?php echo htmlspecialchars($org_opt['series_table_of_contents_box_template']); ?></textarea><br />
					<br />
					<strong><?php _e('Series Meta:', 'organize-series'); ?></strong><br />
					<small><?php _e('This will control how and what series meta information is displayed with posts that are part of a series. [template tag -> wp_seriesmeta_write()]', 'organize-series'); ?></small><br />
					<textarea name="<?php echo $org_name; ?>[series_meta_template]" id="series_meta_template" rows="4"  class="template"><?php echo htmlspecialchars($org_opt['series_meta_template']); ?></textarea><br />
					<br />
					<strong><?php _e('Series Meta (with excerpts):', 'organize-series'); ?></strong><br />
					<small><?php _e('This will control how and what series meta information is displayed with posts that are part of a series when the_excerpt is called. [template tag -> wp_seriesmeta_write(true)]', 'organize-series'); ?></small><br />
					<textarea name="<?php echo $org_name; ?>[series_meta_excerpt_template]" id="series_meta_excerpt_template" rows="4"  class="template"><?php echo htmlspecialchars($org_opt['series_meta_excerpt_template']); ?></textarea><br />
					<br />
					<strong><?php _e('Latest Series (tags before):', 'organize-series'); ?></strong><br />
					<small><?php _e('Put here any html you want before latest series information NOTE: series template tokens WILL NOT be converted here.', 'organize-series'); ?></small><br />
					<textarea name="<?php echo $org_name; ?>[latest_series_before_template]" id="latest_series_before_template" rows="4"  class="template"><?php echo htmlspecialchars($org_opt['latest_series_before_template']); ?></textarea><br />
					<br />
					<strong><?php _e('Latest Series (inner tags):', 'organize-series'); ?></strong><br />
					<small><?php _e('This will control the layout/style and contents that will be returned with the latest_series() template tag (both via widget and/or manual calls).  NOTE: Organize Series %tokens% can be used in this field.', 'organize-series'); ?></small><br />
					<textarea name="<?php echo $org_name; ?>[latest_series_inner_template]" id="latest_series_inner_template" rows="4"  class="template"><?php echo htmlspecialchars($org_opt['latest_series_inner_template']); ?></textarea><br />
					<br />
					<strong><?php _e('Latest Series (tags after):', 'organize-series'); ?></strong><br />
					<small><?php _e('Put here any html you want after latest series information NOTE: series template tokens WILL NOT be converted here.', 'organize-series'); ?></small><br />
					<textarea name="<?php echo $org_name; ?>[latest_series_after_template]" id="latest_series_after_template" rows="4"  class="template"><?php echo htmlspecialchars($org_opt['latest_series_after_template']); ?></textarea><br />	
				</div>
			</div>
		</div>
	</div>
	<?php
}

function series_icon_core_fieldset() {
	global $orgseries;
	$org_opt = $orgseries->settings;
	$org_name = 'org_series_options';
	?>
	<div class="metabox-holder">
	<div class="postbox-container" style="width: 99%;line-height:normal;">
		<div id="topic-toc-settings-icon-core" class="postbox" style="line-height:normal;">
			<div class="inside" style="padding: 10px;">
				<input name="<?php echo $org_name;?>[series_icon_width_series_page]" id="series_icon_width_series_page" type="text" value="<?php echo $org_opt['series_icon_width_series_page']; ?>" size="10" /> <?php _e('Width for icon on series table of contents page (in pixels).', 'organize-series'); ?>
				<br />
				<input name="<?php echo $org_name;?>[series_icon_width_post_page]" id="series_icon_width_post_page" type="text" value="<?php echo $org_opt['series_icon_width_post_page']; ?>" size="10" /> <?php _e('Width for icon on a post page (in pixels).', 'organize-series'); ?>
				<br />	
				<input name="<?php echo $org_name;?>[series_icon_width_latest_series]" id="series_icon_width_latest_series" type="text" value="<?php echo $org_opt['series_icon_width_latest_series']; ?>" size="10" /> <?php _e('Width for icon if displayed via the latest series template (in pixels).', 'organize-series'); ?>
			</div>
		</div>
	</div>
	</div>
	<?php
}
?>