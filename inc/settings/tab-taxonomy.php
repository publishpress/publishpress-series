<?php
/**
 * Taxonomy tab - section and fieldset.
 *
 * Extracted from orgSeries-options.php during settings page modularization.
 *
 * @package Publishpress Series
 */

add_settings_section('series_taxonomy_base_settings', 'Taxonomy', 'orgseries_taxonomy_base_section', 'orgseries_options_page');
add_settings_field('series_taxonomy_base_core_fieldset', 'Taxonomy', 'series_taxonomy_base_core_fieldset', 'orgseries_options_page', 'series_taxonomy_base_settings');

function orgseries_taxonomy_base_section() {
	global $orgseries;
	?>
    <p class="description"><?php esc_html_e('This feature allows you to create a new taxonomy for this plugin to use if you don\'t want to use the default "Series" taxonomy.', 'organize-series'); ?></p>
	<?php
}

function series_taxonomy_base_core_fieldset() {
	global $orgseries;
	$org_opt = $orgseries->settings;
	$org_name = 'org_series_options';
	?>
	<table class="form-table ppseries-settings-table">
    	<tbody>

            <tr valign="top"><th scope="row"><label for="series_custom_base"><?php esc_html_e('Series Taxonomy Slug:', 'organize-series'); ?></label></th>
                <td><input type="text" name="<?php echo esc_attr($org_name); ?>[series_custom_base]" id="series_custom_base" value="<?php echo isset($org_opt['series_custom_base']) ? esc_attr(htmlspecialchars($org_opt['series_custom_base'])) : ''; ?>" /> <br />
                    <p class="description">
                        <?php esc_html_e('This text will be part of the series base URL.', 'organize-series'); ?>
                    </p>
                </td>
            </tr>

            <tr valign="top"><th scope="row"><label for="series_taxonomy_slug"><?php esc_html_e('Series Taxonomy:', 'organize-series'); ?></label></th>
                <td>
                    <input type="text" id="series_taxonomy_slug" name="<?php echo esc_attr($org_name); ?>[series_taxonomy_slug]" value="<?php echo isset($org_opt['series_taxonomy_slug']) ? esc_attr(htmlspecialchars($org_opt['series_taxonomy_slug'])) : ''; ?>"/>
                    <p class="description">
                        <?php esc_html_e('To create a new taxonomy, enter the new name and click the "Update Options" button.', 'organize-series'); ?>
                    </p>
                </td>
            </tr>
            
			<tr valign="top">
            	<th scope="row"><label for="migrate_series_terms">
                	    <?php esc_html_e('Migrate Terms', 'organize-series'); ?>
                	</label>
            	</th>
            	<td>
                    <label>
                        <input type="checkbox" name="migrate_series_terms" id="migrate_series_terms" value="1" />
                        <?php esc_html_e('Automatically migrate existing series terms to the new taxonomy', 'organize-series'); ?>
                    </label>
                    <p class="description">
                        <font color="red"><?php esc_html_e('If checked, all terms from the current taxonomy will be migrated to the new taxonomy when you change the taxonomy slug. If unchecked, only the taxonomy slug will be changed.', 'organize-series'); ?></font>
                    </p>
                </td>
        	</tr>
           

    </tbody>
	</table>	<?php
}
