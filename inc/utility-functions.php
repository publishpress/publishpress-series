<?php

defined('ABSPATH') || exit;

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

        // Load post-list-box addon (shared by Free and Pro - Pro extends via hooks)
        if (file_exists(SERIES_PATH . 'addons/post-list-box/init.php')) {
            require_once SERIES_PATH . 'addons/post-list-box/init.php';
        }

        // Register the Gutenberg blocks before Pro adds its renderer extensions.
        if (file_exists(SERIES_PATH . 'includes-core/blocks.php')) {
            require_once SERIES_PATH . 'includes-core/blocks.php';
        }

        // Skip loading these Free addons when Pro is active (Pro has its own versions)
        if (defined('PUBLISHPRESS_SERIES_PRO_LOADED')) {
            return;
        }

        // Load post-details addon
        if (file_exists(SERIES_PATH . 'addons/post-details/init.php')) {
            require_once SERIES_PATH . 'addons/post-details/init.php';
        }

        // Load post-navigation addon
        if (file_exists(SERIES_PATH . 'addons/post-navigation/init.php')) {
            require_once SERIES_PATH . 'addons/post-navigation/init.php';
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
            $table_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $wpdb->esc_like($table_name))) === $table_name;
            if (!$table_exists) {
                //create table for series icons
                $sql = "CREATE TABLE $table_name (
                term_id INT NOT NULL,
                icon TEXT NOT NULL,
                PRIMARY KEY  (term_id)
            )";
                require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
                dbDelta($sql);
                add_option('series_icon_path', '', '', 'no');
                add_option('series_icon_url', '', '', 'no');
                add_option('series_icon_filetypes', 'jpg gif jpeg png', '', 'no');

                $table_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $wpdb->esc_like($table_name))) === $table_name;
            }

            if ($table_exists) {
                update_option('pp_series_2_11_1_upgraded', true);
            }
        }

        /**
         * Upgrade icon column from VARCHAR(100) to TEXT.
         *
         * dbDelta() handles both new tables and existing tables. The option is
         * only set after verifying that the column has the new type, so a
         * failed database operation can be retried on the next admin request.
         */
        if (!get_option('pp_series_3_1_3_upgraded')) {
            $table_name = $wpdb->prefix . "orgseriesicons";

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            $sql = "CREATE TABLE $table_name (
                term_id INT NOT NULL,
                icon TEXT NOT NULL,
                PRIMARY KEY  (term_id)
            )";
            dbDelta($sql);

            if ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $wpdb->esc_like($table_name))) === $table_name) {
                $column = $wpdb->get_row(
                    $wpdb->prepare(
                        "SHOW COLUMNS FROM `$table_name` LIKE %s", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is the WordPress prefix plus a fixed plugin suffix.
                        'icon'
                    )
                );

                if (
                    $column
                    && isset($column->Type)
                    && in_array(strtolower($column->Type), array('text', 'mediumtext', 'longtext'), true)
                ) {
                    update_option('pp_series_3_1_3_upgraded', true);
                }
            }
        }
    }
}

if (!function_exists('pp_series_upgrade_new_multisite_blog')) {
    /**
     * Create plugin tables and options when a new multisite blog is added.
     *
     * @param WP_Site $new_site New site object.
     * @return void
     */
    function pp_series_upgrade_new_multisite_blog($new_site)
    {
        if (!is_multisite() || empty($new_site->blog_id) || !pp_series_is_network_active()) {
            return;
        }

        switch_to_blog((int) $new_site->blog_id);
        pp_series_upgrade_function();
        restore_current_blog();
    }

    // Run after core's wp_initialize_site (priority 10) has created the new site's tables.
    add_action('wp_initialize_site', 'pp_series_upgrade_new_multisite_blog', 20);
}

if (!function_exists('pp_series_is_network_active')) {
    /**
     * Whether the plugin that loaded Series (Free, or Pro bundling it) is network-active.
     *
     * A new site only gets Series set up when the plugin runs on every site;
     * otherwise the plugin's activation hook sets the site up when it is activated there.
     *
     * @return bool
     */
    function pp_series_is_network_active()
    {
        if (!is_multisite() || !defined('SERIES_FILE_PATH')) {
            return false;
        }

        $plugin_dir = strtok(plugin_basename(SERIES_FILE_PATH), '/');
        $network_plugins = (array) get_site_option('active_sitewide_plugins', array());

        foreach (array_keys($network_plugins) as $plugin_file) {
            if (strtok($plugin_file, '/') === $plugin_dir) {
                return true;
            }
        }

        return false;
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
            if (file_exists(get_stylesheet_directory() . '/' . $template_name)) {
                $located = get_stylesheet_directory() . '/' . $template_name;
                break;
            } elseif (file_exists(get_template_directory() . '/' . $template_name)) {
                $located = get_template_directory() . '/' . $template_name;
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
                <?php
                // Core adds the viewport tag only when it renders a block template itself.
                if (!has_action('wp_head', '_block_template_viewport_meta_tag')) : ?>
                <meta name="viewport" content="width=device-width, initial-scale=1" />
                <?php endif; ?>
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

if (! function_exists('pps_get_editable_layout_post')) {
    function pps_get_editable_layout_post($post_id, $post_type)
    {
        $post_id = (int) $post_id;
        if ($post_id < 1 || ! is_string($post_type) || $post_type === '') {
            return null;
        }

        $post = get_post($post_id);
        if (! $post || $post->post_type !== $post_type) {
            return null;
        }

        if (! current_user_can('manage_publishpress_series') || ! current_user_can('edit_post', $post_id)) {
            return null;
        }

        return $post;
    }
}

if (! function_exists('pps_sanitize_choice')) {
    function pps_sanitize_choice($value, array $allowed, $default)
    {
        if (! is_scalar($value)) {
            return $default;
        }

        $value = (string) $value;

        return in_array($value, $allowed, true) ? $value : $default;
    }
}

if (! function_exists('pps_sanitize_css_color')) {
    function pps_sanitize_css_color($value, $allow_transparent = false)
    {
        if ($allow_transparent && $value === 'transparent') {
            return 'transparent';
        }

        if (! is_string($value)) {
            return '';
        }

        $hex = sanitize_hex_color($value);

        return $hex ? $hex : '';
    }
}

if (! function_exists('pps_sanitize_layout_settings')) {
    function pps_sanitize_layout_settings($settings, $fields)
    {
        if (! is_array($settings) || ! is_array($fields)) {
            return [];
        }

        $clean = [];

        foreach ($fields as $key => $args) {
            if (! is_array($args)) {
                continue;
            }
            if (isset($args['type']) && $args['type'] === 'category_separator') {
                continue;
            }
            if (! array_key_exists($key, $settings)) {
                continue;
            }

            $value   = $settings[$key];
            $type    = isset($args['type']) ? $args['type'] : 'text';
            $default = isset($args['default']) ? $args['default'] : '';

            if ($type === 'select' && ! empty($args['options']) && is_array($args['options'])) {
                $value = is_scalar($value) ? (string) $value : '';
                $clean[$key] = array_key_exists($value, $args['options']) ? $value : $default;
                continue;
            }

            if ($type === 'color') {
                $allow_transparent = ($default === 'transparent');
                $color = pps_sanitize_css_color($value, $allow_transparent);
                $clean[$key] = $color !== '' ? $color : $default;
                continue;
            }

            if (isset($args['sanitize'])) {
                if (is_array($args['sanitize'])) {
                    foreach ($args['sanitize'] as $sanitize_cb) {
                        $value = is_array($value) ? map_deep($value, $sanitize_cb) : call_user_func($sanitize_cb, $value);
                    }
                    $clean[$key] = $value;
                } else {
                    $sanitize_cb = $args['sanitize'];
                    $clean[$key] = is_array($value) ? $value : call_user_func($sanitize_cb, $value);
                }
                continue;
            }

            $clean[$key] = is_array($value) ? $value : sanitize_text_field($value);
        }

        return $clean;
    }
}

if (! function_exists('pps_sanitize_optional_absint')) {
    /**
     * Sanitize an optional non-negative integer: an empty value stays empty ("not set").
     *
     * Used for per-side spacing overrides, where empty means "use the all-sides value".
     *
     * @param mixed $value Raw value.
     *
     * @return int|string
     */
    function pps_sanitize_optional_absint($value)
    {
        if (is_array($value) || '' === trim((string) $value)) {
            return '';
        }

        return absint($value);
    }
}

/*
 * Front-end accessibility helpers.
 *
 * These only add screen-reader text or ARIA attributes. They do not change how
 * existing templates look.
 */

if (! function_exists('ppseries_sr_only_style')) {
    /**
     * Inline clip styles for hidden text, used in case the theme and the plugin CSS
     * do not define .screen-reader-text.
     *
     * @return string
     */
    function ppseries_sr_only_style()
    {
        return 'border:0;clip:rect(1px,1px,1px,1px);clip-path:inset(50%);height:1px;margin:-1px;overflow:hidden;padding:0;position:absolute;width:1px;word-wrap:normal;';
    }
}

if (! function_exists('ppseries_is_plain_text_context')) {
    /**
     * Whether output is being built for a context that strips tags (auto-excerpts, REST),
     * where visually hidden text would show up as plain words.
     *
     * @return bool
     */
    function ppseries_is_plain_text_context()
    {
        return doing_filter('get_the_excerpt') || (function_exists('wp_is_json_request') && wp_is_json_request());
    }
}

if (! function_exists('ppseries_sr_text')) {
    /**
     * Build a visually hidden span for screen readers.
     *
     * @param string $text Plain text. It is escaped here.
     *
     * @return string
     */
    function ppseries_sr_text($text)
    {
        if ('' === (string) $text || ppseries_is_plain_text_context()) {
            return '';
        }

        return '<span class="screen-reader-text" style="' . esc_attr(ppseries_sr_only_style()) . '">' . esc_html($text) . '</span>';
    }
}

if (! function_exists('ppseries_heading_level')) {
    /**
     * Heading level for a hard-coded heading in a front-end output.
     *
     * Filter: ppseries_heading_level( int $level, string $context ).
     * Contexts: 'widget_post_list', 'block_series', 'post_list_box', 'post_navigation',
     * 'series_group'.
     *
     * @param int    $default Level used before this filter existed.
     * @param string $context Where the heading is printed.
     *
     * @return int A level from 1 to 6.
     */
    function ppseries_heading_level($default, $context = '')
    {
        $level = (int) apply_filters('ppseries_heading_level', (int) $default, (string) $context);

        if ($level < 1 || $level > 6) {
            $level = (int) $default;
        }

        return $level;
    }
}

if (! function_exists('ppseries_hide_nav_arrows')) {
    /**
     * Wrap decorative arrow text (<<, >>, «, ») in aria-hidden spans.
     *
     * Only text between tags is changed, so attributes and markup in custom
     * templates are left alone. Arrows that are already wrapped are skipped.
     *
     * @param string $html Navigation HTML.
     *
     * @return string
     */
    function ppseries_hide_nav_arrows($html)
    {
        $html = (string) $html;

        if ('' === $html) {
            return $html;
        }

        $arrow_pattern = '/(?:&lt;&lt;|&gt;&gt;|<<|>>|&laquo;|&raquo;|&#0*171;|&#0*187;|&#x0*ab;|&#x0*bb;|\x{00AB}|\x{00BB})+/iu';

        // Quick exit when there is no arrow at all.
        if (!preg_match($arrow_pattern, $html)) {
            return $html;
        }

        // Split on real tags only: "<" followed by a letter, "/" or "!". A literal "<<" is text.
        // Quoted attribute values may contain ">".
        $parts = preg_split('/(<[a-zA-Z\/!](?:[^>"\']|"[^"]*"|\'[^\']*\')*>)/u', $html, -1, PREG_SPLIT_DELIM_CAPTURE);

        if (!is_array($parts)) {
            return $html;
        }

        $raw_text_tag = '';
        $marker       = 'ppseries-nav-arrow';

        foreach ($parts as $index => $part) {
            if ($index % 2 === 1) {
                // A tag. Track <script>/<style> so their contents are never touched.
                if (preg_match('/^<(script|style)\b/i', $part, $m)) {
                    $raw_text_tag = strtolower($m[1]);
                } elseif ($raw_text_tag && preg_match('/^<\/' . $raw_text_tag . '\b/i', $part)) {
                    $raw_text_tag = '';
                }
                continue;
            }

            if ('' === $part || $raw_text_tag) {
                continue;
            }

            // Already wrapped by an earlier pass.
            if ($index > 0 && false !== strpos($parts[$index - 1], $marker)) {
                continue;
            }

            $replaced = preg_replace_callback(
                $arrow_pattern,
                function ($match) use ($marker) {
                    $arrow = str_replace(array('<', '>'), array('&lt;', '&gt;'), $match[0]);
                    return '<span class="' . $marker . '" aria-hidden="true">' . $arrow . '</span>';
                },
                $part
            );

            if (null !== $replaced) {
                $parts[$index] = $replaced;
            }
        }

        return implode('', $parts);
    }
}

if (! function_exists('ppseries_mark_current_post')) {
    /**
     * Mark the current post in a series post list.
     *
     * Adds aria-current="page" to the link that points to the current post. When the
     * current post is plain text, adds hidden "(current post)" text inside the item.
     *
     * @param string $html    The rendered list item.
     * @param int    $post_id The current post ID.
     *
     * @return string
     */
    function ppseries_mark_current_post($html, $post_id)
    {
        $html = (string) $html;

        if ('' === trim($html) || false !== stripos($html, 'aria-current=') || false !== strpos($html, 'ppseries-current-label')) {
            return $html;
        }

        $permalink = $post_id ? get_permalink($post_id) : '';

        if ($permalink) {
            $target  = untrailingslashit($permalink);
            $matched = false;
            $marked  = preg_replace_callback(
                '/<a\b((?:[^>"\']|"[^"]*"|\'[^\']*\')*)>/i',
                function ($m) use ($target, &$matched) {
                    if ($matched) {
                        return $m[0];
                    }
                    if (preg_match('/\bhref\s*=\s*(["\'])(.*?)\1/i', $m[1], $href)
                        && untrailingslashit(html_entity_decode($href[2], ENT_QUOTES)) === $target
                    ) {
                        $matched = true;
                        return '<a aria-current="page"' . $m[1] . '>';
                    }
                    return $m[0];
                },
                $html
            );

            if ($matched && null !== $marked) {
                return $marked;
            }
        }

        if (ppseries_is_plain_text_context()) {
            return $html;
        }

        $label = '<span class="screen-reader-text ppseries-current-label" style="' . esc_attr(ppseries_sr_only_style()) . '"> '
            . esc_html__('(current post)', 'organize-series') . '</span>';

        $pos = strripos($html, '</li>');
        if (false !== $pos) {
            return substr_replace($html, $label, $pos, 0);
        }

        return $html . $label;
    }
}

if (! function_exists('ppseries_html_is_balanced')) {
    /**
     * Whether every element opened in an HTML fragment is also closed in it.
     *
     * @param string $html
     *
     * @return bool
     */
    function ppseries_html_is_balanced($html)
    {
        $void  = ['area', 'base', 'br', 'col', 'embed', 'hr', 'img', 'input', 'link', 'meta', 'source', 'track', 'wbr'];
        $stack = [];

        if (! preg_match_all('#<(/?)([a-z][a-z0-9-]*)\b[^>]*?(/?)>#i', (string) $html, $matches, PREG_SET_ORDER)) {
            return true;
        }

        foreach ($matches as $tag) {
            $name = strtolower($tag[2]);

            if ('/' === $tag[3] || in_array($name, $void, true)) {
                continue;
            }

            if ('' === $tag[1]) {
                $stack[] = $name;
            } elseif (empty($stack) || array_pop($stack) !== $name) {
                return false;
            }
        }

        return empty($stack);
    }
}

if (! function_exists('ppseries_wrap_nav_landmark')) {
    /**
     * Wrap series navigation HTML in a navigation landmark.
     *
     * Uses a plain div with role="navigation" so theme styles for <nav> do not apply.
     * The %postcontent% token is kept outside the wrapper.
     *
     * @param string $html  Navigation HTML, may contain %postcontent%.
     * @param string $label Optional accessible name. Defaults to "Series navigation".
     *
     * @return string
     */
    function ppseries_wrap_nav_landmark($html, $label = '')
    {
        $html = (string) $html;

        if ('' === trim($html) || false !== strpos($html, 'ppseries-nav-landmark')) {
            return $html;
        }

        if ('' === (string) $label) {
            $label = __('Series navigation', 'organize-series');
        }
        $open     = '<div class="ppseries-nav-landmark" role="navigation" aria-label="' . esc_attr($label) . '">';
        $segments = explode('%postcontent%', $html);

        // A template can open an element before %postcontent% and close it after. Wrapping the
        // pieces separately would then break the markup, so leave such templates untouched.
        foreach ($segments as $segment) {
            if (! ppseries_html_is_balanced($segment)) {
                return $html;
            }
        }

        foreach ($segments as $i => $segment) {
            if ('' !== trim(wp_strip_all_tags($segment, true)) || preg_match('/<(img|svg|a)\b/i', $segment)) {
                $segments[$i] = $open . $segment . '</div>';
            }
        }

        return implode('%postcontent%', $segments);
    }
}
