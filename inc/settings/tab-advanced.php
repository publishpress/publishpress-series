<?php
/**
 * Advanced (Uninstall) tab - section and fieldset.
 *
 * Extracted from orgSeries-options.php during settings page modularization.
 *
 * @package Publishpress Series
 */

add_settings_section('series_uninstall_settings', 'Uninstall', 'orgseries_uninstall_section', 'orgseries_options_page');
add_settings_field('series_uninstall_core_fieldset', 'Series uninstall', 'series_uninstall_core_fieldset', 'orgseries_options_page', 'series_uninstall_settings');

function orgseries_uninstall_section() {
	global $orgseries;
	?>
	<p class="description"><?php esc_html_e('Please change these settings carefully as they make significant changes to PublishPress Series.', 'organize-series'); ?></p>
	<?php
}

function series_uninstall_core_fieldset() {
	global $orgseries;
	$org_opt = $orgseries->settings;
	$org_name = 'org_series_options';
	?>
	<table class="form-table ppseries-settings-table">
    	<tbody>

            <?php do_action('pp_series_advanced_tab_top'); ?>

            <?php do_action('pp_series_advanced_tab_middle'); ?>

        	<tr valign="top">
            	<th scope="row"><label for="kill_on_delete">
                	    <?php esc_html_e('Series Settings', 'organize-series'); ?>
                	</label>
            	</th>
            	<td>
                    <label>
                        <input name="<?php echo esc_attr($org_name); ?>[kill_on_delete]" id="kill_on_delete" type="checkbox" value="1" <?php checked('1', isset($org_opt['kill_on_delete']) ? $org_opt['kill_on_delete'] : ''); ?> />
                    	<span class="description"><?php esc_html_e('Delete all PublishPress Series data from the database when deleting this plugin.', 'organize-series'); ?></span>
                	</label>
                </td>
        	</tr>

        	<tr valign="top">
            	<th scope="row"><label for="kill_on_delete">
                	    <?php esc_html_e('Series Upgrade', 'organize-series'); ?>
                	</label>
            	</th>

            	<td>
					<a class="button" href="<?php echo esc_url(admin_url('admin.php?page=orgseries_options_page&series_action=multiple-series-support&nonce='. wp_create_nonce('multiple-series-support-upgrade'))); ?>"><?php esc_html_e('Run Upgrade Task', 'organize-series'); ?></a>
                    <div>
						<label>
							<span class="description">
								<?php esc_html_e('In version 2.11.4, PublishPress Series made changes to how series are stored. You can run the upgrade task here if you\'re having issues with series parts.', 'organize-series'); ?>
							</span>
						</label>
                    </div>
                </td>
        	</tr>

			<tr valign="top">
            	<th scope="row"><label>
                	    <?php esc_html_e('Sync Series Order to Menu Order', 'organize-series'); ?>
                	</label>
            	</th>

            	<td>
					<a class="button" href="<?php echo esc_url(admin_url('admin.php?page=orgseries_options_page&series_action=sync-menu-order&nonce='. wp_create_nonce('sync-menu-order-action'))); ?>"><?php esc_html_e('Sync to Menu Order', 'organize-series'); ?></a>
                    <div>
						<label>
							<span class="description">
								<?php esc_html_e('This will sync all series part numbers to WordPress menu_order field. Useful for themes/page builders that can only sort by menu order (Page Attributes: Order). This allows you to use native WordPress ordering instead of custom fields.', 'organize-series'); ?>
							</span>
						</label>
                    </div>
                </td>
        	</tr>

			<tr valign="top">
            	<th scope="row"><label>
                	    <?php esc_html_e('Reset settings', 'organize-series'); ?>
                	</label>
            	</th>
            	<td><input type="submit" class="button" name="option_reset" value="<?php esc_attr_e('Reset options to default', 'organize-series'); ?>" /></td>
        	</tr>

    </tbody>
	</table>	<?php
}
