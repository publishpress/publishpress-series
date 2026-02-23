<?php
/**
 * Post List Box tab - section and fieldset.
 *
 * Consolidates the Post List Box enable toggle (from Display tab) and
 * box selector/position (from Templates tab) into one dedicated tab.
 *
 * @package Publishpress Series
 */

add_settings_section('series_post_list_box_settings', __('Post List Box', 'organize-series'), 'orgseries_post_list_box_section', 'orgseries_options_page');
add_settings_field('series_post_list_box_fieldset', __('Post List Box Settings', 'organize-series'), 'series_post_list_box_fieldset', 'orgseries_options_page', 'series_post_list_box_settings');

function orgseries_post_list_box_section() {
	?>
	<p class="description"><?php esc_html_e('Configure the Post List Box that displays all posts in a series.', 'organize-series'); ?></p>
	<?php
}

function series_post_list_box_fieldset() {
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
					<th scope="row"><label for="auto_tag_toggle"><?php esc_html_e('Display Post List Boxes?', 'organize-series'); ?></label></th>
					<td><input name="<?php echo esc_attr($org_name); ?>[auto_tag_toggle]" value="1" id="auto_tag_toggle" type="checkbox" <?php checked('1', isset($org_opt['auto_tag_toggle']) ? $org_opt['auto_tag_toggle'] : ''); ?> /></td>
				</tr>

				<tr valign="top">
					<th scope="row"><label for="series_post_list_box_selection"><?php esc_html_e('Post List Boxes Selection', 'organize-series'); ?></label></th>
					<td>
						<select name="<?php echo esc_attr($org_name); ?>[series_post_list_box_selection]" id="series_post_list_box_selection" class="ppseries-full-width ppseries-layout-select" data-post-type="pps_post_list_box" data-saved-key="series_post_list_box_selection" data-edit-label="<?php esc_attr_e('Edit this box', 'organize-series'); ?>">
							<option value=""><?php esc_html_e('Loading…', 'organize-series'); ?></option>
						</select>
						<p class="ppseries-edit-link-wrap" style="display:none;"><a href="#" class="ppseries-edit-link">&rarr;</a></p>
					</td>
				</tr>

				<tr valign="top" id="series_post_list_position_row" class="ppseries-custom-template-row">
					<th scope="row"><label for="series_post_list_position"><?php esc_html_e('Post List Box Position', 'organize-series'); ?></label></th>
					<td>
						<select name="<?php echo esc_attr($org_name); ?>[series_post_list_position]" id="series_post_list_position">
						<?php
						foreach ($post_box_locations as $key => $label) {
							$selected = (isset($org_opt['series_post_list_position']) && $org_opt['series_post_list_position'] === $key) ? 'selected="selected"' : '';
							// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							echo '<option value="' . esc_attr($key) . '" ' . $selected . '>' . esc_html($label) . '</option>';
						}
						?>
						</select>
					</td>
				</tr>

				<tr valign="top" id="series_post_list_template_row" class="ppseries-custom-template-row">
					<th scope="row"><label for="series_post_list_template"><?php esc_html_e('Series Post List Template', 'organize-series'); ?></label></th>
					<td><textarea name="<?php echo esc_attr($org_name); ?>[series_post_list_template]" id="series_post_list_template" class="ppseries-textarea ppseries-full-width"><?php echo isset($org_opt['series_post_list_template']) ? esc_html(htmlspecialchars(stripslashes($org_opt['series_post_list_template']))) : ''; ?></textarea></td>
				</tr>
				<tr valign="top" id="series_post_list_post_linked_post_row" class="ppseries-custom-template-row">
					<th scope="row"><label for="series_post_list_post_template"><?php esc_html_e('Post Title (Linked Post)', 'organize-series'); ?></label></th>
					<td><input type="text" name="<?php echo esc_attr($org_name); ?>[series_post_list_post_template]" id="series_post_list_post_template" value="<?php echo isset($org_opt['series_post_list_post_template']) ? esc_attr(htmlspecialchars($org_opt['series_post_list_post_template'])) : ''; ?>" class="ppseries-full-width"></td>
				</tr>
				<?php do_action('plist_ptitle_template_unpublished'); ?>
				<tr valign="top" id="series_post_list_currentpost_row" class="ppseries-custom-template-row">
					<th scope="row"><label for="series_post_list_currentpost_template"><?php esc_html_e('Post Title (Current Post)', 'organize-series'); ?></label></th>
					<td><input type="text" name="<?php echo esc_attr($org_name); ?>[series_post_list_currentpost_template]" id="series_post_list_currentpost_template" value="<?php echo isset($org_opt['series_post_list_currentpost_template']) ? esc_attr(htmlspecialchars($org_opt['series_post_list_currentpost_template'])) : ''; ?>" class="ppseries-full-width"></td>
				</tr>

			</tbody>
		</table>
	</div>
	<?php
}
