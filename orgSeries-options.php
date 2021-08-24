<?php
/**
* This file contains all the necessary code for the Publishpress Series Options page.
*
* @package Publishpress Series
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
	if ( !$this_plugin ) $this_plugin = PPSERIES_BASE_NAME;

	if ( $file == $this_plugin ) {
		$settings_link = '<a href="options-general.php?page=orgseries_options_page">'.__("Settings", 'organize-series').'</a>';
		 array_unshift($links, $settings_link);
	}
	return $links;
 }

//add orgSeries to the options submenu and register settings
function orgseries_create_options() {
	global $orgseries;

	$page = add_options_page(__('PublishPress Series Options', 'organize-series'), __('Series Options', 'organize-series'), 'manage_options', 'orgseries_options_page', 'orgseries_option_page');
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
			$update['updated_output'] = '<div class="updated"><p>'. __('PublishPress Series Plugin Options have been RESET','organize-series').'</p></div>';
			update_option('orgseries_update_message', $update['updated_output']);
			return $input;
		}
	} else {
		$update['updated_output'] = '<div class="updated"><p>' . __('PublishPress Series Plugin Options have been updated','organize-series') . '</p></div>';
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
	$newinput['series_toc_title'] = isset($input['series_toc_title']) ? trim(stripslashes($input['series_toc_title'])) : '';
	$newinput['orgseries_api'] = isset($input['orgseries_api']) ? trim($input['orgseries_api']) : '';

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

	add_settings_section('series_uninstall_settings', 'Uninstall', 'orgseries_uninstall_section', 'orgseries_options_page');
	add_settings_field('series_uninstall_core_fieldset', 'Series uninstall', 'series_uninstall_core_fieldset', 'orgseries_options_page', 'series_uninstall_settings');


  add_filter( 'ppseries_admin_settings_tabs', 'ppseries_filter_admin_settings_tabs');
}


function ppseries_filter_admin_settings_tabs($settings_tabs){
  $settings_tabs['series_uninstall_settings'] = __('Uninstall', 'organize-series-cpt');
  return $settings_tabs;
}

function orgseries_option_page() {
	global $orgseries, $wp_rewrite;
	$wp_rewrite->flush_rules();
	$org_opt = $orgseries->settings;
	$org_update_message = get_option('orgseries_update_message');
	?>
	<div class="wrap">
		<div class="icon32" id="icon-options-general"><br /></div>
		<h2><?php _e('PublishPress Series Plugin Options', 'organize-series'); ?></h2>
	<?php
	echo $org_update_message;
	update_option('orgseries_update_message','');
	?>

  <h2 class="nav-tab-wrapper ppseries-settings-tab">
  <?php
    $settings_tabs = ppseries_admin_settings_tabs();
    foreach($settings_tabs as $settings_tab_key => $settings_tab_label){
      if(apply_filters('ppseries_settings_'.$settings_tab_key.'_tabbed', false)){
        $tabbled_class = 'series-tab-content';
      }else{
        $tabbled_class = '';
      }
      echo '<a id="'.$settings_tab_key.'-series-tab" class="nav-tab '.$tabbled_class.'" href="#'.$settings_tab_key.'">'.$settings_tab_label.'</a>';
    }
  ?>
  </h2>


	<div id="poststuff" class="metabox-holder has-right-sidebar">

		<div id="side-info-column" class="inner-sidebar ppseries-settings-tab-content series_templates_settings-series-sidebar">
			<div id="side-sortables" class="meta-box-sortables ui-sortable">

			<div id="token-legend" class="postbox">
				<h3 class="handle"><span><?php _e('Overview', 'organize-series'); ?></span></h3>
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
            <?php do_action('ppseries_licence_key_form'); ?>
			</div>
		</div>
		<div id="post-body" class="has-sidebar">
			<div id="post-body-content" class="has-sidebar-content ppseries-settings-body-content">
				<form action="options.php" method="post" id="series_options" name="series_options">
				<?php settings_fields('orgseries_options'); ?>
				<table class="widefat seriesmanage">
				<tbody id="the-list">
				<tr><td>

					<?php ppseries_do_settings_sections('orgseries_options_page'); ?>

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
					<input type="submit" class="button-primary" name="update_orgseries" value="<?php _e('Update Options', 'organize-series'); ?>" />
					<input type="submit" class="button" name="option_reset" value="<?php _e('Reset options to default', 'organize-series'); ?>" />
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
	<p><?php _e('This section is for the icons that show with your series. Note that you must use a tokens for the icon in the "Templates" settings.', 'organize-series'); ?></p>
	<?php
}

function orgseries_uninstall_section() {
	global $orgseries;
	?>
	<?php
}

function series_automation_core_fieldset() {
	global $orgseries;
	$org_opt = $orgseries->settings;
	$org_name = 'org_series_options';
	$series_css_tougle = is_array($org_opt) && isset($org_opt['series_css_tougle']) ? $org_opt['series_css_tougle'] : 'default';
	$series_perp_toc = is_array($org_opt) && isset($org_opt['series_perp_toc']) ? $org_opt['series_perp_toc'] : 10;
	?>
  <h2 class="ppseries-settings-header"><?php _e('Series Automation Core Options', 'organize-series'); ?></h2>
	<div class="metabox-holder">
		<div class="postbox-container" style="width: 99%;line-height:normal;">
			<div id="topic-toc-settings-automation-core" class="postbox" style="line-height:normal;border:unset;">
					<div class="inside" style="padding: 10px;">
					<label><input name="<?php echo $org_name;?>[auto_tag_toggle]" value="1" id="auto_tag_toggle" type="checkbox" <?php checked('1', $org_opt['auto_tag_toggle']); ?> /> <?php _e('Display series post list box?', 'organize-series'); ?></label><br /><br />
					<label><input name="<?php echo $org_name; ?>[auto_tag_nav_toggle]" id="auto_tag_nav_toggle" type="checkbox" value="1" <?php checked('1', $org_opt['auto_tag_nav_toggle']); ?> /> <?php _e('Display series navigation links?', 'organize-series'); ?></label>
				<br /><br />
					<label><input name="<?php echo $org_name; ?>[auto_tag_seriesmeta_toggle]" id="auto_tag_seriesmeta_toggle" type="checkbox" value="1" <?php checked('1', $org_opt['auto_tag_seriesmeta_toggle']); ?> /> <?php _e('Display series meta information with posts?', 'organize-series'); ?></label>
					<br /><br />
					<label><input name="<?php echo $org_name; ?>[custom_css]" id="custom_css" type="checkbox" value="1" <?php checked('1', $org_opt['custom_css']); ?> /> <?php _e('Use custom .css?', 'organize-series'); ?></label>

					<br />
					&emsp;<em><?php _e('.css style for:', 'organize-series'); ?></em><br />
					&emsp;<label><input name="<?php echo $org_name; ?>[series_css_tougle]" class="css_style" id="css_dark" type="radio" value="dark" <?php checked('dark', $series_css_tougle); ?> <?php disabled('0', $org_opt['custom_css']) ?> /><?php _e(' dark themes', 'organize-series'); ?> </label><br />
					&emsp;<label><input name="<?php echo $org_name; ?>[series_css_tougle]" class="css_style" id="css_light" type="radio" value="light" <?php checked('light', $series_css_tougle); ?> <?php disabled('0', $org_opt['custom_css']) ?> /><?php _e(' light themes', 'organize-series'); ?> </label><br />
					&emsp;<label><input name="<?php echo $org_name; ?>[series_css_tougle]" class="css_style" id="css_default" type="radio" value="default" <?php checked('default', $series_css_tougle); ?> <?php disabled('0', $org_opt['custom_css']) ?> /><?php _e(' default .css style', 'organize-series'); ?> </label><br />
					<br />
					<strong><?php _e('Series Table of Contents URL:', 'organize-series'); ?></strong><br />
					<?php bloginfo('url') ?>/<input type="text" name="<?php echo $org_name; ?>[series_toc_url]" value="<?php echo htmlspecialchars($org_opt['series_toc_url']); ?>" /><br />
					<br />

					<strong><?php _e('Series Per Page:', 'organize-series'); ?></strong>
					<input type="text" name="<?php echo $org_name; ?>[series_perp_toc]" style="width:40px" value="<?php echo (int) ($series_perp_toc); ?>" /><br />
					<br />

					<strong><?php _e('Series Custom Base:', 'organize-series'); ?></strong><br />
					<input type="text" name="<?php echo $org_name; ?>[series_custom_base]" value="<?php echo htmlspecialchars($org_opt['series_custom_base']); ?>" /><br />
					<br />
					<strong><?php _e('Series Table of Contents Title:', 'organize-series'); ?></strong><br /><input type="text" name="<?php echo $org_name; ?>[series_toc_title]" value="<?php echo htmlspecialchars($org_opt['series_toc_title']); ?>" style="width:300px;"/><br />
					<br />
					<label><input name="<?php echo $org_name; ?>[series_posts_orderby]" id="series_posts_orderby_part" type="radio" value="meta_value" <?php checked('meta_value', $org_opt['series_posts_orderby']); ?> /><?php _e('order by series part', 'organize-series'); ?></label>
					<label><input name="<?php echo $org_name; ?>[series_posts_orderby]" id="series_posts_orderby_date" type="radio" value="post_date" <?php checked('post_date', $org_opt['series_posts_orderby']); ?> /><?php _e('Order by date', 'organize-series'); ?></label>
					<br />
					<label><input name="<?php echo $org_name; ?>[series_posts_order]" id="series_posts_order_ASC" type="radio" value="ASC" <?php checked('ASC', $org_opt['series_posts_order']); ?> /><?php _e('Ascending', 'organize-series'); ?></label>
					<label><input name="<?php echo $org_name; ?>[series_posts_order]" id="series_posts_order_DESC" type="radio" value="DESC" <?php checked('DESC', $org_opt['series_posts_order']); ?> /><?php _e('Descending', 'organize-series'); ?></label>
					<br />


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
  <h2 class="ppseries-settings-header"><?php _e('Series Templates Core Options', 'organize-series'); ?></h2>
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
					<small><?php _e('This will control the layout/style and contents that will be returned with the latest_series() template tag (both via widget and/or manual calls).  NOTE: PublishPress Series %tokens% can be used in this field.', 'organize-series'); ?></small><br />
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
			<div class="inside">

          <table class="form-table ppseries-settings-table">
            <tbody>

              <tr valign="top">
                <th scope="row"><label for="series_icon_width_series_page"><?php _e('Width for icon on series table of contents page (in pixels)', 'organize-series'); ?></label></th>
                <td><input  min="1" max="1000000000"name="<?php echo $org_name;?>[series_icon_width_series_page]" id="series_icon_width_series_page" type="number" value="<?php echo $org_opt['series_icon_width_series_page']; ?>" /></p>
                </td>
                </tr>

                <tr valign="top">
                  <th scope="row"><label for="series_icon_width_post_page"><?php _e('Width for icon on a post page (in pixels).', 'organize-series'); ?></label></th>
                  <td><input min="1" max="1000000000" name="<?php echo $org_name;?>[series_icon_width_post_page]" id="series_icon_width_post_page" type="number" value="<?php echo $org_opt['series_icon_width_post_page']; ?>" /></p>
                  </td>
                  </tr>

                  <tr valign="top">
                    <th scope="row"><label for="series_icon_width_latest_series"><?php _e('Width for icon if displayed via the latest series template (in pixels).', 'organize-series'); ?></label></th>
                    <td><input min="1" max="1000000000" name="<?php echo $org_name;?>[series_icon_width_latest_series]" id="series_icon_width_latest_series" type="number" value="<?php echo $org_opt['series_icon_width_latest_series']; ?>" /></p>
                    </td>
                    </tr>

            </tbody>
        </table>


			</div>
		</div>
	</div>
	</div>
	<?php
}

function series_uninstall_core_fieldset() {
	global $orgseries;
	$org_opt = $orgseries->settings;
	$org_name = 'org_series_options';
	?>
  <h2 class="ppseries-settings-header"><?php _e('Series Settings', 'organize-series'); ?></h2>
  <span style="padding: 5px; padding-bottom: 8px;">
  <label><input name="<?php echo $org_name; ?>[kill_on_delete]" id="kill_on_delete" type="checkbox" value="1" <?php checked('1', $org_opt['kill_on_delete']); ?> /> <?php _e('Delete all PublishPress Series data from the database when deleting this plugin.', 'organize-series'); ?> </label>
  </span>
	<?php
}
?>
