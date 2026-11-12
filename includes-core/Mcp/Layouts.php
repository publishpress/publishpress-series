<?php

/** Layout discovery and writes reuse native field definitions and sanitizers. */

namespace PublishPress\Series\Mcp;

\defined('ABSPATH') || exit;
class Layouts
{
    public static function modules(): array
    {
        return [
            'post-list-box' => ['type' => 'pps_post_list_box', 'fields' => '\PPS_Post_List_Box_Fields', 'prefix' => \PPS_Post_List_Box_Fields::META_PREFIX, 'reader' => ['\PPS_Post_List_Box_Fields', 'get_post_list_box_layout_meta_values'], 'selection' => 'series_post_list_box_selection', 'filter' => 'pps_post_list_box_field_pro_locked'],
            'post-details' => ['type' => 'pps_post_details', 'fields' => '\PPS_Series_Post_Details_Fields', 'prefix' => \PPS_Series_Post_Details_Utilities::META_PREFIX, 'reader' => ['\PPS_Series_Post_Details_Utilities', 'get_post_details_settings'], 'selection' => 'series_post_details_selection', 'filter' => 'pps_series_post_details_field_pro_locked'],
            'post-navigation' => ['type' => 'pps_post_navigation', 'fields' => '\PPS_Series_Post_Navigation_Fields', 'prefix' => \PPS_Series_Post_Navigation_Utilities::META_PREFIX, 'reader' => ['\PPS_Series_Post_Navigation_Utilities', 'get_post_navigation_settings'], 'selection' => 'series_post_navigation_selection', 'filter' => 'pps_series_post_navigation_field_pro_locked'],
        ];
    }

    public static function readable(int $id, string $type): bool
    {
        $post = get_post($id);
        return $post && $post->post_type === $type && current_user_can('read_post', $id) && current_user_can('edit_post', $id);
    }

    public static function fields(string $module, int $id = 0): array
    {
        $definition = self::modules()[$module];
        $post = $id ? get_post($id) : new \WP_Post((object) ['ID' => 0, 'post_type' => $definition['type']]);
        $fields = $definition['fields']::get_fields($post);
        if ($module === 'post-details') {
            $fields = apply_filters('pps_series_post_details_fields', $fields, $post);
        }
        if ($module === 'post-navigation') {
            $fields = apply_filters('pps_series_post_navigation_fields', $fields, $post);
        }
        return array_filter($fields, static fn($field) => isset($field['type']) && !in_array($field['type'], ['category_separator', 'button', 'html'], true));
    }

    private static function locked(string $module, string $key, array $field): bool
    {
        $locks = $module === 'post-navigation' ? ppseries_get_post_navigation_pro_locked_fields() : [];
        $locked = !empty($field['pro_only']) || in_array($key, $locks, true);
        if ($module === 'post-details') {
            $locked = $locked || (!Registry::pro() && in_array($key, \PPS_Series_Post_Details_Utilities::get_pro_only_keys(), true));
        }
        return (bool) apply_filters(self::modules()[$module]['filter'], $locked, $key, $field, $locks);
    }

    public static function schema(array $field): array
    {
        $type = $field['type'];
        if ($type === 'checkbox') {
            return ['type' => 'boolean'];
        }
        if ($type === 'select') {
            return ['type' => 'string', 'enum' => array_map('strval', array_keys($field['options'] ?? []))];
        }
        if (in_array($type, ['number', 'media'], true)) {
            $schema = ['type' => 'integer', 'minimum' => (int) ($field['min'] ?? 0)];
            if (isset($field['max'])) {
                $schema['maximum'] = (int) $field['max'];
            }
            if (($field['sanitize'] ?? '') === 'pps_sanitize_optional_absint') {
                $schema['type'] = ['integer', 'string'];
                $schema['pattern'] = '^$';
            }
            return $schema;
        }
        if ($type === 'color') {
            return ['type' => 'string', 'pattern' => ($field['default'] ?? '') === 'transparent' ? '^(transparent|#[a-fA-F0-9]{3}([a-fA-F0-9]{3})?)$' : (($field['default'] ?? '') === '' ? '^(|#[a-fA-F0-9]{3}([a-fA-F0-9]{3})?)$' : '^(#[a-fA-F0-9]{3}([a-fA-F0-9]{3})?)$')];
        }
        return Registry::text(10000);
    }

    public static function register(): void
    {
        $modules = ['type' => 'string', 'enum' => array_keys(self::modules())];
        $output = ['type' => 'object'];
        Registry::add('get-layout', __('Read Series layout fields and settings', 'organize-series'), __('Inspect a Post List Box, Post Details or Post Navigation layout. Omit id to discover native fields/defaults for a new layout. Includes exact JSON schema, native labels and edition locks. Use list-layouts to find IDs.', 'organize-series'), Registry::object(['module' => $modules, 'id' => Registry::id()], ['module']), [self::class, 'read'], static fn() => current_user_can('manage_publishpress_series'), false, false, $output);
        Registry::add('list-layouts', __('Find Series layouts', 'organize-series'), __('List readable native Series layouts, including trash when requested.', 'organize-series'), Registry::object(['module' => $modules, 'trash' => ['type' => 'boolean']] + Registry::page(), ['module']), [self::class, 'listing'], static fn() => current_user_can('manage_publishpress_series'), false, false, $output);
        $union = [];
        foreach (array_keys(self::modules()) as $module) {
            foreach (self::fields($module) as $key => $field) {
                // The selected module is validated against its exact native schema again on execution.
                $union[$key] = ['type' => ['string', 'integer', 'boolean']];
            }
        }
        Registry::add('save-layout', __('Create or update a Series layout', 'organize-series'), __('Create a published layout or patch one by id and expected_hash. Use get-layout for exact per-module field types and locks. JSON booleans for checkboxes, integers for sizes and image IDs, strings for select choices. Omitted fields and unrelated metadata remain unchanged. dry_run previews.', 'organize-series'), Registry::object(['module' => $modules, 'id' => Registry::id(), 'title' => Registry::text(200), 'settings' => Registry::object($union), 'expected_hash' => Registry::revision(), 'dry_run' => ['type' => 'boolean']], ['module', 'settings']), [self::class, 'save'], static fn() => current_user_can('manage_publishpress_series'), true, true, $output);
        Registry::add('transition-layout', __('Trash, restore or delete a Series layout', 'organize-series'), __('Native layout lifecycle. Read id and expected_hash first. Permanent deletion requires prior trash and native delete permission. dry_run previews.', 'organize-series'), Registry::object(['module' => $modules, 'id' => Registry::id(), 'expected_hash' => Registry::revision(), 'operation' => ['type' => 'string', 'enum' => ['trash', 'untrash', 'delete']], 'dry_run' => ['type' => 'boolean']], ['module', 'id', 'expected_hash', 'operation']), [self::class, 'transition'], static fn() => current_user_can('manage_publishpress_series'), true, true, $output);
    }

    private static function state(int $id, array $module): array
    {
        $post = get_post($id);
        return [$post ? (array) $post : null, get_post_meta($id, $module['prefix'] . 'layout_meta_value', true)];
    }

    public static function read(array $input)
    {
        $id = $input['id'] ?? 0;
        $module = self::modules()[$input['module']];
        if ($id && !self::readable($id, $module['type'])) {
            return Registry::denied();
        }
        $fields = $settings = [];
        $native = call_user_func($module['reader'], $id);
        foreach (self::fields($input['module'], $id) as $key => $field) {
            $schema = self::schema($field);
            $value = $native[$key] ?? $field['default'] ?? '';
            $value = Registry::project($value, $schema);
            $settings[$key] = $value;
            $fields[$key] = ['label' => $field['label'] ?? $key, 'description' => $field['description'] ?? '', 'schema' => $schema, 'default' => array_key_exists('default', $field) ? Registry::project($field['default'], $schema) : null, 'locked' => self::locked($input['module'], $key, $field)];
        }
        $post = $id ? get_post($id) : null;
        return ['id' => $id, 'module' => $input['module'], 'title' => $post ? $post->post_title : '', 'status' => $post ? $post->post_status : 'new', 'settings' => (object) $settings, 'fields' => (object) $fields, 'expected_hash' => Registry::hash(self::state($id, $module))];
    }

    public static function listing(array $input)
    {
        $module = self::modules()[$input['module']];
        // phpcs:ignore WordPress.WP.PostsPerPage.posts_per_page_posts_per_page -- Bounded ID-only scan before object permission filtering.
        $posts = get_posts(['fields' => 'ids', 'post_type' => $module['type'], 'post_status' => !empty($input['trash']) ? 'trash' : ['publish', 'draft', 'pending'], 'posts_per_page' => 1001, 'orderby' => 'ID', 'order' => 'ASC']);
        if (count($posts) > 1000) {
            return Registry::error(__('Use native administration for more than 1,000 layouts.', 'organize-series'), 413);
        }
        $items = [];
        foreach ($posts as $postId) {
            $post = get_post($postId);
            if ($post && self::readable($post->ID, $module['type'])) {
                $items[] = ['id' => $post->ID, 'title' => $post->post_title, 'status' => $post->post_status];
            }
        }
        $size = $input['per_page'] ?? 20;
        $offset = (($input['page'] ?? 1) - 1) * $size;
        return ['items' => array_slice($items, $offset, $size), 'has_more' => count($items) > $offset + $size];
    }

    public static function save(array $input)
    {
        $id = $input['id'] ?? 0;
        $name = $input['module'];
        $module = self::modules()[$name];
        $type = get_post_type_object($module['type']);
        if (!$type || ($id && (!pps_get_editable_layout_post($id, $module['type']) || !Registry::editable($id) || !self::readable($id, $module['type']))) || (!$id && (!current_user_can($type->cap->create_posts) || !current_user_can($type->cap->publish_posts)))) {
            return Registry::denied();
        }
        if (!$id && empty($input['title'])) {
            return Registry::error(__('Supply a title for the new layout.', 'organize-series'));
        }
        if ($id && is_wp_error($check = Registry::stale($input, self::state($id, $module)))) {
            return $check;
        }
        if ($id && get_post_status($id) === 'trash') {
            return Registry::error(__('Restore the layout before editing.', 'organize-series'));
        }
        $fields = self::fields($name, $id);
        $schemas = [];
        foreach ($fields as $key => $field) {
            $schemas[$key] = self::schema($field);
        }
        $patch = Registry::validate($input['settings'], Registry::object($schemas));
        if (is_wp_error($patch)) {
            return $patch;
        }
        foreach ($patch as $key => $value) {
            if (self::locked($name, $key, $fields[$key])) {
                return Registry::error(__('This layout field requires an active Pro feature.', 'organize-series'), 403);
            }
            if ($fields[$key]['type'] === 'media' && $value && (!wp_attachment_is_image($value) || !current_user_can('read_post', $value))) {
                return Registry::denied();
            }
            if (is_bool($value)) {
                $patch[$key] = (int) $value;
            }
        }
        $raw = $id ? (array) get_post_meta($id, $module['prefix'] . 'layout_meta_value', true) : [];
        $clean = pps_sanitize_layout_settings($patch, $fields);
        $candidate = array_merge($raw, $clean);
        if ($input['dry_run'] ?? false) {
            return ['dry_run' => true, 'settings' => (object) $clean, 'title' => isset($input['title']) ? sanitize_text_field($input['title']) : get_the_title($id)];
        }
        if ($id && is_wp_error($check = Registry::stale($input, self::state($id, $module)))) {
            return $check;
        }
        if ($id && (!pps_get_editable_layout_post($id, $module['type']) || !Registry::editable($id))) {
            return Registry::denied();
        }
        $postData = ['post_type' => $module['type']];
        if (isset($input['title'])) {
            $postData['post_title'] = sanitize_text_field($input['title']);
        }
        if ($id) {
            $postData['ID'] = $id;
            $result = wp_update_post(wp_slash($postData), true);
        } else {
            $postData['post_status'] = 'publish';
            $result = wp_insert_post(wp_slash($postData), true);
        }
        if (is_wp_error($result)) {
            return $result;
        }
        $id = (int) $result;
        update_post_meta($id, $module['prefix'] . 'layout_meta_value', wp_slash($candidate));
        if (get_post_meta($id, $module['prefix'] . 'layout_meta_value', true) !== $candidate || (isset($postData['post_title']) && get_post_field('post_title', $id) !== $postData['post_title'])) {
            return ['complete' => false, 'id' => $id, 'message' => __('The layout exists but its settings were not fully confirmed. Read before retrying.', 'organize-series')];
        }
        return self::read(['module' => $name, 'id' => $id]);
    }

    public static function transition(array $input)
    {
        $id = $input['id'];
        $module = self::modules()[$input['module']];
        if (!self::readable($id, $module['type']) || !Registry::editable($id) || !current_user_can('delete_post', $id)) {
            return Registry::denied();
        }
        if (is_wp_error($check = Registry::stale($input, self::state($id, $module)))) {
            return $check;
        }
        $operation = $input['operation'];
        $trashed = get_post_status($id) === 'trash';
        if (($operation !== 'trash' && !$trashed) || ($operation === 'trash' && $trashed)) {
            return Registry::error(__('This lifecycle operation does not match the current layout state.', 'organize-series'), 409);
        }
        if ($input['dry_run'] ?? false) {
            return ['dry_run' => true, 'id' => $id, 'operation' => $operation];
        }
        $result = $operation === 'delete' ? wp_delete_post($id, true) : ($operation === 'trash' ? wp_trash_post($id) : wp_untrash_post($id));
        $complete = $operation === 'delete' ? !get_post($id) : ($operation === 'trash' ? get_post_status($id) === 'trash' : (get_post($id) && get_post_status($id) !== 'trash'));
        return $result && $complete ? ['complete' => true, 'id' => $id, 'operation' => $operation] : Registry::unconfirmed();
    }
}
