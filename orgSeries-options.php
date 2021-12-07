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
		$settings_link = '<a href="'. ppseries_series_settings_page() .'">'.__("Settings", 'organize-series').'</a>';
		 array_unshift($links, $settings_link);
	}
	return $links;
 }

//add orgSeries to the options submenu and register settings
function orgseries_create_options() {
	global $orgseries;

	$page = add_menu_page(
		__('PublishPress Series Options', 'organize-series'),
		__('Series', 'organize-series'),
		'manage_publishpress_series',
		'orgseries_options_page',
		'orgseries_option_page',
		'dashicons-book-alt',
		68
	);

	add_action('admin_init', 'orgseries_options_init');
	add_action('admin_print_scripts-' . $page, 'orgseries_options_scripts');

}

function ppseries_register_temporary_taxonomy(){
    //let register taxonomy if not exist for migration purpose
		$object_type = apply_filters('orgseries_posttype_support', array('post'));
		$capabilities = array(
			'manage_terms' => 'manage_series',
			'edit_terms' => 'manage_series',
			'delete_terms' => 'manage_series',
			'assign_terms' => 'manage_series'
			);
		$labels = array(
			'name' => _x('Series', 'taxonomy general name', 'organize-series'),
			'singular_name' => _x('Series', 'taxonomy singular name', 'organize-series'),
			'search_items' => __('Search Series', 'organize-series'),
			'popular_items' => __('Popular Series', 'organize-series'),
			'all_items' => __('All Series', 'organize-series'),
			'edit_item' => __('Edit Series', 'organize-series'),
			'update_item' => __('Update Series', 'organize-series'),
			'add_new_item' => __('Add New Series', 'organize-series'),
			'new_item_name' => __('New Series Name', 'organize-series'),
			'menu_name' => __('Manage Series', 'organize-series'),
			'not_found' => __('No series found', 'organize-series')
			);
		$args = array(
			'update_count_callback' => '_os_update_post_term_count',
			'labels' => $labels,
			'rewrite' => array( 'slug' => 'series', 'with_front' => true ),
			'show_ui' => true,
			'capabilities' => $capabilities,
			'query_var' => 'series',
			);
		register_taxonomy( 'series', $object_type, $args );
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
	}elseif ( isset($_POST['migrate_series']) && (int)$_POST['migrate_series'] === 1 ) {

        global $wpdb;

        ppseries_register_temporary_taxonomy();

        $input = $orgseries->settings;


        $args = array(
            'hide_empty' => false,
            'taxonomy' => 'series'
        );
        $terms = get_terms($args);

        $count = 0;

        foreach ( $terms as $term ) {
            $count++;
            $update = $wpdb->update(
                $wpdb->prefix . 'term_taxonomy',
                [ 'taxonomy' => ppseries_get_series_slug() ],
                [ 'term_taxonomy_id' => $term->term_id ],
                [ '%s' ],
                [ '%d' ]
            );
        }
        $update['updated_output'] = '<div class="updated"><p>'. sprintf(__('%1$s series migrated to new taxonomy', 'organize-series'), $count) .'</p></div>';
        update_option('orgseries_update_message', $update['updated_output']);
        return $input;
	} else {
		$update['updated_output'] = '<div class="updated"><p>' . __('PublishPress Series Plugin Options have been updated','organize-series') . '</p></div>';
	}
	//toggles and paging info
	$newinput['auto_tag_toggle'] = isset($input['auto_tag_toggle']) && $input['auto_tag_toggle'] == 1 ? 1 : 0;
	$newinput['series_post_list_limit'] = trim(stripslashes($input['series_post_list_limit']));
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
	$newinput['series_firstpost_nav_custom_text'] = trim(stripslashes($input['series_firstpost_nav_custom_text']));
	$newinput['series_posts_orderby'] = trim(stripslashes($input['series_posts_orderby']));
	$newinput['series_posts_order'] = trim(stripslashes($input['series_posts_order']));
	$newinput['latest_series_before_template'] = trim(stripslashes($input['latest_series_before_template']));
	$newinput['latest_series_inner_template'] = trim(stripslashes($input['latest_series_inner_template']));
	$newinput['latest_series_after_template'] = trim(stripslashes($input['latest_series_after_template']));
	$newinput['series_post_list_position'] = trim(stripslashes($input['series_post_list_position']));
	$newinput['series_metabox_position'] = trim(stripslashes($input['series_metabox_position']));
	$newinput['series_navigation_box_position'] = trim(stripslashes($input['series_navigation_box_position']));
	$newinput['series_taxonomy_slug'] = ( isset($input['series_taxonomy_slug']) && !empty(trim($input['series_taxonomy_slug'])) ? $input['series_taxonomy_slug'] : 'series' );

    // overview page options
    $newinput['series_overview_page_layout'] = trim(stripslashes($input['series_overview_page_layout']));
    $newinput['series_overview_page_columns'] = (int) $input['series_overview_page_columns'];

	//series-icon related settings
	$newinput['series_icon_width_series_page'] = (int) $input['series_icon_width_series_page'];
	$newinput['series_icon_width_post_page'] = (int) $input['series_icon_width_post_page'];
	$newinput['series_icon_width_latest_series'] = (int) $input['series_icon_width_latest_series'];

    //we need to maintain series slug settings separately
    update_option('pp_series_taxonomy_slug', $newinput['series_taxonomy_slug']);

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

	add_settings_section('series_taxonomy_base_settings', 'URLs and Taxonomy', 'orgseries_taxonomy_base_section', 'orgseries_options_page');
	add_settings_field('series_taxonomy_base_core_fieldset', 'URLs and Taxonomy', 'series_taxonomy_base_core_fieldset', 'orgseries_options_page', 'series_taxonomy_base_settings');

    add_settings_section('series_overview_page_settings', 'Overview Page', 'orgseries_overview_page_section', 'orgseries_options_page');
    add_settings_field('series_overview_page_core_fieldset', 'Overview Page', 'series_overview_page_core_fieldset', 'orgseries_options_page', 'series_overview_page_settings');

	add_settings_section('series_uninstall_settings', 'Uninstall', 'orgseries_uninstall_section', 'orgseries_options_page');
	add_settings_field('series_uninstall_core_fieldset', 'Series uninstall', 'series_uninstall_core_fieldset', 'orgseries_options_page', 'series_uninstall_settings');


  add_filter( 'ppseries_admin_settings_tabs', 'ppseries_filter_admin_settings_tabs');
}


function ppseries_filter_admin_settings_tabs($settings_tabs){
  $settings_tabs['series_taxonomy_base_settings'] = __('URLs and Taxonomy', 'organize-series');
  $settings_tabs['series_uninstall_settings'] = __('Uninstall / Reset', 'organize-series');
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

				<h3 class="handle"><span><?php _e('Allowed Html', 'organize-series'); ?></span></h3>
					<div class="inside">
						<?php $html_list = '<div> <img> <span> <p> <hr> <br /> <ol> <ul> <li> <fieldset> <legend> <h1> <h2> <h3> <h4> <h5> <h6>';
						echo '<p><code>'. htmlentities ($html_list) .'</code></p>';
						?>
					</div>

				</p>
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
						<strong>%post_title_list_short%</strong><br />
						<em><?php _e('Is the location token for where the contents of the post list post templates will appear and use provided widget post short title.', 'organize-series'); ?></em><br /><br />
					<strong>%post_title%</strong><br />
						<em><?php _e('Will be replaced with the post title of a post in the series', 'organize-series'); ?></em><br /><br />
					<strong>%post_title_linked%</strong><br />
						<em><?php _e('Will be replaced with the post title of a post in the series linked to the page view of that post.', 'organize-series'); ?></em><br /><br />
					<strong>%previous_post%</strong><br />
						<em><?php _e('Will be replaced by the navigation link for the previous post in a series. The text will be whatever is included in the \'Custom Previous Post Navigation Text\' field. If that field is empty then the text will be the title of the post', 'organize-series'); ?></em><br /><br />
					<strong>%next_post%</strong><br />
						<em><?php _e('Will be replaced by the navigation link for the next post in a series. The text will be whatever is included in the \'Custom Next Post Navigation Text\' field. If that field is empty then the text will be the title of the post', 'organize-series'); ?></em><br /><br />
					<strong>%first_post%</strong><br />
						<em><?php _e('Will be replaced by the navigation link for the first post in a series. The text will be whatever is included in the \'Custom First Post Navigation Text\' field. If that field is empty then the text will be the title of the post', 'organize-series'); ?></em><br /><br />
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
				</span>
				</form>
				<div id="TBcontent" class="reset_dialog" style="display:none;">
					<p> <?php _e('Clicking Yes will reset the options to the defaults and you will lose all customizations. Or you can click cancel and return.', 'organize-series'); ?></p>
					<input type="submit" id="TBcancel" class="button" value="<?php esc_attr_e('No', 'organize-series'); ?>" />
					<input type="submit" id="TBsubmit" class="alignright button-primary" value="<?php esc_attr_e('Yes', 'organize-series'); ?>" />
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
	<?php
}

function orgseries_icon_section() {
	global $orgseries;
	?>
	<p><?php _e('This section is for the icons that show with your series. Note that you must use a token for the icon in the "Templates" settings.', 'organize-series'); ?></p>
	<?php
}

function orgseries_uninstall_section() {
	global $orgseries;
	?>
	<?php
}

function orgseries_taxonomy_base_section() {
	global $orgseries;
	?>
	<?php
}

function orgseries_overview_page_section() {
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
			<div id="topic-toc-settings-automation-core" class="" style="line-height:normal;border:unset;">
					<div class="inside" style="padding: 0;margin: 0;">


	<h1><?php _e('Display on single posts in a series', 'organize-series'); ?></h1>

          				<table class="form-table ppseries-settings-table">
            				<tbody>

								<tr valign="top"><th scope="row"><label for="auto_tag_toggle"><?php _e('Display Series Post List box?', 'organize-series'); ?></label></th>
									<td><input name="<?php echo $org_name;?>[auto_tag_toggle]" value="1" id="auto_tag_toggle" type="checkbox" <?php checked('1', $org_opt['auto_tag_toggle']); ?> /></td>
								</tr>

								<tr valign="top"><th scope="row"><label for="series_post_list_limit"><?php _e('Maximum number of items in Series Post List', 'organize-series'); ?></label></th>
									<td><input min="0" name="<?php echo $org_name;?>[series_post_list_limit]" value="<?php echo ( isset($org_opt['series_post_list_limit']) ? htmlspecialchars($org_opt['series_post_list_limit']) : ''); ?>" id="series_post_list_limit" type="number" /></td>
								</tr>

								<tr valign="top"><th scope="row"><label for="auto_tag_nav_toggle"><?php _e('Display Series Navigation links ?', 'organize-series'); ?></label></th>
									<td><input name="<?php echo $org_name; ?>[auto_tag_nav_toggle]" id="auto_tag_nav_toggle" type="checkbox" value="1" <?php checked('1', $org_opt['auto_tag_nav_toggle']); ?> /></td>
								</tr>

								<tr valign="top"><th scope="row"><label for="auto_tag_seriesmeta_toggle"><?php _e('Display Series Meta information?', 'organize-series'); ?></label></th>
									<td><input name="<?php echo $org_name; ?>[auto_tag_seriesmeta_toggle]" id="auto_tag_seriesmeta_toggle" type="checkbox" value="1" <?php checked('1', $org_opt['auto_tag_seriesmeta_toggle']); ?> /></td>
								</tr>

								<tr valign="top"><th scope="row"><label for="custom_css"><?php _e('Use PublishPress Series CSS styles?', 'organize-series'); ?></label></th>
									<td><input name="<?php echo $org_name; ?>[custom_css]" id="custom_css" type="checkbox" value="1" <?php checked('1', $org_opt['custom_css']); ?> /></td>
								</tr>

								<tr valign="top"><th scope="row"><label for=""><?php _e('Style options', 'organize-series'); ?></label></th>
									<td>
										<label><input name="<?php echo $org_name; ?>[series_css_tougle]" class="css_style" id="css_default" type="radio" value="default" <?php checked('default', $series_css_tougle); ?> <?php disabled('0', $org_opt['custom_css']) ?> /><?php _e('Use default style', 'organize-series'); ?> </label><br />
                                        <label><input name="<?php echo $org_name; ?>[series_css_tougle]" class="css_style" id="css_box" type="radio" value="box" <?php checked('box', $series_css_tougle); ?> <?php disabled('0', $org_opt['custom_css']) ?> /><?php _e('Use box style', 'organize-series'); ?> </label><br />
										<label><input name="<?php echo $org_name; ?>[series_css_tougle]" class="css_style" id="css_dark" type="radio" value="dark" <?php checked('dark', $series_css_tougle); ?> <?php disabled('0', $org_opt['custom_css']) ?> /><?php _e('Use dark style', 'organize-series'); ?> </label><br />
                                        <label><input name="<?php echo $org_name; ?>[series_css_tougle]" class="css_style" id="css_light" type="radio" value="light" <?php checked('light', $series_css_tougle); ?> <?php disabled('0', $org_opt['custom_css']) ?> /><?php _e('Use light style', 'organize-series'); ?> </label>
									</td>
								</tr>


								<tr valign="top"><th scope="row" colspan="2"><h1><?php _e('Display on Series Table of Contents screens', 'organize-series'); ?></h1></th></tr>

								<tr valign="top"><th scope="row"><label for="series_toc_url"><?php _e('Series Table of Contents URL:', 'organize-series'); ?></label></th>
									<td>
                                        <span id="toc-home-url"><?php bloginfo('url') ?>/</span><input type="text" name="<?php echo $org_name; ?>[series_toc_url]" id="series_toc_url" value="<?php echo htmlspecialchars($org_opt['series_toc_url']); ?>" />
                                        <button onclick="gotoTOCUrl(event)" class="button">view page</button>
                                    </td>
								</tr>
                                <script>
                                    function gotoTOCUrl(e){
                                        e.preventDefault();
                                        var toc_url = document.getElementById("toc-home-url").innerHTML + document.getElementById("series_toc_url").value;
                                        window.open(toc_url);
                                    }
                                </script>

								<tr valign="top"><th scope="row"><label for="series_perp_toc"><?php _e('Series Per Page:', 'organize-series'); ?></label></th>
									<td><input type="number" name="<?php echo $org_name; ?>[series_perp_toc]" value="<?php echo (int) ($series_perp_toc); ?>" /></td>
								</tr>

								<tr valign="top"><th scope="row"><label for="series_toc_title"><?php _e('Series Table of Contents Title:', 'organize-series'); ?></label></th>
									<td><input type="text" id="series_toc_title" name="<?php echo $org_name; ?>[series_toc_title]" value="<?php echo htmlspecialchars($org_opt['series_toc_title']); ?>"/></td>
								</tr>

								<tr valign="top"><th scope="row"><label for=""><?php _e('Order series by:', 'organize-series'); ?></label></th>
									<td>
										<label><input name="<?php echo $org_name; ?>[series_posts_orderby]" id="series_posts_orderby_part" type="radio" value="meta_value" <?php checked('meta_value', $org_opt['series_posts_orderby']); ?> /><?php _e('Series part', 'organize-series'); ?></label> &nbsp;
										<label><input name="<?php echo $org_name; ?>[series_posts_orderby]" id="series_posts_orderby_date" type="radio" value="post_date" <?php checked('post_date', $org_opt['series_posts_orderby']); ?> /><?php _e('Order by date', 'organize-series'); ?></label>
									</td>
								</tr>

								<tr valign="top"><th scope="row"><label for=""><?php _e('Series order method', 'organize-series'); ?></label></th>
									<td>
										<label><input name="<?php echo $org_name; ?>[series_posts_order]" id="series_posts_order_ASC" type="radio" value="ASC" <?php checked('ASC', $org_opt['series_posts_order']); ?> /><?php _e('Ascending', 'organize-series'); ?></label>&nbsp;
										<label><input name="<?php echo $org_name; ?>[series_posts_order]" id="series_posts_order_DESC" type="radio" value="DESC" <?php checked('DESC', $org_opt['series_posts_order']); ?> /><?php _e('Descending', 'organize-series'); ?></label></td>
								</tr>

            					</tbody>
        					</table>


					</div>
				</div>
	<?php
}

function series_templates_core_fieldset() {
	global $orgseries;
	$org_opt = $orgseries->settings;
	$org_name = 'org_series_options';

	$post_box_locations = [
		'default'=> __('As in Template', 'organize-series'),
		'top' 	 => __('Top', 'organize-series'),
		'bottom' => __('Bottom', 'organize-series'),
	];
	?>
			<div id="topic-toc-settings-series-template-core" style="line-height:normal;">
				<div class="inside" style="padding: 0;margin: 0;">

					<table class="form-table ppseries-settings-table">
            			<tbody>
							<tr valign="top">
    							<th scope="row" colspan="2" style="padding-top: 0;">
        							<h1>
            							<?php _e('Series Post List Box', 'organize-series'); ?>
        							</h1>
									<p class="description"><?php _e('This display is shown at the top of all posts in a series.', 'organize-series'); ?></p>
    							</th>
							</tr>
							<tr valign="top"><th scope="row"><label for="series_post_list_template"><?php _e('Series Post List', 'organize-series'); ?></label></th>
								<td><textarea name="<?php echo $org_name; ?>[series_post_list_template]" id="series_post_list_template" class="ppseries-textarea ppseries-full-width"><?php echo esc_html(htmlspecialchars(stripslashes($org_opt['series_post_list_template']))); ?></textarea>
								</td>
							</tr>

								<tr valign="top"><th scope="row"><label for="series_post_list_position"><?php _e('Series Post List box Location', 'organize-series'); ?></label></th>
									<td>
										<select name="<?php echo $org_name;?>[series_post_list_position]" id="series_post_list_position">
										<?php
										foreach($post_box_locations as $key => $label){
											$selected = ( isset($org_opt['series_post_list_position']) && $org_opt['series_post_list_position'] === $key ) ? 'selected="selected"' : '';
											echo '<option value="'.$key.'" '.$selected.'>'.$label.'</option>';

										}
										?>
										</select>
									</td>
								</tr>

							<tr valign="top"><th scope="row"><label for="series_post_list_post_template"><?php _e('Series Post List Post Title', 'organize-series'); ?></label></th>
								<td><input type="text" name="<?php echo $org_name; ?>[series_post_list_post_template]" id="series_post_list_post_template" value="<?php echo esc_attr(htmlspecialchars($org_opt['series_post_list_post_template'])); ?>" class="ppseries-full-width">
								</td>
							</tr>
							<?php do_action('plist_ptitle_template_unpublished') ?>

							<tr valign="top"><th scope="row"><label for="series_post_list_currentpost_template"><?php _e('Series Post List Current Post Title', 'organize-series'); ?></label></th>
								<td><input type="text" name="<?php echo $org_name; ?>[series_post_list_currentpost_template]" id="series_post_list_currentpost_template" value="<?php echo esc_attr(htmlspecialchars($org_opt['series_post_list_currentpost_template'])); ?>" class="ppseries-full-width">
								</td>
							</tr>

							<tr valign="top">
    							<th scope="row" colspan="2">
        							<h1>
            							<?php _e('Series Meta Box', 'organize-series'); ?>
        							</h1>
									<p class="description"><?php _e('This display is shown at the top of all posts in a series.', 'organize-series'); ?></p>
    							</th>
							</tr>

							<tr valign="top"><th scope="row"><label for="series_meta_template"><?php _e('Series Meta:', 'organize-series'); ?></label></th>
								<td><textarea name="<?php echo $org_name; ?>[series_meta_template]" id="series_meta_template" class="ppseries-textarea ppseries-full-width"><?php echo esc_html(htmlspecialchars(stripslashes($org_opt['series_meta_template']))); ?></textarea>

								</td>
							</tr>

								<tr valign="top"><th scope="row"><label for="series_metabox_position"><?php _e('Series Metabox Location', 'organize-series'); ?></label></th>
									<td>
										<select name="<?php echo $org_name;?>[series_metabox_position]" id="series_metabox_position">
										<?php
										foreach($post_box_locations as $key => $label){
											$selected = ( isset($org_opt['series_metabox_position']) && $org_opt['series_metabox_position'] === $key ) ? 'selected="selected"' : '';
											echo '<option value="'.$key.'" '.$selected.'>'.$label.'</option>';

										}
										?>
										</select>
									</td>
								</tr>

							<tr valign="top"><th scope="row"><label for="series_meta_excerpt_template"><?php _e('Series Meta (with excerpts):', 'organize-series'); ?></label></th>
								<td>
									<textarea name="<?php echo $org_name; ?>[series_meta_excerpt_template]" id="series_meta_excerpt_template" class="ppseries-textarea ppseries-full-width"><?php echo esc_html(htmlspecialchars(stripslashes($org_opt['series_meta_excerpt_template']))); ?></textarea>
								</td>
							</tr>

							<tr valign="top">
    							<th scope="row" colspan="2">
        							<h1>
            							<?php _e('Series Navigation Box', 'organize-series'); ?>
        							</h1>
									<p class="description"><?php _e('This display is shown at the bottom of all posts in a series.', 'organize-series'); ?></p>
    							</th>
							</tr>

							<tr valign="top"><th scope="row"><label for="series_post_nav_template"><?php _e('Series Post Navigation:', 'organize-series'); ?></label></th>
								<td><textarea name="<?php echo $org_name; ?>[series_post_nav_template]" id="series_post_nav_template" class="ppseries-textarea ppseries-full-width"><?php echo esc_html(htmlspecialchars(stripslashes($org_opt['series_post_nav_template']))); ?></textarea>
								</td>
							</tr>

								<tr valign="top"><th scope="row"><label for="series_navigation_box_position"><?php _e('Series Post Navigation Location', 'organize-series'); ?></label></th>
									<td>
										<select name="<?php echo $org_name;?>[series_navigation_box_position]" id="series_navigation_box_position">
										<?php
										foreach($post_box_locations as $key => $label){
											$selected = ( isset($org_opt['series_navigation_box_position']) && $org_opt['series_navigation_box_position'] === $key ) ? 'selected="selected"' : '';
											echo '<option value="'.$key.'" '.$selected.'>'.$label.'</option>';

										}
										?>
										</select>
									</td>
								</tr>

							<tr valign="top"><th scope="row"><label for="series_nextpost_nav_custom_text"><?php _e('Next Post', 'organize-series'); ?></label></th>
								<td><input type="text" name="<?php echo $org_name; ?>[series_nextpost_nav_custom_text]" id="series_nextpost_nav_custom_text" value="<?php echo esc_attr(htmlspecialchars($org_opt['series_nextpost_nav_custom_text'])); ?>" class="ppseries-full-width">
								</td>
							</tr>

							<tr valign="top"><th scope="row"><label for="series_prevpost_nav_custom_text"><?php _e('Previous Post', 'organize-series'); ?></label></th>
								<td><input type="text" name="<?php echo $org_name; ?>[series_prevpost_nav_custom_text]" id="series_prevpost_nav_custom_text" value="<?php echo esc_attr(htmlspecialchars($org_opt['series_prevpost_nav_custom_text'])); ?>" class="ppseries-full-width">
								</td>
							</tr>

							<tr valign="top"><th scope="row"><label for="series_firstpost_nav_custom_text"><?php _e('First Post', 'organize-series'); ?></label></th>
								<td><input type="text" name="<?php echo $org_name; ?>[series_firstpost_nav_custom_text]" id="series_firstpost_nav_custom_text" value="<?php echo (isset($org_opt['series_firstpost_nav_custom_text'])) ? esc_attr(htmlspecialchars($org_opt['series_firstpost_nav_custom_text'])) : 'Series Home'; ?>" class="ppseries-full-width">
								</td>
							</tr>

							<tr valign="top">
    							<th scope="row" colspan="2">
        							<h1>
            							<?php _e('Latest Series', 'organize-series'); ?>
        							</h1>
									<p class="description"><?php _e('This display is used by the "Series Table of Contents" widget.', 'organize-series'); ?></p>
    							</th>
							</tr>

							<tr valign="top"><th scope="row"><label for="latest_series_before_template"><?php _e('Latest Series (tags before):', 'organize-series'); ?></label></th>
								<td><input type="text" name="<?php echo $org_name; ?>[latest_series_before_template]" id="latest_series_before_template" value="<?php echo esc_attr(htmlspecialchars($org_opt['latest_series_before_template'])); ?>" class="ppseries-full-width">
								</td>
							</tr>

							<tr valign="top"><th scope="row"><label for="latest_series_inner_template"><?php _e('Latest Series (inner tags):', 'organize-series'); ?></label></th>
								<td><input type="text" name="<?php echo $org_name; ?>[latest_series_inner_template]" id="latest_series_inner_template" value="<?php echo esc_attr(htmlspecialchars($org_opt['latest_series_inner_template'])); ?>" class="ppseries-full-width">
								</td>
							</tr>

							<tr valign="top"><th scope="row"><label for="latest_series_after_template"><?php _e('Latest Series (tags after):', 'organize-series'); ?></label></th>
								<td><input type="text" name="<?php echo $org_name; ?>[latest_series_after_template]" id="latest_series_after_template" value="<?php echo esc_attr(htmlspecialchars($org_opt['latest_series_after_template'])); ?>" class="ppseries-full-width">
								</td>
							</tr>

                            <tr valign="top"><th scope="row"><label for="series_table_of_contents_box_template"><?php _e('Series Table of Contents', 'organize-series'); ?></label></th>
								<td><textarea name="<?php echo $org_name; ?>[series_table_of_contents_box_template]" id="series_table_of_contents_box_template" class="ppseries-textarea ppseries-full-width"><?php echo esc_html(htmlspecialchars(stripslashes($org_opt['series_table_of_contents_box_template']))); ?></textarea>
                                <small><?php _e('This display is used by the "Series Table of Contents". To find the URL for this display, go the "Display" tab and then "Series Table of Contents URL".', 'organize-series'); ?></small>
								</td>
							</tr>

						</tbody>
					</table>

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
		<div id="topic-toc-settings-icon-core" class="" style="line-height:normal;">
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

function series_taxonomy_base_core_fieldset() {
	global $orgseries;
	$org_opt = $orgseries->settings;
	$org_name = 'org_series_options';
	?>
	<table class="form-table ppseries-settings-table">
    	<tbody>
            <tr valign="top"><th scope="row"><label for="series_taxonomy_slug"><?php _e('Series Taxonomy:', 'organize-series'); ?></label></th>
                <td>
                    <input type="text" id="series_taxonomy_slug" name="<?php echo $org_name; ?>[series_taxonomy_slug]" value="<?php echo htmlspecialchars($org_opt['series_taxonomy_slug']); ?>"/>
                    <br />
                    <small><?php _e('This feature allows you to create a new taxonomy for this plugin to use if you don\'t want to use the default "Series" taxonomy.', 'organize-series'); ?></small>
                </td>
            </tr>
            <?php if( $org_opt['series_taxonomy_slug'] !== 'series'){ ?>
			<tr valign="top">
            	<th scope="row"><label>
                	    <?php _e('Migrate', 'organize-series'); ?>
                	</label>
            	</th>
            	<td>
                    <button type="submit" class="button" name="migrate_series" value="1"><?php _e('Migrate series to new taxonomy', 'organize-series'); ?></button>
                    <div><br />
                    <font color="red"><?php _e('Please use with caution. Running this process will delete all the terms from the current taxonomy and migrate them to a new taxonomy.', 'organize-series'); ?></font>
                    </div>
                    <span class="spinner ppseries-spinner"></span>
                </td>
        	</tr>
            <?php } ?>

            <tr valign="top"><th scope="row"><label for="series_custom_base"><?php _e('Series Custom Base:', 'organize-series'); ?></label></th>
                <td><input type="text" name="<?php echo $org_name; ?>[series_custom_base]" id="series_custom_base" value="<?php echo htmlspecialchars($org_opt['series_custom_base']); ?>" /> <br />
                    <small><?php _e('This text will be part of the URL for all Series Overview pages.', 'organize-series'); ?></small>
                </td>

            </tr>

    </tbody>
	</table>	<?php
}

function series_overview_page_core_fieldset() {
	global $orgseries;
	$org_opt               = $orgseries->settings;
	$org_name              = 'org_series_options';
    $overview_page_layouts = [
		'default' => __('Default', 'organize-series'),
		'grid' 	  => __('Grid', 'organize-series'),
		'list'    => __('List', 'organize-series'),
	];
    ?>
    <p><?php _e('Choose the design for the taxonomy page where a Series is displayed.', 'organize-series'); ?></p>
    <table class="form-table ppseries-settings-table">
    	<tbody>
            <tr valign="top">
                <th scope="row">
                    <label for="series_overview_page_layout"><?php _e('Layout:', 'organize-series'); ?></label>
                </th>
                <td>
                    <select name="<?php echo $org_name;?>[series_overview_page_layout]" id="series_overview_page_layout">
                    <?php
                    foreach($overview_page_layouts as $key => $label){
                        $selected = ( isset($org_opt['series_overview_page_layout']) && $org_opt['series_overview_page_layout'] === $key ) ? 'selected="selected"' : '';
                        echo '<option value="'.$key.'" '.$selected.'>'.$label.'</option>';

                    }
                    ?>
                    </select>
                    <br/>
                    <small><?php _e('Please note: choosing a layout different to "Default" will override the taxonomy template from your theme.', 'organize-series'); ?></small>
                </td>
            </tr>
            <tr valign="top" class="pps-row-columns"<?php echo ($org_opt['series_overview_page_layout'] === 'grid') ? '' : ' style="display:none;"' ?>>
                <th scope="row">
                    <label for="series_overview_page_columns"><?php _e('Columns:', 'organize-series'); ?></label>
                </th>
                <td>
                    <input min="0" max="6" name="<?php echo $org_name;?>[series_overview_page_columns]" value="<?php echo ( isset($org_opt['series_overview_page_columns']) ? htmlspecialchars($org_opt['series_overview_page_columns']) : ''); ?>" id="series_overview_page_columns" type="number" />
                </td>
            </tr>
        </tbody>
	</table>
    <?php
}

function series_uninstall_core_fieldset() {
	global $orgseries;
	$org_opt = $orgseries->settings;
	$org_name = 'org_series_options';
	?>
	<table class="form-table ppseries-settings-table">
    	<tbody>
        	<tr valign="top">
            	<th scope="row"><label for="kill_on_delete">
                	    <?php _e('Series Settings', 'organize-series'); ?>
                	</label>
                	<br /><small style="color: #646970;">
                    	<?php _e('Delete all PublishPress Series data from the database when deleting this plugin.', 'organize-series'); ?></label>
                	</small>
            	</th>
            	<td><input name="<?php echo $org_name; ?>[kill_on_delete]" id="kill_on_delete" type="checkbox" value="1" <?php checked('1', $org_opt['kill_on_delete']); ?> /></td>
        	</tr>

			<tr valign="top">
            	<th scope="row"><label>
                	    <?php _e('Reset settings', 'organize-series'); ?>
                	</label>
            	</th>
            	<td><input type="submit" class="button" name="option_reset" value="<?php _e('Reset options to default', 'organize-series'); ?>" /></td>
        	</tr>

    </tbody>
	</table>	<?php
}
?>
