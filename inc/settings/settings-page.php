<?php
/**
 * Settings page rendering function.
 *
 * Extracted from orgSeries-options.php during settings page modularization.
 *
 * @package Publishpress Series
 */

function orgseries_option_page() {
	global $orgseries, $wp_rewrite;
	$wp_rewrite->flush_rules();
	$org_opt = $orgseries->settings;
	$org_update_message = get_option('orgseries_update_message');
	?>
	<div class="wrap">
		<div class="icon32" id="icon-options-general"><br /></div>
		<h2><?php esc_html_e('PublishPress Series Plugin Options', 'organize-series'); ?></h2>
	<?php
    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo $org_update_message;
	update_option('orgseries_update_message','');
	?>

  <h2 class="nav-tab-wrapper ppseries-settings-tab">
  <?php
    $settings_tabs = ppseries_admin_settings_tabs();
    foreach($settings_tabs as $settings_tab_key => $settings_tab_label){
      /*if(apply_filters('ppseries_settings_'.$settings_tab_key.'_tabbed', false)){
        $tabbled_class = 'series-tab-content';
      }else{
        $tabbled_class =  !defined('SERIES_PRO_VERSION') ? 'series-tab-content' : '';
      }*/
      $tabbled_class = 'series-tab-content';
      // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
      echo '<a id="'. esc_attr($settings_tab_key) .'-series-tab" class="nav-tab '. esc_attr($tabbled_class) .'" href="#'. $settings_tab_key .'">'.$settings_tab_label.'</a>';
    }
  ?>
  </h2>


	<div id="poststuff" class="metabox-holder has-right-sidebar ppseries-settings-layout">

        <div id="side-info-column">

		<div class="inner-sidebar">
			<div id="side-sortables" class="meta-box-sortables ui-sortable">
                <div id="token-legend" class="postbox_">

                    <div id="ppseries-token-sidebar" class="ppseries-settings-tab-content series_post_list_box_settings-series-sidebar series_post_details_settings-series-sidebar series_navigation_settings-series-sidebar series_legacy_settings-series-sidebar postbox">
                            
                        <h3 class="handle"><span><?php esc_html_e('Allowed Html', 'organize-series'); ?></span></h3>
                        <div class="inside">
                            <?php $html_list = '<div> <img> <span> <p> <hr> <br /> <ol> <ul> <li> <fieldset> <legend> <h1> <h2> <h3> <h4> <h5> <h6>';
                            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                            echo '<p><code>'. htmlentities ($html_list) .'</code></p>';
                            ?>
                        </div>

                    </p>
                    <h3 class="handle"><span><?php esc_html_e('Overview', 'organize-series'); ?></span></h3>
                    <div class="inside">
                        <p><small><?php esc_html_e('The following is a legend of the tokens that are available for use in the custom template fields. These will be replaced with the appropriate values when the plugin runs.', 'organize-series'); ?></small></p>
                        
                        <span class="pp-tooltips-library" data-toggle="tooltip" data-placement="left">
                            <strong>%series_icon%</strong>
                            <span class="tooltip-text">
                                <span><?php esc_html_e('This will be replaced with the series icon for a series.', 'organize-series'); ?></span>
                                <i></i>
                            </span>
                        </span><br /><br />
                        
                        <span class="pp-tooltips-library" data-toggle="tooltip" data-placement="left">
                            <strong>%series_icon_linked%</strong>
                            <span class="tooltip-text">
                                <span><?php esc_html_e('Same as %series_icon% except that the series icon will be linked to the series page','organize-series'); ?></span>
                                <i></i>
                            </span>
                        </span><br /><br />
                        
                        <span class="pp-tooltips-library" data-toggle="tooltip" data-placement="left">
                            <strong>%series_list%</strong>
                            <span class="tooltip-text">
                                <span><?php esc_html_e('This token is for use with the orgSeries widget only - it references where you want the list of series titles to be inserted and requires that the template for each series title be also set.', 'organize-series'); ?></span>
                                <i></i>
                            </span>
                        </span><br /><br />
                        
                        <span class="pp-tooltips-library" data-toggle="tooltip" data-placement="left">
                            <strong>%series_title%</strong>
                            <span class="tooltip-text">
                                <span><?php esc_html_e('This will be replaced with the title of a series', 'organize-series'); ?></span>
                                <i></i>
                            </span>
                        </span><br /><br />
                        
                        <span class="pp-tooltips-library" data-toggle="tooltip" data-placement="left">
                            <strong>%series_title_linked%</strong>
                            <span class="tooltip-text">
                                <span><?php esc_html_e('Same as %series_title% except that it will also be linked to the series page', 'organize-series'); ?></span>
                                <i></i>
                            </span>
                        </span><br /><br />
                        
                        <span class="pp-tooltips-library" data-toggle="tooltip" data-placement="left">
                            <strong>%post_title_list%</strong>
                            <span class="tooltip-text">
                                <span><?php esc_html_e('Is the location token for where the contents of the post list post templates will appear.', 'organize-series'); ?></span>
                                <i></i>
                            </span>
                        </span><br /><br />
                        
                        <span class="pp-tooltips-library" data-toggle="tooltip" data-placement="left">
                            <strong>%post_title_list_short%</strong>
                            <span class="tooltip-text">
                                <span><?php esc_html_e('Is the location token for where the contents of the post list post templates will appear and use provided widget post short title.', 'organize-series'); ?></span>
                                <i></i>
                            </span>
                        </span><br /><br />
                        
                        <span class="pp-tooltips-library" data-toggle="tooltip" data-placement="left">
                            <strong>%post_title%</strong>
                            <span class="tooltip-text">
                                <span><?php esc_html_e('Will be replaced with the post title of a post in the series', 'organize-series'); ?></span>
                                <i></i>
                            </span>
                        </span><br /><br />
                        
                        <span class="pp-tooltips-library" data-toggle="tooltip" data-placement="left">
                            <strong>%post_title_linked%</strong>
                            <span class="tooltip-text">
                                <span><?php esc_html_e('Will be replaced with the post title of a post in the series linked to the page view of that post.', 'organize-series'); ?></span>
                                <i></i>
                            </span>
                        </span><br /><br />
                        
                        <span class="pp-tooltips-library" data-toggle="tooltip" data-placement="left">
                            <strong>%post_title_short%</strong>
                            <span class="tooltip-text">
                                <span><?php esc_html_e('Will be replaced with the post title short of a post in the series', 'organize-series'); ?></span>
                                <i></i>
                            </span>
                        </span><br /><br />
                        
                        <span class="pp-tooltips-library" data-toggle="tooltip" data-placement="left">
                            <strong>%post_title_short_linked%</strong>
                            <span class="tooltip-text">
                                <span><?php esc_html_e('Will be replaced with the post title short of a post in the series linked to the page view of that post.', 'organize-series'); ?></span>
                                <i></i>
                            </span>
                        </span><br /><br />
                        
                        <span class="pp-tooltips-library" data-toggle="tooltip" data-placement="left">
                            <strong>%previous_post%</strong>
                            <span class="tooltip-text">
                                <span><?php esc_html_e('Will be replaced by the navigation link for the previous post in a series. The text will be whatever is included in the \'Custom Previous Post Navigation Text\' field. If that field is empty then the text will be the title of the post', 'organize-series'); ?></span>
                                <i></i>
                            </span>
                        </span><br /><br />
                        
                        <span class="pp-tooltips-library" data-toggle="tooltip" data-placement="left">
                            <strong>%next_post%</strong>
                            <span class="tooltip-text">
                                <span><?php esc_html_e('Will be replaced by the navigation link for the next post in a series. The text will be whatever is included in the \'Custom Next Post Navigation Text\' field. If that field is empty then the text will be the title of the post', 'organize-series'); ?></span>
                                <i></i>
                            </span>
                        </span><br /><br />
                        
                        <span class="pp-tooltips-library" data-toggle="tooltip" data-placement="left">
                            <strong>%first_post%</strong>
                            <span class="tooltip-text">
                                <span><?php esc_html_e('Will be replaced by the navigation link for the first post in a series. The text will be whatever is included in the \'Custom First Post Navigation Text\' field. If that field is empty then the text will be the title of the post', 'organize-series'); ?></span>
                                <i></i>
                            </span>
                        </span><br /><br />
                        <span class="pp-tooltips-library" data-toggle="tooltip" data-placement="left">
                            <strong>%postcontent%</strong>
                            <span class="tooltip-text">
                                <span><?php esc_html_e('Use this tag either before or after the rest of the template code.  It will indicate where you want the content of a post to display.', 'organize-series'); ?></span>
                                <i></i>
                            </span>
                        </span><br /><br />
                        
                        <span class="pp-tooltips-library" data-toggle="tooltip" data-placement="left">
                            <strong>%series_part%</strong>
                            <span class="tooltip-text">
                                <span><?php esc_html_e('Will display what part of a series the post is', 'organize-series'); ?></span>
                                <i></i>
                            </span>
                        </span><br /><br />
                        
                        <span class="pp-tooltips-library" data-toggle="tooltip" data-placement="left">
                            <strong>%total_posts_in_series%</strong>
                            <span class="tooltip-text">
                                <span><?php esc_html_e('Will display the total number of posts in a series', 'organize-series'); ?></span>
                                <i></i>
                            </span>
                        </span><br /><br />
                        
                        <span class="pp-tooltips-library" data-toggle="tooltip" data-placement="left">
                            <strong>%series_description%</strong>
                            <span class="tooltip-text">
                                <span><?php esc_html_e('Will display the description for the series', 'organize-series'); ?></span>
                                <i></i>
                            </span>
                        </span><br /><br />

						<!-- PRO Features -->
                        
                        <span class="pp-tooltips-library" data-toggle="tooltip" data-placement="left">
                            <strong>%series_slug%</strong> <?	if (!pp_series_is_pro_active()) { ?> <span class="ppseries-pro-badge">PRO</span> <? } ?>
                            <span class="tooltip-text">
                                <span><?php esc_html_e('Will output the slug of the series', 'organize-series'); ?></span>
                                <i></i>
                            </span>
                        </span><br /><br />
                        
                        <span class="pp-tooltips-library" data-toggle="tooltip" data-placement="left">
                            <strong>%series_id%</strong> <?	if (!pp_series_is_pro_active()) { ?> <span class="ppseries-pro-badge">PRO</span> <? } ?>
                            <span class="tooltip-text">
                                <span><?php esc_html_e('Will output the ID of the series', 'organize-series'); ?></span>
                                <i></i>
                            </span>
                        </span><br /><br />
                        
                        <span class="pp-tooltips-library" data-toggle="tooltip" data-placement="left">
                            <strong>%post_author%</strong> <?	if (!pp_series_is_pro_active()) { ?> <span class="ppseries-pro-badge">PRO</span> <? } ?>
                            <span class="tooltip-text">
                                <span><?php esc_html_e('Will output the author of the post', 'organize-series'); ?></span>
                                <i></i>
                            </span>
                        </span><br /><br />
                        
                        <span class="pp-tooltips-library" data-toggle="tooltip" data-placement="left">
                            <strong>%post_thumbnail%</strong> <?	if (!pp_series_is_pro_active()) { ?> <span class="ppseries-pro-badge">PRO</span> <? } ?>
                            <span class="tooltip-text">
                                <span><?php esc_html_e('If the post has a feature-image then that image will be displayed', 'organize-series'); ?></span>
                                <i></i>
                            </span>
                        </span><br /><br />
                        
                        <span class="pp-tooltips-library" data-toggle="tooltip" data-placement="left">
                            <strong>%post_date%</strong> <?	if (!pp_series_is_pro_active()) { ?> <span class="ppseries-pro-badge">PRO</span> <? } ?>
                            <span class="tooltip-text">
                                <span><?php esc_html_e('The date that a post was published', 'organize-series'); ?></span>
                                <i></i>
                            </span>
                        </span><br /><br />
                        
                        <span class="pp-tooltips-library" data-toggle="tooltip" data-placement="left">
                            <strong>%unpublished_post_title%</strong> <?	if (!pp_series_is_pro_active()) { ?> <span class="ppseries-pro-badge">PRO</span> <? } ?>
                            <span class="tooltip-text">
                                <span><?php esc_html_e('Will be replaced with the unpublished post title of a post in the series', 'organize-series'); ?></span>
                                <i></i>
                            </span>
                        </span><br /><br />
                        
                        <span class="pp-tooltips-library" data-toggle="tooltip" data-placement="left">
                            <strong>%total_posts_in_series_with_unpub%</strong> <?	if (!pp_series_is_pro_active()) { ?> <span class="ppseries-pro-badge">PRO</span> <? } ?>
                            <span class="tooltip-text">
                                <span><?php esc_html_e('Will display the total number of published and unpublished posts in a series', 'organize-series'); ?></span>
                                <i></i>
                            </span>
                        </span><br /><br />

                        <?php do_action('orgseries_token_description'); ?>
                            <?php do_action('ppseries_licence_key_form'); ?>
                        </div>

                    </div>

                    <div class="ppseries-settings-tab-content series_post_list_box_settings-series-sidebar series_post_details_settings-series-sidebar series_navigation_settings-series-sidebar series_taxonomy_base_settings-series-sidebar series_group_settings-series-sidebar series_metabox_settings-series-sidebar series_legacy_settings-series-sidebar series_uninstall_settings-series-sidebar series_addon_settings-series-sidebar series_license_settings-series-sidebar series_cpt_settings-series-sidebar">
                        <?php do_action('publishpress_series_admin_after_sidebar'); ?>
                    </div>
                    
                </div>
            </div>
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
                wp_nonce_field('publishpress_series_settings_nonce_action', 'publishpress_series_settings_nonce_field');
				//$submit_text = __('Do you really want to reset to default options (all your custom changes will be lost)?', 'organize-series');
				//$script_text = "javascript:return confirm('".$submit_text."')"
				?>
				<span class="submit">
					<input type="hidden" name="org_series_options[updated_output]" value="" />
					<input type="hidden" name="org_series_options[reset_option]" class="reset_option" value="" />
					<input type="submit" class="button-primary" name="update_orgseries" value="<?php esc_attr_e('Update Options', 'organize-series'); ?>" />
				</span>
				</form>
				<div id="TBcontent" class="reset_dialog" style="display:none;">
					<p> <?php esc_html_e('Clicking Yes will reset the options to the defaults and you will lose all customizations. Or you can click cancel and return.', 'organize-series'); ?></p>
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
