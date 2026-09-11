<?php

/**
 * Layouts tab - section registration.
 *
 * @package Publishpress Series
 */

add_settings_section(
    'series_layouts_settings',
    __('Layouts', 'organize-series'),
    'orgseries_layouts_section',
    'orgseries_options_page'
);

function orgseries_layouts_section()
{
    ?>
    <p class="description"><?php esc_html_e('Configure the layout displays shown on posts in a series.', 'organize-series'); ?></p>
    <?php
}
