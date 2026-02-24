<?php
/**
 * Series Category tab - section and fieldset.
 *
 * @package Publishpress Series
 */

add_settings_section('series_group_settings', __('Series Category', 'organize-series'), 'ppseries_series_group_section', 'orgseries_options_page');
add_settings_field('series_group_fieldset', __('Series Category', 'organize-series'), 'ppseries_series_group_fieldset', 'orgseries_options_page', 'series_group_settings');

function ppseries_series_group_section() {
	?>
	<p class="description"><?php esc_html_e('Settings for Series Categories archive URL and display behavior.', 'organize-series'); ?></p>
	<?php
}

function ppseries_series_group_fieldset() {
	global $orgseries;
	$org_opt = $orgseries->settings;
	$org_name = 'org_series_options';
	$series_group_archive_render_mode = isset($org_opt['series_group_archive_render_mode']) ? $org_opt['series_group_archive_render_mode'] : 'plugin';
	?>
	<table class="form-table ppseries-settings-table">
		<tbody>
			<tr valign="top">
				<th scope="row"><label for="series_group_custom_base"><?php esc_html_e('Series Categories URL Base:', 'organize-series'); ?></label></th>
				<td>
					<input type="text" id="series_group_custom_base" name="<?php echo esc_attr($org_name); ?>[series_group_custom_base]" value="<?php echo isset($org_opt['series_group_custom_base']) ? esc_attr(htmlspecialchars($org_opt['series_group_custom_base'])) : 'series-category'; ?>"/>
					<p class="description">
						<?php esc_html_e('This text will be used in Series Categories archive URLs. If you change it, old URLs will be redirected to the new base.', 'organize-series'); ?>
					</p>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row"><label for="series_group_archive_render_mode"><?php esc_html_e('Series Categories Archive Template:', 'organize-series'); ?></label></th>
				<td>
					<select id="series_group_archive_render_mode" name="<?php echo esc_attr($org_name); ?>[series_group_archive_render_mode]">
						<option value="theme" <?php selected('theme', $series_group_archive_render_mode); ?>><?php esc_html_e('Use template from Theme', 'organize-series'); ?></option>
						<option value="plugin" <?php selected('plugin', $series_group_archive_render_mode); ?>><?php esc_html_e('Use template from Plugin', 'organize-series'); ?></option>
					</select>
					<p class="description">
						<?php esc_html_e('Theme template uses your active theme template hierarchy. Plugin template uses PublishPress Series fallback design when your theme does not provide taxonomy-series_group.php.', 'organize-series'); ?>
					</p>
				</td>
			</tr>

			<tr valign="top" id="ppseries-plugin-template-display-options-row">
				<th scope="row"><?php esc_html_e('Plugin Template Display Options:', 'organize-series'); ?></th>
				<td>
					<label>
						<input type="checkbox" name="<?php echo esc_attr($org_name); ?>[series_group_plugin_show_image]" value="1" <?php checked(1, isset($org_opt['series_group_plugin_show_image']) ? (int) $org_opt['series_group_plugin_show_image'] : 1); ?> />
						<?php esc_html_e('Show Series Icon', 'organize-series'); ?>
					</label>
					<br />
					<label>
						<input type="checkbox" name="<?php echo esc_attr($org_name); ?>[series_group_plugin_show_description]" value="1" <?php checked(1, isset($org_opt['series_group_plugin_show_description']) ? (int) $org_opt['series_group_plugin_show_description'] : 1); ?> />
						<?php esc_html_e('Show Series Description', 'organize-series'); ?>
					</label>
					<br />
					<label>
						<input type="checkbox" name="<?php echo esc_attr($org_name); ?>[series_group_plugin_show_series_items]" value="1" <?php checked(1, isset($org_opt['series_group_plugin_show_series_items']) ? (int) $org_opt['series_group_plugin_show_series_items'] : 0); ?> />
						<?php esc_html_e('Show Posts in each Series as list items', 'organize-series'); ?>
					</label>
					<br /><br />
					<label for="series_group_plugin_columns"><?php esc_html_e('Columns:', 'organize-series'); ?></label>
					<?php $series_group_plugin_columns = isset($org_opt['series_group_plugin_columns']) ? (int) $org_opt['series_group_plugin_columns'] : 3; ?>
					<select id="series_group_plugin_columns" name="<?php echo esc_attr($org_name); ?>[series_group_plugin_columns]">
						<option value="1" <?php selected(1, $series_group_plugin_columns); ?>>1</option>
						<option value="2" <?php selected(2, $series_group_plugin_columns); ?>>2</option>
						<option value="3" <?php selected(3, $series_group_plugin_columns); ?>>3</option>
						<option value="4" <?php selected(4, $series_group_plugin_columns); ?>>4</option>
					</select>
				</td>
			</tr>
		</tbody>
	</table>
	<script>
	(function() {
		const select = document.getElementById('series_group_archive_render_mode');
		const row = document.getElementById('ppseries-plugin-template-display-options-row');
		if (!select || !row) return;
		const toggle = () => {
			row.style.display = (select.value === 'plugin') ? '' : 'none';
		};
		select.addEventListener('change', toggle);
		toggle();
	})();
	</script>
	<?php
}
