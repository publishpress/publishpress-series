<?php
/**
 * Pro Features tab - section and fieldset (free only).
 *
 * Extracted from orgSeries-options.php during settings page modularization.
 *
 * @package Publishpress Series
 */

add_settings_section('series_addon_settings', __('Pro Features', 'publishpress-series-pro'), 'ppseries_addon_section', 'orgseries_options_page');
add_settings_field('orgseries_addon_settings', __('Pro Features', 'publishpress-series-pro'), 'series_addon_settings_display', 'orgseries_options_page', 'series_addon_settings');

/**
 * Display the Pro Features section header
 */
function ppseries_addon_section() {
	?>
	<p class="description"><?php _e('These settings allow you enable or disable features in PublishPress Series Pro.', 'organize-series'); ?></p>
	<?php
}

/**
 * Display the Pro Features settings with Pro overlay
 */
function series_addon_settings_display() {
	$series_addons = [
		'cpt' => ['name' => __('Custom Post Type Support', 'organize-series'), 'description' => __('Allow custom post types to be used with PublishPress Series.', 'organize-series')],
		'shortcodes' => ['name' => __('Shortcodes', 'organize-series'), 'description' => __('Provides shortcodes to display series information.', 'organize-series')],
		'extra-tokens' => ['name' => __('Extra Tokens', 'organize-series'), 'description' => __('Provides extra tokens to customize the output of series information.', 'organize-series')],
		'multiples' => ['name' => __('Multiple Series', 'organize-series'), 'description' => __('Allows authors to add posts to more than one series.', 'organize-series')]
	];
	?>
	<div class="ppseries-pro-feature">
		<table class="form-table ppseries-settings-table">
			<tbody>
			<?php
			foreach ( $series_addons as $series_addon => $series_addon_option ) {
				?>
				<tr valign="top">
				<th scope="row">
					<label for="ppseries-enable-<?php echo $series_addon; ?>">
						<?php echo $series_addon_option['name']; ?>
					</label>
				</th>
				<td>
					<label>
					<input type="checkbox" value="<?php echo $series_addon; ?>" id="ppseries-enable-<?php echo $series_addon; ?>" disabled="disabled" />
						<span class="description"><?php echo $series_addon_option['description']; ?></span>
					<div class="ppseries-pro-lock">
						<span class="ppseries-pro-badge">PRO</span>
						<span class="tooltip-text">
							<span><?php esc_html_e('This feature is available in PublishPress Series Pro', 'organize-series'); ?></span>
							<i></i>
						</span>
					</div>
					</label>
				</td>
			</tr>
				<?php
			}
			?>
			</tbody>
		</table>
	</div>
	<?php
}
