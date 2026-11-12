<?php

/** Typed Series settings using native defaults and sanitization. */

namespace PublishPress\Series\Mcp;

\defined('ABSPATH') || exit;
class Settings
{
    public static function allowed(): bool
    {
        $capability = apply_filters('option_page_capability_orgseries_options', 'manage_options');
        return self::canRead() && is_string($capability) && $capability !== '' && current_user_can($capability);
    }

    public static function canRead(): bool
    {
        return current_user_can('manage_publishpress_series');
    }

    public static function fields(): array
    {
        $fields = [];
        foreach (['auto_tag_toggle', 'auto_tag_nav_toggle', 'auto_tag_seriesmeta_toggle', 'metabox_show_add_new', 'metabox_show_post_title_in_widget', 'custom_css', 'limit_series_meta_to_single', 'kill_on_delete', 'series_group_plugin_show_image', 'series_group_plugin_show_description', 'series_group_plugin_show_series_items'] as $key) {
            $fields[$key] = ['type' => 'boolean'];
        }
        $choices = [
            'series_post_list_position' => ['default', 'top', 'bottom'],
            'series_metabox_position' => ['default', 'top', 'bottom'],
            'series_navigation_box_position' => ['default', 'top', 'bottom'],
            'series_css_tougle' => ['default', 'box', 'dark', 'light'],
            'series_overview_page_layout' => ['default', 'grid', 'list'],
            'metabox_series_order' => ['default', 'a-z', 'z-a'],
            'series_posts_orderby' => array_keys(ppseries_series_posts_orderby_sql()),
            'series_posts_order' => array_keys(ppseries_series_posts_order_sql()),
            'series_group_archive_render_mode' => ['theme', 'plugin'],
        ];
        foreach ($choices as $key => $values) {
            $fields[$key] = ['type' => 'string', 'enum' => $values];
        }
        foreach (['series_post_list_template', 'series_post_list_post_template', 'series_post_list_currentpost_template', 'series_meta_template', 'series_meta_excerpt_template', 'series_table_of_contents_box_template', 'series_post_nav_template', 'series_nextpost_nav_custom_text', 'series_prevpost_nav_custom_text', 'series_firstpost_nav_custom_text', 'latest_series_before_template', 'latest_series_inner_template', 'latest_series_after_template', 'series_toc_title'] as $key) {
            $fields[$key] = Registry::text(100000);
        }
        foreach (['series_custom_base', 'series_group_custom_base', 'series_toc_url'] as $key) {
            $fields[$key] = Registry::text(200);
        }
        foreach (['series_icon_width_series_page', 'series_icon_width_post_page', 'series_icon_width_latest_series', 'series_perp_toc'] as $key) {
            $fields[$key] = ['type' => 'integer', 'minimum' => 1, 'maximum' => 1000000000];
        }
        $fields['series_post_list_limit'] = ['type' => 'string', 'pattern' => '^(-?[0-9]+)?$'];
        $fields['series_overview_page_columns'] = ['type' => 'integer', 'minimum' => 1, 'maximum' => 6];
        $fields['series_group_plugin_columns'] = ['type' => 'integer', 'minimum' => 1, 'maximum' => 4];
        foreach (Layouts::modules() as $module) {
            $fields[$module['selection']] = ['type' => 'integer', 'minimum' => 0];
        }
        if (Registry::pro()) {
            $fields['enabled_pro_addons'] = Registry::listOf(['type' => 'string', 'enum' => array_keys(ppseries_get_pro_feature_definitions())], 4);
            if (self::addon('cpt')) {
                $types = array_values(array_diff(get_post_types(['show_ui' => true, 'public' => true]), ['series_group', 'wp_block', 'attachment']));
                $fields['post_types_for_series'] = Registry::listOf(['type' => 'string', 'enum' => $types]);
            }
            if (self::addon('extra-tokens')) {
                $fields['series_post_list_unpublished_post_template'] = Registry::text(100000);
            }
        }
        return $fields;
    }

    public static function addon(string $name): bool
    {
        global $orgseries;
        $enabled = $orgseries->settings['enabled_pro_addons'] ?? ['cpt', 'shortcodes', 'extra-tokens'];
        return Registry::pro() && in_array($name, (array) $enabled, true);
    }

    public static function register(): void
    {
        $output = ['type' => 'object'];
        Registry::add('get-plugin-settings', __('Read Series settings and choices', 'organize-series'), __('Discover native Series Settings: automatic displays, layouts, legacy templates, category archives, metabox, sorting, uninstall policy and active Pro features. Booleans use JSON true/false; layout IDs are integers. No license or account credentials are returned.', 'organize-series'), Registry::object(), [self::class, 'read'], [self::class, 'canRead'], false, false, $output);
        Registry::add('update-plugin-settings', __('Update Series settings', 'organize-series'), __('Patch the exact keys discovered by get-plugin-settings. Preserve omitted settings. Read expected_hash first. dry_run previews without saving. Pro addon and post-type changes take effect on the next request. Taxonomy identity migrations remain in native administration.', 'organize-series'), Registry::object(['settings' => Registry::object(self::fields()), 'expected_hash' => Registry::revision(), 'dry_run' => ['type' => 'boolean']], ['settings', 'expected_hash']), [self::class, 'update'], [self::class, 'allowed'], true, true, $output);
    }

    public static function read(array $input = []): array
    {
        global $orgseries;
        $raw = (array) get_option('org_series_options', []);
        $defaults = $orgseries->get_default_settings();
        $labels = [
            'auto_tag_toggle' => __('Display Series Post List Boxes?', 'organize-series'),
            'auto_tag_nav_toggle' => __('Display Series Navigation?', 'organize-series'),
            'auto_tag_seriesmeta_toggle' => __('Display Series Post Details?', 'organize-series'),
            'series_post_list_box_selection' => __('Post List Boxes Selection', 'organize-series'),
            'series_post_details_selection' => __('Post Details Selection', 'organize-series'),
            'series_post_navigation_selection' => __('Navigation Selection', 'organize-series'),
            'series_post_list_position' => __('Post List Box Position', 'organize-series'),
            'series_metabox_position' => __('Post Details Position', 'organize-series'),
            'series_navigation_box_position' => __('Navigation Position', 'organize-series'),
            'series_nextpost_nav_custom_text' => __('Next Post Text', 'organize-series'),
            'series_prevpost_nav_custom_text' => __('Previous Post Text', 'organize-series'),
            'series_firstpost_nav_custom_text' => __('First Post Text', 'organize-series'),
            'series_custom_base' => __('Series Taxonomy Slug:', 'organize-series'),
            'series_group_custom_base' => __('Series Categories URL Base:', 'organize-series'),
            'series_group_archive_render_mode' => __('Series Categories Archive Template:', 'organize-series'),
            'limit_series_meta_to_single' => __('Hide on archive screens', 'organize-series'),
            'metabox_show_add_new' => __('Show "Add New"', 'organize-series'),
            'metabox_show_post_title_in_widget' => __('Show "Post title in widget"', 'organize-series'),
        ];
        $values = $definitions = [];
        foreach (self::fields() as $key => $schema) {
            if ($key === 'kill_on_delete' && is_multisite() && (!is_super_admin() || !current_user_can('manage_network_options'))) {
                continue;
            }
            $value = $raw[$key] ?? $defaults[$key] ?? null;
            if ($key === 'kill_on_delete') {
                $value = ppseries_get_kill_on_delete();
            }
            if ($key === 'enabled_pro_addons') {
                $value = $value ?? array_values(array_filter(array_keys(ppseries_get_pro_feature_definitions()), [self::class, 'addon']));
                $value = array_values(array_intersect((array) $value, array_keys(ppseries_get_pro_feature_definitions())));
            }
            if ($key === 'post_types_for_series' && $value === null) {
                $value = ['post'];
            }
            if ($value !== null) {
                $value = Registry::project($value, $schema);
                $values[$key] = $value;
            }
            $definitions[$key] = ['label' => $labels[$key] ?? ucwords(str_replace('_', ' ', $key)), 'schema' => $schema, 'default' => array_key_exists($key, $defaults) ? Registry::project($defaults[$key], $schema) : null];
        }
        return ['settings' => (object) $values, 'fields' => (object) $definitions, 'pro' => Registry::pro(), 'expected_hash' => Registry::hash(self::state())];
    }

    private static function state(): array
    {
        return [(array) get_option('org_series_options', []), ppseries_get_kill_on_delete()];
    }

    public static function update(array $input)
    {
        global $orgseries;
        $state = self::state();
        if (is_wp_error($check = Registry::stale($input, $state))) {
            return $check;
        }
        $patch = Registry::validate($input['settings'], Registry::object(self::fields()));
        if (is_wp_error($patch)) {
            return $patch;
        }
        if (isset($patch['kill_on_delete']) && is_multisite() && (!current_user_can('manage_network_options') || !is_super_admin())) {
            return Registry::denied();
        }
        foreach ($patch as $key => $value) {
            if (is_string($value) && !current_user_can('unfiltered_html') && wp_kses_post($value) !== $value) {
                return Registry::error(__('This text requires unfiltered_html. Use safe post HTML.', 'organize-series'));
            }
            foreach (Layouts::modules() as $module) {
                if ($key === $module['selection'] && $value && (!Layouts::readable($value, $module['type']) || get_post_status($value) !== 'publish')) {
                    return Registry::denied();
                }
            }
            if (is_bool($value)) {
                $patch[$key] = (int) $value;
            }
        }
        // Native validation can derive compatibility fields; keep all unrelated stored keys.
        $merged = array_merge($state[0], $patch);
        $clean = orgseries_validate(wp_slash($merged), false);
        $candidate = array_merge($state[0], array_intersect_key($clean, $patch));
        if ($patch && isset($clean['last_modified'])) {
            $candidate['last_modified'] = $clean['last_modified'];
        }
        if (is_multisite()) {
            unset($candidate['kill_on_delete']);
        }
        if (isset($patch['series_group_custom_base'])) {
            $candidate['series_group_previous_base'] = $clean['series_group_previous_base'];
        }
        if (is_wp_error($check = Registry::stale($input, self::state()))) {
            return $check;
        }
        if ($input['dry_run'] ?? false) {
            return ['dry_run' => true, 'changes' => (object) array_intersect_key($candidate, $patch)];
        }
        if (!self::allowed()) {
            return Registry::denied();
        }
        if ($patch) {
            // Validation already ran without side effects; avoid a second form sanitizer pass.
            $priority = has_filter('sanitize_option_org_series_options', 'orgseries_validate');
            remove_filter('sanitize_option_org_series_options', 'orgseries_validate');
            try {
                update_option('org_series_options', $candidate);
            } finally {
                if ($priority !== false) {
                    add_filter('sanitize_option_org_series_options', 'orgseries_validate', $priority);
                }
            }
            if ((array) get_option('org_series_options') !== $candidate) {
                return Registry::unconfirmed();
            }
            if (array_key_exists('kill_on_delete', $patch) && is_multisite()) {
                update_site_option('pp_series_kill_on_delete', $patch['kill_on_delete']);
                if ((bool) ppseries_get_kill_on_delete() !== (bool) $patch['kill_on_delete']) {
                    return Registry::unconfirmed();
                }
            }
            $orgseries->settings = $candidate;
            if (array_intersect(array_keys($patch), ['series_custom_base', 'series_group_custom_base', 'series_toc_url'])) {
                update_option('pp_series_flush_rewrite_rules', 1);
            }
        }
        return self::read();
    }
}
