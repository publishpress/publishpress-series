<?php
/**
 * Settings upgrade notices and processing functions.
 *
 * Extracted from orgSeries-options.php during settings page modularization.
 *
 * @package Publishpress Series
 */

function publishpress_series_upgrade_require_changes() {
	if (isset($_REQUEST['series_action']) && in_array($_REQUEST['series_action'], ['multiple-series-support', 'sync-menu-order'])) {
		//we don't want to show the warning after the migration request
		return;
	}

	if (current_user_can('manage_publishpress_series') && ! publishpress_multi_series_supported()) {
		$test_series = get_series(['number' => 1]);
		//The upgrade notification only matter to site with series.
		if (!empty($test_series) && !is_wp_error($test_series)) {
			?>
			<div class="notice notice-error">
				<h2 style="margin-top: 15px; margin-bottom: 0px;"><?php esc_html_e('Please update your PublishPress Series data', 'organize-series'); ?></h2> 
				<p>
				<?php esc_html_e('We have made changes to how series are stored and this requires a small update. This improves support for the Multiple Series feature, and resolves some issues with the order of posts. Please click this button to upgrade.', 'organize-series'); ?>
				<br /><br />  <a class="button button-primary" href="<?php echo esc_url(admin_url('admin.php?page=orgseries_options_page&series_action=multiple-series-support&nonce='. wp_create_nonce('multiple-series-support-upgrade'))); ?>"><?php esc_html_e('Update Series data', 'organize-series'); ?></a></p>
			</div>
			<?php
		} else {
			update_option('publishpress_multi_series_supported', true);
		}
	}
}

function publishpress_series_upgrade_require_row_notice($pluginFile, $pluginData) {

	if (current_user_can('manage_publishpress_series') && ! publishpress_multi_series_supported() && in_array($pluginData['Name'], ['PublishPress Series', 'PublishPress Series Pro'])) {
		$test_series = get_series(['number' => 1]);
		//The upgrade notification only matter to site with series.
		if (!empty($test_series) && !is_wp_error($test_series)) {
			?>
			<tr class="ppa-plugin-warning">
				<td colspan="4" class="colspanchange">
					<div class="multiple-instances-warning">
					<h2 style="margin-top: 15px; margin-bottom: 0px;"><?php esc_html_e('Please update your PublishPress Series data', 'organize-series'); ?></h2> 
					<p><?php esc_html_e('We have made changes to how series are stored and this requires a small update. This improves support for the Multiple Series feature, and resolves some issues with the order of posts. Please click this button to upgrade.', 'organize-series'); ?>
				<br /><br />  <a class="button button-primary" href="<?php echo esc_url(admin_url('admin.php?page=orgseries_options_page&series_action=multiple-series-support&nonce='. wp_create_nonce('multiple-series-support-upgrade'))); ?>"><?php esc_html_e('Update Series data', 'organize-series'); ?></a></p>
				</div>
				</td>
			</tr>
			<?php
		} else {
			update_option('publishpress_multi_series_supported', true);
		}
	}
}

function publishpress_series_process_upgrade() {
	global $wpdb;
	if (isset($_REQUEST['series_action'])
		&& isset($_REQUEST['nonce'])
		&& $_REQUEST['series_action'] === 'multiple-series-support'
		&& wp_verify_nonce(sanitize_key($_REQUEST['nonce']), 'multiple-series-support-upgrade')
		&& current_user_can('manage_publishpress_series')
	) {

			//get list of posts that contain the meta key SERIES_PART_KEY
			$query = "SELECT p.ID, t.term_id, pm.meta_value FROM $wpdb->posts AS p LEFT JOIN $wpdb->postmeta AS pm ON p.ID = pm.post_id LEFT JOIN $wpdb->term_relationships AS tr ON p.ID = tr.object_id LEFT JOIN $wpdb->term_taxonomy AS tt ON tr.term_taxonomy_id = tt.term_taxonomy_id LEFT JOIN $wpdb->terms AS t ON tt.term_id = t.term_id WHERE pm.meta_key = '".SERIES_PART_KEY."' AND tt.taxonomy = '".ppseries_get_series_slug()."'";
			$posts = $wpdb->get_results($query);
			
			if (!empty($posts)) {
				foreach ($posts as $post) {
					$meta_key = SERIES_PART_KEY . '_' . $post->term_id;
					$meta_value = $post->meta_value;
					if (empty(get_post_meta($post->ID, $meta_key, true))) {
						add_post_meta($post->ID, $meta_key, $meta_value);
					}
					delete_post_meta($post->ID, SERIES_PART_KEY);
				}
			}
			update_option('publishpress_multi_series_supported', true);
			update_option('os_multi_import', true);
			add_filter('removable_query_args', function ($args) {
				return array_merge($args, [
					'series_action',
					'nonce',
				]);
			});
			add_action('admin_notices', function () {
				?>
				<div class="notice notice-success is-dismissible">
					<p>
						<?php esc_html_e('Series upgrade completed.', 'organize-series'); ?>
					</p>
				</div>
				<?php
			});
	}
}

function publishpress_series_sync_menu_order() {
	global $wpdb;
	if (isset($_REQUEST['series_action'])
		&& isset($_REQUEST['nonce'])
		&& $_REQUEST['series_action'] === 'sync-menu-order'
		&& wp_verify_nonce(sanitize_key($_REQUEST['nonce']), 'sync-menu-order-action')
		&& current_user_can('manage_publishpress_series')
	) {
		$updated_count = 0;
		
		// Get all series
		$all_series = get_terms(array(
			'taxonomy' => ppseries_get_series_slug(),
			'hide_empty' => false,
		));
		
		if (!empty($all_series) && !is_wp_error($all_series)) {
			foreach ($all_series as $series) {
				$series_id = $series->term_id;
				$part_key = apply_filters('orgseries_part_key', SERIES_PART_KEY, $series_id);
				
				// Get all posts in this series with their part numbers
				$query = $wpdb->prepare(
					"SELECT p.ID, pm.meta_value as series_part 
					FROM {$wpdb->posts} p 
					INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id 
					INNER JOIN {$wpdb->term_relationships} tr ON p.ID = tr.object_id 
					INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id 
					WHERE pm.meta_key = %s 
					AND tt.term_id = %d 
					AND tt.taxonomy = %s 
					AND p.post_status = 'publish'",
					$part_key,
					$series_id,
					ppseries_get_series_slug()
				);
				
				$posts = $wpdb->get_results($query);
				
				if (!empty($posts)) {
					foreach ($posts as $post) {
						$series_part = (int) $post->series_part;
						
						// Update menu_order to match series part
						$result = $wpdb->update(
							$wpdb->posts,
							array('menu_order' => $series_part),
							array('ID' => $post->ID),
							array('%d'),
							array('%d')
						);
						
						if ($result !== false) {
							$updated_count++;
							clean_post_cache($post->ID);
						}
					}
				}
			}
		}
		
		add_filter('removable_query_args', function ($args) {
			return array_merge($args, [
				'series_action',
				'nonce',
			]);
		});
		
		add_action('admin_notices', function () use ($updated_count) {
			?>
			<div class="notice notice-success is-dismissible">
				<p>
					<?php 
					printf(
						esc_html__('Series order sync completed. %d posts updated.', 'organize-series'),
						$updated_count
					);
					?>
				</p>
			</div>
			<?php
		});
	}
}
