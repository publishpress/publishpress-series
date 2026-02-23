<?php
/**
 * Post Types tab - section and fieldset (free only).
 *
 * Extracted from orgSeries-options.php during settings page modularization.
 *
 * @package Publishpress Series
 */

add_settings_section('series_cpt_settings', __('Post Types', 'publishpress-series-pro'), 'ppseries_cpt_section', 'orgseries_options_page');
add_settings_field('orgseries_cpt_settings', __('Custom Post Type Support', 'publishpress-series-pro'), 'series_cpt_settings_display', 'orgseries_options_page', 'series_cpt_settings');

/**
 * Display the Post Types section header
 */
function ppseries_cpt_section() {
	?>
	<p class="description"><?php _e('Enable PublishPress Series for custom post types.', 'organize-series'); ?></p>
	<?php
}

/**
 * Display the Custom Post Type Support settings with Pro badge
 */
function series_cpt_settings_display() {
	global $orgseries;
	$post_types = get_post_types(array( 'show_ui' => true, 'public' => true ));
	$excluded_post_type = ['series_group', 'wp_block', 'attachment'];
	?>
	<div class="ppseries-pro-feature">
		<table class="form-table ppseries-settings-table">
			<tbody>
				<tr valign="top">
					<td>
						<table><tbody>
							<?php
							foreach ( $post_types as $post_type ) {
								if (in_array($post_type, $excluded_post_type)){
									continue;
								}
								
								// Get post type label
								$post_type_object = get_post_type_object($post_type);
								$post_type_label = $post_type_object ? $post_type_object->labels->singular_name : $post_type;
								?>
								<tr valign="top">
								<th scope="row">
									<label for="<?php echo $post_type; ?>">
										<?php echo $post_type_label; ?>
									</label>
								</th>
								<td>
									<label>
									<input id="<?php echo $post_type; ?>" type="checkbox" <?php echo ($post_type === 'post') ? 'checked="checked"' : 'disabled="disabled"'; ?> />
									<?php if ($post_type !== 'post') : ?>
									<div class="ppseries-pro-lock">
										<span class="ppseries-pro-badge">PRO</span>
										<span class="tooltip-text">
											<span><?php esc_html_e('This feature is available in PublishPress Series Pro', 'organize-series'); ?></span>
											<i></i>
										</span>
									</div>
									<?php endif; ?>
								</td>
								</tr>
								<?php
							}
							?>
						</tbody></table>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
	<?php
}
