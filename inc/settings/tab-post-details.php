<?php
/**
 * Post Details tab - section and fieldset.
 *
 * Consolidates the Post Details enable toggle (from Display tab) and
 * details selector/position/limit (from Templates tab) into one dedicated tab.
 *
 * @package Publishpress Series
 */

add_settings_section('series_post_details_settings', __('Post Details', 'organize-series'), 'orgseries_post_details_section', 'orgseries_options_page');
add_settings_field('series_post_details_fieldset', __('Post Details Settings', 'organize-series'), 'series_post_details_fieldset', 'orgseries_options_page', 'series_post_details_settings');

function orgseries_post_details_section() {
	?>
	<p class="description"><?php esc_html_e('Configure the Series Post Details display shown on posts in a series.', 'organize-series'); ?></p>
	<?php
}

function series_post_details_fieldset() {
	global $orgseries;
	$org_opt = $orgseries->settings;
	$org_name = 'org_series_options';

	$post_box_locations = [
		'default' => __('As in Template', 'organize-series'),
		'top'     => __('Top', 'organize-series'),
		'bottom'  => __('Bottom', 'organize-series'),
	];
	?>
	<div style="line-height:normal;">
		<table class="form-table ppseries-settings-table">
			<tbody>

				<tr valign="top">
					<th scope="row"><label for="auto_tag_seriesmeta_toggle"><?php esc_html_e('Display Series Post Details?', 'organize-series'); ?></label></th>
					<td><input name="<?php echo esc_attr($org_name); ?>[auto_tag_seriesmeta_toggle]" id="auto_tag_seriesmeta_toggle" type="checkbox" value="1" <?php checked('1', isset($org_opt['auto_tag_seriesmeta_toggle']) ? $org_opt['auto_tag_seriesmeta_toggle'] : ''); ?> /></td>
				</tr>

				<tr valign="top">
					<th scope="row"><label for="series_post_details_selection"><?php esc_html_e('Post Details Selection', 'organize-series'); ?></label></th>
					<td>
						<select name="<?php echo esc_attr($org_name); ?>[series_post_details_selection]" id="series_post_details_selection" class="ppseries-full-width ppseries-layout-select" data-post-type="pps_post_details" data-saved-key="series_post_details_selection" data-edit-label="<?php esc_attr_e('Edit this layout', 'organize-series'); ?>">
							<option value=""><?php esc_html_e('Loading…', 'organize-series'); ?></option>
						</select>
						<p class="ppseries-edit-link-wrap" style="display:none;"><a href="#" class="ppseries-edit-link">&rarr;</a></p>
					</td>
				</tr>

				<tr valign="top" id="series_metabox_position_row" class="ppseries-custom-template-row">
					<th scope="row"><label for="series_metabox_position"><?php esc_html_e('Post Details Position', 'organize-series'); ?></label></th>
					<td>
						<select name="<?php echo esc_attr($org_name); ?>[series_metabox_position]" id="series_metabox_position">
						<?php
						foreach ($post_box_locations as $key => $label) {
							$selected = (isset($org_opt['series_metabox_position']) && $org_opt['series_metabox_position'] === $key) ? 'selected="selected"' : '';
							// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							echo '<option value="' . esc_attr($key) . '" ' . $selected . '>' . esc_html($label) . '</option>';
						}
						?>
						</select>
					</td>
				</tr>

				<tr valign="top" id="series_meta_template_row" class="ppseries-custom-template-row">
					<th scope="row"><label for="series_meta_template"><?php esc_html_e('Series Meta Template', 'organize-series'); ?></label></th>
					<td><textarea name="<?php echo esc_attr($org_name); ?>[series_meta_template]" id="series_meta_template" class="ppseries-textarea ppseries-full-width"><?php echo isset($org_opt['series_meta_template']) ? esc_html(htmlspecialchars(stripslashes($org_opt['series_meta_template']))) : ''; ?></textarea></td>
				</tr>

				<tr valign="top" id="series_meta_excerpt_template_row" class="ppseries-custom-template-row">
					<th scope="row"><label for="series_meta_excerpt_template"><?php esc_html_e('Series Meta (with excerpts)', 'organize-series'); ?></label></th>
					<td><textarea name="<?php echo esc_attr($org_name); ?>[series_meta_excerpt_template]" id="series_meta_excerpt_template" class="ppseries-textarea ppseries-full-width"><?php echo isset($org_opt['series_meta_excerpt_template']) ? esc_html(htmlspecialchars(stripslashes($org_opt['series_meta_excerpt_template']))) : ''; ?></textarea></td>
				</tr>

				<tr valign="top" id="limit_series_meta_to_single_row" class="ppseries-custom-template-row">
					<th scope="row"><label for="limit_series_meta_to_single"><?php esc_html_e('Limit to single page only', 'organize-series'); ?></label></th>
					<td>
						<input name="<?php echo esc_attr($org_name); ?>[limit_series_meta_to_single]" value="1" id="limit_series_meta_to_single" type="checkbox" <?php checked('1', isset($org_opt['limit_series_meta_to_single']) ? $org_opt['limit_series_meta_to_single'] : ''); ?> />
						<span class="description"><?php esc_html_e('Whether to limit series meta display to single page only or include archive page.', 'organize-series'); ?></span>
					</td>
				</tr>

			</tbody>
		</table>
	</div>
	<?php
}
