<?php

if (!function_exists('orgSeries_dir')) {
    /**
     * Nifty function to get the name of the directory orgSeries is installed in.
     */
    function orgSeries_dir()
    {
        if (stristr(SERIES_FILE_PATH, '/')) {
            $orgdir = explode('/plugins/', dirname(SERIES_FILE_PATH));
        } else {
            $orgdir = explode('\\plugins\\', dirname(SERIES_FILE_PATH));
        }
        return str_replace('\\', '/', end($orgdir)); //takes care of MS slashes
    }
}


if (!function_exists('pp_series_free_version_init')) {
    function pp_series_free_version_init()
    {
        if (is_admin() && !defined('SERIES_PRO_VERSION')) {
            require_once(SERIES_PATH . '/includes-core/PPSeriesCoreAdmin.php');
            new \PublishPress\Series\PPSeriesCoreAdmin();
        }
    }
}

if (!function_exists('ppseries_settings_template_tabbed_callback')) {
    add_filter('ppseries_settings_series_templates_settings_tabbed', 'ppseries_settings_template_tabbed_callback');
    function ppseries_settings_template_tabbed_callback()
    {
        return true;
    }
}

if (!function_exists('pp_series_upgrade_function')) {
    //activation functions/codes
    function pp_series_upgrade_function()
    {
        global $wpdb;

        if (!get_option('pp_series_2_7_1_upgraded')) {
            if (function_exists('get_role')) {
                $role = get_role('administrator');
                if (null !== $role && !$role->has_cap('manage_publishpress_series')) {
                    $role->add_cap('manage_publishpress_series');
                }
            }
            update_option('pp_series_2_7_1_upgraded', true);
        }

        if (!get_option('pp_series_2_8_0_upgraded')) {
            $settings = get_option('org_series_options');
            $settings = apply_filters('org_series_settings', $settings);
            //add new series settings only if not fresh installation
            if ($settings) {
                $settings['metabox_show_post_title_in_widget'] = 0;
                $settings['metabox_show_add_new'] = 0;
                update_option('org_series_options', $settings);
            }
            update_option('pp_series_2_8_0_upgraded', true);
        }

        if (!get_option('pp_series_2_10_0_upgraded')) {
            $settings = get_option('org_series_options');
            $settings = apply_filters('org_series_settings', $settings);
            //add new series settings only if not fresh installation
            if ($settings) {
                $settings['limit_series_meta_to_single'] = 0;
                update_option('org_series_options', $settings);
            }
            update_option('pp_series_2_10_0_upgraded', true);
        }

        if (!get_option('pp_series_2_10_0_1_upgraded')) {
            if (!$wpdb->get_var("SHOW COLUMNS FROM `{$wpdb->terms}` LIKE 'term_order'")) {
                $wpdb->query("ALTER TABLE `{$wpdb->terms}` ADD `term_order` INT(11) NOT NULL DEFAULT 0;");
                update_option('pp_series_2_10_0_1_upgraded', true);
            }
        }

        if (!get_option('pp_series_2_11_1_upgraded')) {
            $table_name = $wpdb->prefix . "orgseriesicons";
            if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
                //create table for series icons
                $sql = "CREATE TABLE $table_name (
                term_id INT NOT NULL,
                icon VARCHAR(100) NOT NULL,
                PRIMARY KEY  (term_id)
            )";
                require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
                dbDelta($sql);
                add_option('series_icon_path', '');
                add_option('series_icon_url', '');
                add_option('series_icon_filetypes', 'jpg gif jpeg png');
            }
            update_option('pp_series_2_11_1_upgraded', true);
        }


    }
}

if (!function_exists('pp_series_locate_template')) {
    /**
     * Check if template exist in theme/child theme
     *
     * We wouldn't use wordpress locate_template()
     * as it support theme compact which load
     * default template for files like sidebar.php
     * even if it doesn't exist in theme
     *
     * @param array $template
     * @return void
     */
    function pp_series_locate_template($template_names)
    {
        $located = false;
        foreach ((array) $template_names as $template_name) {
            if (!$template_name) {
                continue;
            }
            if (file_exists(STYLESHEETPATH . '/' . $template_name)) {
                $located = STYLESHEETPATH . '/' . $template_name;
                break;
            } elseif (file_exists(TEMPLATEPATH . '/' . $template_name)) {
                $located = TEMPLATEPATH . '/' . $template_name;
                break;
            }
        }

        return $located;

    }
}


if (!function_exists('pp_series_is_block_theme')) {
    /**
     * Check if current active theme is block theme/support full site editing
     *
     * @return bool
     */
    function pp_series_is_block_theme()
    {
        $is_block_theme = false;

        if (
            function_exists('wp_is_block_theme')
            && function_exists('block_template_part')
            && wp_is_block_theme()
        ) {
            $is_block_theme = true;
        }

        return $is_block_theme;
    }
}


if (!function_exists('pp_series_get_block_theme_header')) {
    /**
     * Retreive block theme header
     *
     * @return string
     */
    function pp_series_get_block_theme_header()
    {
        $block_theme_header = '';

        if (pp_series_is_block_theme()) {
            $header_template_part = get_block_template(get_stylesheet() . '//header', 'wp_template_part');
            if ($header_template_part && isset($header_template_part->content)) {
                $block_theme_header = do_blocks($header_template_part->content);
            }
        }

        return $block_theme_header;
    }
}


if (!function_exists('pp_series_get_block_theme_footer')) {
    /**
     * Retreive block theme footer
     *
     * @return string
     */
    function pp_series_get_block_theme_footer()
    {
        $block_theme_footer = '';

        if (pp_series_is_block_theme()) {
            $footer_template_part = get_block_template(get_stylesheet() . '//footer', 'wp_template_part');
            if ($footer_template_part && isset($footer_template_part->content)) {
                $block_theme_footer = do_blocks($footer_template_part->content);
            }
        }

        return $block_theme_footer;
    }
}


if (!function_exists('pp_series_format_block_theme_header')) {
    /**
     * Format block theme header
     *
     * @return void
     */
    function pp_series_format_block_theme_header()
    {
        $fse_header = pp_series_get_block_theme_header();
        $fse_footer = pp_series_get_block_theme_footer(); //we need to get footer as well before wp_head() call to enable fse css generator
        ?>
        <!doctype html>
        <html <?php language_attributes(); ?>>
            <head>
                <meta charset="<?php bloginfo('charset'); ?>">
                <?php wp_head(); ?>
            </head>
            <body <?php body_class(); ?>>
                <?php wp_body_open(); ?>
                <div class="wp-site-blocks">
                    <?php echo $fse_header; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }
}


if (!function_exists('pp_series_format_block_theme_footer')) {
    /**
     * Format block theme footer
     *
     * @return void
     */
    function pp_series_format_block_theme_footer()
    {
        $fse_footer = pp_series_get_block_theme_footer(); ?>
                </div>
                <?php echo $fse_footer; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                        wp_footer(); ?>
            </body>
        </html><?php
    }
}


if (!function_exists('publishpress_multi_series_supported')) {
    /**
     * Check if multiple series part is supported
     *
     * @return bool
     */
    function publishpress_multi_series_supported()
    {
        return !empty(get_option('publishpress_multi_series_supported'));
    }
}