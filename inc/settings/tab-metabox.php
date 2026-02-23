<?php
/**
 * Metabox tab - section and fieldset.
 *
 * Extracted from orgSeries-options.php during settings page modularization.
 *
 * @package Publishpress Series
 */

add_settings_section('series_metabox_settings', 'Metabox', 'orgseries_metabox_section', 'orgseries_options_page');
add_settings_field('series_metabox_core_fieldset', 'Metabox', 'series_metabox_core_fieldset', 'orgseries_options_page', 'series_metabox_settings');

function orgseries_metabox_section() {
	global $orgseries;
	?>
	<p class="description"><?php esc_html_e('These settings allow you to customize the metabox on the post editing screen.', 'organize-series'); ?></p>
	<?php
}

function series_metabox_core_fieldset() {
	global $orgseries;
	$org_opt = $orgseries->settings;
	$org_name = 'org_series_options';
	$metabox_series_order = is_array($org_opt) && isset($org_opt['metabox_series_order']) ? $org_opt['metabox_series_order'] : 'default';
	$metabox_series_order_options = [
		'default' => __('Default Series Order', 'organize-series'),
		'a-z' 	  => __('Alphabetical A-Z', 'organize-series'),
		'z-a'    => __('Alphabetical Z-A', 'organize-series'),
	];
	?>
	<table class="form-table ppseries-settings-table">
    	<tbody>

            <tr valign="top"><th scope="row"><label for="metabox_show_add_new"><?php esc_html_e('Show "Add New"', 'organize-series'); ?></label></th>
                <td><input name="<?php echo esc_attr($org_name);?>[metabox_show_add_new]" value="1" id="metabox_show_add_new" type="checkbox" <?php checked('1', isset($org_opt['metabox_show_add_new']) ? $org_opt['metabox_show_add_new'] : ''); ?> /></td>
            </tr>

            <tr valign="top"><th scope="row"><label for="metabox_show_post_title_in_widget"><?php esc_html_e('Show "Post title in widget"', 'organize-series'); ?></label></th>
                <td><input name="<?php echo esc_attr($org_name);?>[metabox_show_post_title_in_widget]" value="1" id="metabox_show_post_title_in_widget" type="checkbox" <?php checked('1', isset($org_opt['metabox_show_post_title_in_widget']) ? $org_opt['metabox_show_post_title_in_widget'] : ''); ?> /></td>
            </tr>

			<tr valign="top"><th scope="row"><label for=""><?php esc_html_e('Metabox Series Order', 'organize-series'); ?></label></th>
				<td>
					<?php foreach ($metabox_series_order_options as $key => $label) : ?>
						<div  style="margin-bottom: 10px;">
							<label>
								<input name="<?php echo esc_attr($org_name); ?>[metabox_series_order]" 
									class="" 
									id="metabox_series_order-<?php echo esc_attr($key); ?>" 
									type="radio" 
									value="<?php echo esc_attr($key); ?>" 
									<?php checked($key, $metabox_series_order); ?> 
								/>
								<?php echo esc_html($label); ?> 
							</label>
						</div>
					<?php endforeach; ?>
				</td>
			</tr>

        </tbody>
	</table>	<?php
}
