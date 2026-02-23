<?php
/**
 * Legacy tab - section and fieldset with stacked postbox cards.
 *
 * Contains deprecated features kept for backward compatibility:
 * CSS Styles, Series Overview, Series TOC, Series Icons,
 * Custom Templates, Series Order.
 *
 * @package Publishpress Series
 */

add_settings_section('series_legacy_settings', __('Legacy', 'organize-series'), 'orgseries_legacy_section', 'orgseries_options_page');
add_settings_field('series_legacy_fieldset', __('Legacy Settings', 'organize-series'), 'series_legacy_fieldset', 'orgseries_options_page', 'series_legacy_settings');

function orgseries_legacy_section() {
	
}

function series_legacy_fieldset() {
	global $orgseries;
	$org_opt = $orgseries->settings;
	$org_name = 'org_series_options';
	$series_css_tougle = is_array($org_opt) && isset($org_opt['series_css_tougle']) ? $org_opt['series_css_tougle'] : 'default';
	$series_perp_toc = is_array($org_opt) && isset($org_opt['series_perp_toc']) ? $org_opt['series_perp_toc'] : 10;

	$overview_page_layouts = [
		'default' => __('Default', 'organize-series'),
		'grid'    => __('Grid', 'organize-series'),
		'list'    => __('List', 'organize-series'),
	];
	?>
	<div class="ppseries-legacy-cards-wrap">

		<!-- CSS Styles -->
		<div class="ppseries-legacy-card postbox">
			<h3 class="ppseries-legacy-card-header hndle"><span><?php esc_html_e('CSS Styles', 'organize-series'); ?></span></h3>
			<div class="ppseries-legacy-card-content inside">
				<table class="form-table ppseries-settings-table">
					<tbody>
						<tr valign="top">
							<th scope="row"><label for="custom_css"><?php esc_html_e('Use PublishPress Series CSS styles?', 'organize-series'); ?></label></th>
							<td><input name="<?php echo esc_attr($org_name); ?>[custom_css]" id="custom_css" type="checkbox" value="1" <?php checked('1', isset($org_opt['custom_css']) ? $org_opt['custom_css'] : ''); ?> /></td>
						</tr>
						<tr valign="top">
							<th scope="row"><label><?php esc_html_e('Style options', 'organize-series'); ?></label></th>
							<td>
								<label><input name="<?php echo esc_attr($org_name); ?>[series_css_tougle]" class="css_style" id="css_default" type="radio" value="default" <?php checked('default', $series_css_tougle); ?> <?php disabled('0', isset($org_opt['custom_css']) ? $org_opt['custom_css'] : ''); ?> /><?php esc_html_e('Use default style', 'organize-series'); ?> </label><br />
								<label><input name="<?php echo esc_attr($org_name); ?>[series_css_tougle]" class="css_style" id="css_box" type="radio" value="box" <?php checked('box', $series_css_tougle); ?> <?php disabled('0', isset($org_opt['custom_css']) ? $org_opt['custom_css'] : ''); ?> /><?php esc_html_e('Use box style', 'organize-series'); ?> </label><br />
								<label><input name="<?php echo esc_attr($org_name); ?>[series_css_tougle]" class="css_style" id="css_dark" type="radio" value="dark" <?php checked('dark', $series_css_tougle); ?> <?php disabled('0', isset($org_opt['custom_css']) ? $org_opt['custom_css'] : ''); ?> /><?php esc_html_e('Use dark style', 'organize-series'); ?> </label><br />
								<label><input name="<?php echo esc_attr($org_name); ?>[series_css_tougle]" class="css_style" id="css_light" type="radio" value="light" <?php checked('light', $series_css_tougle); ?> <?php disabled('0', isset($org_opt['custom_css']) ? $org_opt['custom_css'] : ''); ?> /><?php esc_html_e('Use light style', 'organize-series'); ?> </label>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>

		<!-- Display on Series Overview screens -->
		<div class="ppseries-legacy-card postbox">
			<h3 class="ppseries-legacy-card-header hndle"><span><?php esc_html_e('Series Overview', 'organize-series'); ?></span></h3>
			<div class="ppseries-legacy-card-content inside">
				<p class="description"><?php esc_html_e('Choose the design for the taxonomy page where a single Series is displayed.', 'organize-series'); ?></p>
				<table class="form-table ppseries-settings-table">
					<tbody>
						<tr valign="top">
							<th scope="row"><label for="series_overview_page_layout"><?php esc_html_e('Layout:', 'organize-series'); ?></label></th>
							<td>
								<select name="<?php echo esc_attr($org_name); ?>[series_overview_page_layout]" id="series_overview_page_layout">
								<?php
								foreach ($overview_page_layouts as $key => $label) {
									$selected = (isset($org_opt['series_overview_page_layout']) && $org_opt['series_overview_page_layout'] === $key) ? 'selected="selected"' : '';
									// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
									echo '<option value="' . esc_attr($key) . '" ' . $selected . '>' . esc_html($label) . '</option>';
								}
								?>
								</select>
								<div id="series_overview_page_layout_desc">
									<p class="description">
										<?php
										echo sprintf(
											// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
											__('Choosing a layout different to "Default" will override the taxonomy template from your theme. <a href="%s" target="_blank">Click here for details on how to customize these designs</a>.', 'organize-series'),
											'https://publishpress.com/knowledge-base/series-archive-templates/'
										);
										?>
									</p>
								</div>
							</td>
						</tr>
						<tr valign="top" class="pps-row-columns"<?php echo (isset($org_opt['series_overview_page_layout']) && $org_opt['series_overview_page_layout'] === 'grid') ? '' : ' style="display:none;"'; ?>>
							<th scope="row"><label for="series_overview_page_columns"><?php esc_html_e('Columns:', 'organize-series'); ?></label></th>
							<td>
								<input min="1" max="6" name="<?php echo esc_attr($org_name); ?>[series_overview_page_columns]" value="<?php echo (isset($org_opt['series_overview_page_columns']) ? esc_attr(htmlspecialchars($org_opt['series_overview_page_columns'])) : '1'); ?>" id="series_overview_page_columns" type="number" />
							</td>
						</tr>
						<tr valign="top">
							<th scope="row"><label><?php esc_html_e('Order series by:', 'organize-series'); ?></label></th>
							<td>
								<label><input name="<?php echo esc_attr($org_name); ?>[series_posts_orderby]" id="series_posts_orderby_part" type="radio" value="meta_value" <?php checked('meta_value', isset($org_opt['series_posts_orderby']) ? $org_opt['series_posts_orderby'] : ''); ?> /><?php esc_html_e('Series part', 'organize-series'); ?></label> &nbsp;
								<label><input name="<?php echo esc_attr($org_name); ?>[series_posts_orderby]" id="series_posts_orderby_date" type="radio" value="post_date" <?php checked('post_date', isset($org_opt['series_posts_orderby']) ? $org_opt['series_posts_orderby'] : ''); ?> /><?php esc_html_e('Order by date', 'organize-series'); ?></label>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row"><label><?php esc_html_e('Series order method', 'organize-series'); ?></label></th>
							<td>
								<label><input name="<?php echo esc_attr($org_name); ?>[series_posts_order]" id="series_posts_order_ASC" type="radio" value="ASC" <?php checked('ASC', isset($org_opt['series_posts_order']) ? $org_opt['series_posts_order'] : ''); ?> /><?php esc_html_e('Ascending', 'organize-series'); ?></label>&nbsp;
								<label><input name="<?php echo esc_attr($org_name); ?>[series_posts_order]" id="series_posts_order_DESC" type="radio" value="DESC" <?php checked('DESC', isset($org_opt['series_posts_order']) ? $org_opt['series_posts_order'] : ''); ?> /><?php esc_html_e('Descending', 'organize-series'); ?></label>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>

		<!-- Series TOC -->
		<div class="ppseries-legacy-card postbox">
			<h3 class="ppseries-legacy-card-header hndle"><span><?php esc_html_e('Series Table of Contents', 'organize-series'); ?></span></h3>
			<div class="ppseries-legacy-card-content inside">
				<table class="form-table ppseries-settings-table">
					<tbody>
						<tr valign="top">
							<th scope="row"><label for="series_toc_url"><?php esc_html_e('Series Table of Contents URL:', 'organize-series'); ?></label></th>
							<td>
								<span id="toc-home-url"><?php bloginfo('url'); ?>/</span><input type="text" name="<?php echo esc_attr($org_name); ?>[series_toc_url]" id="series_toc_url" value="<?php echo isset($org_opt['series_toc_url']) ? esc_attr(htmlspecialchars($org_opt['series_toc_url'])) : ''; ?>" />
								<button onclick="gotoTOCUrl(event)" class="button"><?php esc_html_e('view page', 'organize-series'); ?></button>
								<?php
								global $wp_rewrite;
								if (empty($wp_rewrite->permalink_structure)) {
									?>
									<div class="publishpress-series-permalink-error">
										<p>
											<?php
											printf(
												esc_html__('You must %1s update your permalink structure %2s to something other than "Plain" for the Series Table of Contents URL to work.', 'organize-series'),
												'<a href="' . admin_url('options-permalink.php') . '">',
												'</a>'
											);
											?>
										</p>
									</div>
									<?php
								}
								?>
							</td>
						</tr>
						<script>
							function gotoTOCUrl(e){
								e.preventDefault();
								var toc_url = document.getElementById("toc-home-url").innerHTML + document.getElementById("series_toc_url").value;
								window.open(toc_url);
							}
						</script>
						<tr valign="top">
							<th scope="row"><label for="series_perp_toc"><?php esc_html_e('Series Per Page:', 'organize-series'); ?></label></th>
							<td><input type="number" name="<?php echo esc_attr($org_name); ?>[series_perp_toc]" value="<?php echo (int) ($series_perp_toc); ?>" /></td>
						</tr>
						<tr valign="top">
							<th scope="row"><label for="series_toc_title"><?php esc_html_e('Series Table of Contents Title:', 'organize-series'); ?></label></th>
							<td><input type="text" id="series_toc_title" name="<?php echo esc_attr($org_name); ?>[series_toc_title]" value="<?php echo isset($org_opt['series_toc_title']) ? esc_attr(htmlspecialchars($org_opt['series_toc_title'])) : ''; ?>" /></td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>

		<!-- Series Icons -->
		<div class="ppseries-legacy-card postbox">
			<h3 class="ppseries-legacy-card-header hndle"><span><?php esc_html_e('Series Icons', 'organize-series'); ?></span></h3>
			<div class="ppseries-legacy-card-content inside">
				<table class="form-table ppseries-settings-table">
					<tbody>
						<tr valign="top">
							<th scope="row"><label for="series_icon_width_series_page"><?php esc_html_e('Width for icon on series table of contents page (in pixels)', 'organize-series'); ?></label></th>
							<td><input min="1" max="1000000000" name="<?php echo esc_attr($org_name); ?>[series_icon_width_series_page]" id="series_icon_width_series_page" type="number" value="<?php echo isset($org_opt['series_icon_width_series_page']) ? esc_attr($org_opt['series_icon_width_series_page']) : ''; ?>" /></td>
						</tr>
						<tr valign="top">
							<th scope="row"><label for="series_icon_width_post_page"><?php esc_html_e('Width for icon on a post page (in pixels).', 'organize-series'); ?></label></th>
							<td><input min="1" max="1000000000" name="<?php echo esc_attr($org_name); ?>[series_icon_width_post_page]" id="series_icon_width_post_page" type="number" value="<?php echo isset($org_opt['series_icon_width_post_page']) ? esc_attr($org_opt['series_icon_width_post_page']) : ''; ?>" /></td>
						</tr>
						<tr valign="top">
							<th scope="row"><label for="series_icon_width_latest_series"><?php esc_html_e('Width for icon if displayed via the latest series template (in pixels).', 'organize-series'); ?></label></th>
							<td><input min="1" max="1000000000" name="<?php echo esc_attr($org_name); ?>[series_icon_width_latest_series]" id="series_icon_width_latest_series" type="number" value="<?php echo isset($org_opt['series_icon_width_latest_series']) ? esc_attr($org_opt['series_icon_width_latest_series']) : ''; ?>" /></td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>

		<!-- Latest Series -->
		<div class="ppseries-legacy-card postbox">
			<h3 class="ppseries-legacy-card-header hndle"><span><?php esc_html_e('Latest Series', 'organize-series'); ?></span></h3>
			<div class="ppseries-legacy-card-content inside">
				<p class="description"><?php esc_html_e('This display is used by the "Latest Series" widget.', 'organize-series'); ?></p>
				<table class="form-table ppseries-settings-table">
					<tbody>
						<tr valign="top">
							<th scope="row"><label for="latest_series_before_template"><?php esc_html_e('Latest Series (tags before):', 'organize-series'); ?></label></th>
							<td><input type="text" name="<?php echo esc_attr($org_name); ?>[latest_series_before_template]" id="latest_series_before_template" value="<?php echo isset($org_opt['latest_series_before_template']) ? esc_attr(htmlspecialchars($org_opt['latest_series_before_template'])) : ''; ?>" class="ppseries-full-width"></td>
						</tr>
						<tr valign="top">
							<th scope="row"><label for="latest_series_inner_template"><?php esc_html_e('Latest Series (inner tags):', 'organize-series'); ?></label></th>
							<td><input type="text" name="<?php echo esc_attr($org_name); ?>[latest_series_inner_template]" id="latest_series_inner_template" value="<?php echo isset($org_opt['latest_series_inner_template']) ? esc_attr(htmlspecialchars($org_opt['latest_series_inner_template'])) : ''; ?>" class="ppseries-full-width"></td>
						</tr>
						<tr valign="top">
							<th scope="row"><label for="latest_series_after_template"><?php esc_html_e('Latest Series (tags after):', 'organize-series'); ?></label></th>
							<td><input type="text" name="<?php echo esc_attr($org_name); ?>[latest_series_after_template]" id="latest_series_after_template" value="<?php echo isset($org_opt['latest_series_after_template']) ? esc_attr(htmlspecialchars($org_opt['latest_series_after_template'])) : ''; ?>" class="ppseries-full-width"></td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>

		<!-- Series Table of Contents -->
		<div class="ppseries-legacy-card postbox">
			<h3 class="ppseries-legacy-card-header hndle"><span><?php esc_html_e('Series Table of Contents', 'organize-series'); ?></span></h3>
			<div class="ppseries-legacy-card-content inside">
				<p class="description"><?php esc_html_e('This display is used by the "Series Table of Contents" widget, shortcode, and URL.', 'organize-series'); ?></p>
				<table class="form-table ppseries-settings-table">
					<tbody>
						<tr valign="top">
							<th scope="row"><label for="series_table_of_contents_box_template"><?php esc_html_e('Series Table of Contents', 'organize-series'); ?></label></th>
							<td>
								<textarea name="<?php echo esc_attr($org_name); ?>[series_table_of_contents_box_template]" id="series_table_of_contents_box_template" class="ppseries-textarea ppseries-full-width"><?php echo isset($org_opt['series_table_of_contents_box_template']) ? esc_html(htmlspecialchars(stripslashes($org_opt['series_table_of_contents_box_template']))) : ''; ?></textarea>
								<p class="description"><?php esc_html_e('This display is used by the "Series Table of Contents". To find the URL for this display, go the "Display" tab and then "Series Table of Contents URL".', 'organize-series'); ?></p>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>


	</div>
	<?php
}
