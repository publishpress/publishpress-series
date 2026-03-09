<?php
/**
 * Navigation tab - section and fieldset.
 *
 * Consolidates the Navigation enable toggle (from Display tab) and
 * navigation selector/position (from Templates tab) into one dedicated tab.
 *
 * @package Publishpress Series
 */

add_settings_section('series_navigation_settings', __('Navigation', 'organize-series'), 'orgseries_navigation_section', 'orgseries_options_page');
add_settings_field('series_navigation_fieldset', __('Navigation Settings', 'organize-series'), 'series_navigation_fieldset', 'orgseries_options_page', 'series_navigation_settings');

function orgseries_navigation_section() {
	?>
	<p class="description"><?php esc_html_e('Configure the Series Navigation display shown on posts in a series.', 'organize-series'); ?></p>
	<?php
}

function series_navigation_fieldset() {
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
					<th scope="row"><label for="auto_tag_nav_toggle"><?php esc_html_e('Display Series Navigation?', 'organize-series'); ?></label></th>
					<td><input name="<?php echo esc_attr($org_name); ?>[auto_tag_nav_toggle]" id="auto_tag_nav_toggle" type="checkbox" value="1" <?php checked('1', isset($org_opt['auto_tag_nav_toggle']) ? $org_opt['auto_tag_nav_toggle'] : ''); ?> /></td>
				</tr>

				<tr valign="top">
					<th scope="row"><label for="series_post_navigation_selection"><?php esc_html_e('Navigation Selection', 'organize-series'); ?></label></th>
					<td>
						<select name="<?php echo esc_attr($org_name); ?>[series_post_navigation_selection]" id="series_post_navigation_selection" class="ppseries-full-width ppseries-layout-select" data-post-type="pps_post_navigation" data-saved-key="series_post_navigation_selection" data-edit-label="<?php esc_attr_e('Edit this layout', 'organize-series'); ?>">
							<option value=""><?php esc_html_e('Loading…', 'organize-series'); ?></option>
						</select>
						<p class="ppseries-edit-link-wrap" style="display:none;"><a href="#" class="ppseries-edit-link">&rarr;</a></p>
					</td>
				</tr>

				<tr valign="top" id="series_navigation_box_position_row" class="ppseries-custom-template-row">
					<th scope="row"><label for="series_navigation_box_position"><?php esc_html_e('Navigation Position', 'organize-series'); ?></label></th>
					<td>
						<select name="<?php echo esc_attr($org_name); ?>[series_navigation_box_position]" id="series_navigation_box_position">
						<?php
						foreach ($post_box_locations as $key => $label) {
							$selected = (isset($org_opt['series_navigation_box_position']) && $org_opt['series_navigation_box_position'] === $key) ? 'selected="selected"' : '';
							// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							echo '<option value="' . esc_attr($key) . '" ' . $selected . '>' . esc_html($label) . '</option>';
						}
						?>
						</select>
					</td>
				</tr>

				<tr valign="top" id="series_post_nav_template_row" class="ppseries-custom-template-row">
					<th scope="row"><label for="series_post_nav_template"><?php esc_html_e('Navigation Template', 'organize-series'); ?></label></th>
					<td><textarea name="<?php echo esc_attr($org_name); ?>[series_post_nav_template]" id="series_post_nav_template" class="ppseries-textarea ppseries-full-width"><?php echo isset($org_opt['series_post_nav_template']) ? esc_html(htmlspecialchars(stripslashes($org_opt['series_post_nav_template']))) : ''; ?></textarea></td>
				</tr>

				<tr valign="top" id="series_nextpost_nav_custom_text_row" class="ppseries-custom-template-row">
					<th scope="row"><label for="series_nextpost_nav_custom_text"><?php esc_html_e('Next Post Text', 'organize-series'); ?></label></th>
					<td><input type="text" name="<?php echo esc_attr($org_name); ?>[series_nextpost_nav_custom_text]" id="series_nextpost_nav_custom_text" value="<?php echo isset($org_opt['series_nextpost_nav_custom_text']) ? esc_attr(htmlspecialchars($org_opt['series_nextpost_nav_custom_text'])) : ''; ?>" class="ppseries-full-width"></td>
				</tr>

				<tr valign="top" id="series_prevpost_nav_custom_text_row" class="ppseries-custom-template-row">
					<th scope="row"><label for="series_prevpost_nav_custom_text"><?php esc_html_e('Previous Post Text', 'organize-series'); ?></label></th>
					<td><input type="text" name="<?php echo esc_attr($org_name); ?>[series_prevpost_nav_custom_text]" id="series_prevpost_nav_custom_text" value="<?php echo isset($org_opt['series_prevpost_nav_custom_text']) ? esc_attr(htmlspecialchars($org_opt['series_prevpost_nav_custom_text'])) : ''; ?>" class="ppseries-full-width"></td>
				</tr>

				<tr valign="top" id="series_firstpost_nav_custom_text_row" class="ppseries-custom-template-row">
					<th scope="row"><label for="series_firstpost_nav_custom_text"><?php esc_html_e('First Post Text', 'organize-series'); ?></label></th>
					<td><input type="text" name="<?php echo esc_attr($org_name); ?>[series_firstpost_nav_custom_text]" id="series_firstpost_nav_custom_text" value="<?php echo (isset($org_opt['series_firstpost_nav_custom_text'])) ? esc_attr(htmlspecialchars($org_opt['series_firstpost_nav_custom_text'])) : 'Series Home'; ?>" class="ppseries-full-width"></td>
				</tr>

			</tbody>
		</table>
	</div>
	<?php
}
