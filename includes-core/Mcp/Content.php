<?php

/** Native Series taxonomy, editorial membership, ordering and publishing operations. */

namespace PublishPress\Series\Mcp;

\defined('ABSPATH') || exit;
class Content
{
    public static function allowed(): bool
    {
        return current_user_can('manage_publishpress_series');
    }

    public static function register(): void
    {
        $output = ['type' => 'object'];
        $taxonomy = ['type' => 'string', 'enum' => ['series', 'category']];
        $fields = ['name' => Registry::text(200), 'slug' => Registry::text(200), 'description' => Registry::text(100000), 'parent' => ['type' => 'integer', 'minimum' => 0], 'icon_id' => ['type' => 'integer', 'minimum' => 0], 'order' => ['type' => 'integer', 'minimum' => 0], 'category_ids' => Registry::listOf(Registry::id())];
        $permit = [self::class, 'allowed'];
        Registry::add('get-content-choices', __('Discover Series features and identifiers', 'organize-series'), __('Discover the native series taxonomy, Series Categories, supported post types, publishing and active Pro addons. Layout discovery uses get-layout; settings use get-plugin-settings. Hub supplies generic post lookup.', 'organize-series'), Registry::object(), [self::class, 'choices'], $permit, false, false, $output);
        Registry::add('list-series', __('Find Series and Series Categories', 'organize-series'), __('Find native Series terms or Series Categories by name. Returns native term metadata and concurrency tokens without exposing post content.', 'organize-series'), Registry::object(['taxonomy' => $taxonomy, 'search' => Registry::text(200)] + Registry::page()), [self::class, 'listing'], $permit, false, false, $output);
        Registry::add('list-series-posts', __('Read posts in a Series', 'organize-series'), __('List readable posts with their native part numbers, pending order, status and date. Pagination filters unreadable objects first. Get-post-series supplies edit tokens for individual posts.', 'organize-series'), Registry::object(['id' => Registry::id()] + Registry::page(), ['id']), [self::class, 'postListing'], $permit, false, false, $output);
        Registry::add('get-series', __('Inspect a Series or category', 'organize-series'), __('Read name, description, icon, order, category assignments and expected_hash. Series Categories use taxonomy=category. Parent IDs apply to hierarchical categories.', 'organize-series'), Registry::object(['taxonomy' => $taxonomy, 'id' => Registry::id()], ['id']), [self::class, 'read'], $permit, false, false, $output);
        Registry::add('save-series', __('Create or update a Series or category', 'organize-series'), __('Create with name, or patch an existing id and expected_hash. Omitted fields remain unchanged. icon_id is a readable image attachment; zero clears the Series icon. category_ids replace Series category assignments; [] clears them. dry_run previews.', 'organize-series'), Registry::object(['taxonomy' => $taxonomy, 'id' => Registry::id(), 'expected_hash' => Registry::revision(), 'fields' => Registry::object($fields), 'dry_run' => ['type' => 'boolean']], ['fields']), [self::class, 'save'], $permit, true, true, $output);
        Registry::add('delete-series', __('Delete a Series or category', 'organize-series'), __('Delete a native taxonomy term without deleting its posts. Requires the current term and membership token and permission to edit all affected posts. dry_run previews. Large collections remain in native administration.', 'organize-series'), Registry::object(['taxonomy' => $taxonomy, 'id' => Registry::id(), 'expected_hash' => Registry::revision(), 'dry_run' => ['type' => 'boolean']], ['id', 'expected_hash']), [self::class, 'delete'], $permit, true, true, $output);
        Registry::add('get-post-series', __('Inspect a post’s Series and block assignments', 'organize-series'), __('Read Series membership, part numbers, widget short title and Series Gutenberg block attributes for a readable editable supported post. Returns expected_hash covering affected Series order and content.', 'organize-series'), Registry::object(['id' => Registry::id()], ['id']), [self::class, 'postRead'], $permit, false, false, $output);
        Registry::add('update-post-series', __('Set post Series, part numbers and widget title', 'organize-series'), __('Replace memberships with items; [] removes all. Omit items to update only short_title. Part zero uses native automatic placement. Multiple Series requires active Pro Multiple Series. All posts whose numbering can change require native read/edit permission. Read expected_hash from get-series before adding a new membership. dry_run previews.', 'organize-series'), self::postPatchSchema(), [self::class, 'postUpdate'], $permit, true, true, $output);
        $bulkRow = self::postPatchSchema();
        unset($bulkRow['properties']['dry_run']);
        Registry::add('bulk-update-post-series', __('Update Series memberships for multiple posts', 'organize-series'), __('Validate every explicit post, Series token and native permission before updating up to 20 posts. Each row uses the same input as update-post-series. Omitted fields are preserved. dry_run previews all rows. Native numbering is not transactional; inspect complete, applied_ids and failed_id before retrying a partial result.', 'organize-series'), Registry::object(['items' => Registry::listOf($bulkRow, 20) + ['minItems' => 1], 'dry_run' => ['type' => 'boolean']], ['items']), [self::class, 'bulkPostUpdate'], $permit, true, true, $output);
        Registry::add('manage-series-posts', __('Order, publish, schedule or unpublish Series posts', 'organize-series'), __('Native Series Order and Publish Series workflows. Get expected_hash from get-series. order requires the complete counted post set and never publishes drafts. pending-order requires complete unpublished set. publish accepts selected pending/draft posts; date is optional site-local YYYY-MM-DD HH:MM for scheduling. unpublish affects the entire Series. trash requires explicit post_ids and native deletion capabilities. mark-published changes only the Series marker. date-asc/date-desc reuse native publication-date ordering. Cross-Series status transitions require series_tokens from get-series for the other memberships. sync-menu-order copies part numbers to WordPress menu order. All affected objects are preflighted; native multi-step operations are not transactional. dry_run previews.', 'organize-series'), Registry::object(['id' => Registry::id(), 'expected_hash' => Registry::revision(), 'operation' => ['type' => 'string', 'enum' => ['order', 'date-asc', 'date-desc', 'pending-order', 'publish', 'unpublish', 'trash', 'mark-published', 'sync-menu-order']], 'post_ids' => Registry::listOf(Registry::id(), 1000), 'series_tokens' => Registry::listOf(Registry::object(['id' => Registry::id(), 'expected_hash' => Registry::revision()], ['id', 'expected_hash']), 50), 'date' => ['type' => 'string', 'pattern' => '^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$'], 'dry_run' => ['type' => 'boolean']], ['id', 'expected_hash', 'operation']), [self::class, 'manage'], $permit, true, true, $output);
        Registry::add('update-series-block', __('Add or configure a Series Gutenberg block', 'organize-series'), __('Append a native Series block or update one using its zero-based path from get-post-series. Only registered Series block attributes are accepted, with native editor availability and layout access checks. No arbitrary HTML or other block edits. dry_run previews.', 'organize-series'), Registry::object(['id' => Registry::id(), 'expected_hash' => Registry::revision(), 'block_name' => ['type' => 'string', 'enum' => array_keys(self::blockSchemas())], 'path' => ['type' => 'array', 'items' => ['type' => 'integer', 'minimum' => 0], 'maxItems' => 20], 'attributes' => ['type' => 'object'], 'dry_run' => ['type' => 'boolean']], ['id', 'expected_hash', 'block_name', 'attributes']), [self::class, 'blockUpdate'], $permit, true, true, $output);
    }

    private static function postPatchSchema(): array
    {
        $member = Registry::object(['series_id' => Registry::id(), 'part' => ['type' => 'integer', 'minimum' => 0, 'maximum' => 100000]], ['series_id']);
        return Registry::object(['id' => Registry::id(), 'expected_hash' => Registry::revision(), 'items' => Registry::listOf($member, 50), 'series_tokens' => Registry::listOf(Registry::object(['id' => Registry::id(), 'expected_hash' => Registry::revision()], ['id', 'expected_hash']), 50), 'short_title' => Registry::text(1000), 'dry_run' => ['type' => 'boolean']], ['id', 'expected_hash']);
    }

    private static function taxonomy(array $input): string
    {
        return ($input['taxonomy'] ?? 'series') === 'category' ? 'series_group' : ppseries_get_series_slug();
    }

    public static function choices(array $input): array
    {
        $tax = get_taxonomy(ppseries_get_series_slug());
        $features = [];
        foreach (ppseries_get_pro_feature_definitions() as $name => $definition) {
            $features[$name] = $definition + ['requires_pro' => true, 'enabled' => Settings::addon($name)];
        }
        return ['taxonomy' => $tax->name, 'category_taxonomy' => taxonomy_exists('series_group') ? 'series_group' : null, 'post_types' => $tax->object_type, 'timezone' => wp_timezone_string(), 'multiple_series' => Settings::addon('multiples') && class_exists('osMulti'), 'pro' => Registry::pro(), 'blocks' => (object) self::blockSchemas(), 'features' => (object) $features];
    }

    private static function term(array $input)
    {
        $tax = get_taxonomy(self::taxonomy($input));
        if (!$tax || !current_user_can($tax->cap->manage_terms)) {
            return Registry::denied();
        }
        $term = get_term($input['id'], $tax->name);
        return !$term || is_wp_error($term) ? Registry::error(__('This Series term does not exist.', 'organize-series'), 404) : $term;
    }

    private static function members(int $id, string $taxonomy)
    {
        global $wpdb;
        $ids = get_objects_in_term($id, $taxonomy);
        if ($wpdb->last_error) {
            return Registry::error(__('Series membership could not be read.', 'organize-series'), 500);
        }
        if (is_wp_error($ids)) {
            return $ids;
        }
        if (count($ids) > 1000) {
            return Registry::error(__('Use native administration for more than 1,000 affected records.', 'organize-series'), 413);
        }
        return array_map('intval', $ids);
    }

    private static function groupIds(int $id): array
    {
        $group = function_exists('orgseries_group_id') ? orgseries_group_id($id, false) : 0;
        $ids = $group ? wp_get_object_terms($group, 'series_group', ['fields' => 'ids']) : [];
        return is_wp_error($ids) ? [] : array_map('intval', $ids);
    }

    private static function termState(\WP_Term $term)
    {
        $ids = self::members($term->term_id, $term->taxonomy);
        if (is_wp_error($ids)) {
            return $ids;
        }
        if ($term->taxonomy === ppseries_get_series_slug()) {
            $cleanupIds = ppseries_get_order_meta_post_ids($term->term_id, 1001);
            if (is_wp_error($cleanupIds)) {
                return $cleanupIds;
            }
            $ids = array_values(array_unique(array_merge($ids, $cleanupIds)));
            if (count($ids) > 1000) {
                return Registry::error(__('Use native administration for more than 1,000 affected records.', 'organize-series'), 413);
            }
        }
        $posts = [];
        foreach ($ids as $id) {
            $post = get_post($id);
            $posts[] = [$id, $post ? Registry::hash((array) $post) : null, wp_get_post_series($id), get_post_meta($id, apply_filters('orgseries_part_key', SERIES_PART_KEY, $term->term_id), false), get_post_meta($id, apply_filters('orgseries_pending_part_key', '_pending_series_part', $term->term_id), false), get_post_meta($id, ppseries_series_return_key($term->term_id), true)];
        }
        $children = [];
        $tax = get_taxonomy($term->taxonomy);
        if ($tax && $tax->hierarchical) {
            $children = get_terms(['taxonomy' => $term->taxonomy, 'parent' => $term->term_id, 'hide_empty' => false, 'orderby' => 'term_id', 'number' => 1001]);
            if (is_wp_error($children)) {
                return $children;
            }
            if (count($children) > 1000) {
                return Registry::error(__('Use native administration for this category hierarchy.', 'organize-series'), 413);
            }
            $children = array_map(static fn($child) => (array) $child, $children);
        }
        return [(array) $term, $posts, $term->taxonomy === ppseries_get_series_slug() ? series_get_icons($term->term_id) : null, $term->taxonomy === ppseries_get_series_slug() ? self::groupIds($term->term_id) : [], in_array($term->term_id, (array) get_option('im_published_series')), in_array($term->term_id, (array) get_option('im_unpublished_series')), $children, wp_timezone_string()];
    }

    public static function read(array $input)
    {
        $term = self::term($input);
        if (is_wp_error($term)) {
            return $term;
        }
        $state = self::termState($term);
        if (is_wp_error($state)) {
            return $state;
        }
        $result = ['id' => $term->term_id, 'name' => $term->name, 'slug' => $term->slug, 'description' => $term->description, 'parent' => $term->parent, 'order' => (int) ($term->term_order ?? 0), 'expected_hash' => Registry::hash($state), 'timezone' => wp_timezone_string()];
        if ($term->taxonomy === ppseries_get_series_slug()) {
            $result['icon'] = (string) series_get_icons($term->term_id);
            $result['category_ids'] = self::groupIds($term->term_id);
            $result['published_marker'] = in_array($term->term_id, (array) get_option('im_published_series'));
            $result['unpublished_marker'] = in_array($term->term_id, (array) get_option('im_unpublished_series'));
        }
        return $result;
    }

    public static function listing(array $input)
    {
        $tax = get_taxonomy(self::taxonomy($input));
        if (!$tax || !current_user_can($tax->cap->manage_terms)) {
            return Registry::denied();
        }
        $size = $input['per_page'] ?? 20;
        $terms = get_terms(['taxonomy' => $tax->name, 'hide_empty' => false, 'search' => $input['search'] ?? '', 'number' => $size + 1, 'offset' => (($input['page'] ?? 1) - 1) * $size, 'orderby' => 'name']);
        if (is_wp_error($terms)) {
            return $terms;
        }
        $items = [];
        foreach (array_slice($terms, 0, $size) as $term) {
            $items[] = ['id' => $term->term_id, 'name' => $term->name, 'slug' => $term->slug];
        }
        return ['items' => $items, 'has_more' => count($terms) > $size];
    }

    public static function postListing(array $input)
    {
        $term = self::term($input);
        if (is_wp_error($term)) {
            return $term;
        }
        $ids = self::members($term->term_id, $term->taxonomy);
        if (is_wp_error($ids)) {
            return $ids;
        }
        $items = [];
        foreach ($ids as $id) {
            $post = get_post($id);
            if ($post && current_user_can('read_post', $id)) {
                $items[] = ['id' => $id, 'title' => $post->post_title, 'status' => $post->post_status, 'date' => $post->post_date, 'part' => (int) wp_series_part($id, $term->term_id), 'pending_part' => (int) get_post_meta($id, apply_filters('orgseries_pending_part_key', '_pending_series_part', $term->term_id), true)];
            }
        }
        usort($items, static fn($a, $b) => [$a['part'], $a['id']] <=> [$b['part'], $b['id']]);
        $size = $input['per_page'] ?? 20;
        $offset = (($input['page'] ?? 1) - 1) * $size;
        return ['items' => array_slice($items, $offset, $size), 'has_more' => count($items) > $offset + $size];
    }

    private static function affected(array $series, bool $countedOnly = false)
    {
        $ids = [];
        foreach (array_unique($series) as $id) {
            $members = self::members((int) $id, ppseries_get_series_slug());
            if (is_wp_error($members)) {
                return $members;
            }
            if ($countedOnly) {
                $members = array_values(array_filter($members, 'ppseries_is_counted_series_post'));
            }
            $ids = array_unique(array_merge($ids, $members));
        }
        if (count($ids) > 1000) {
            return Registry::error(__('Use native administration for more than 1,000 affected posts.', 'organize-series'), 413);
        }
        foreach ($ids as $id) {
            if (!Registry::editable($id)) {
                return Registry::denied();
            }
        }
        return array_values($ids);
    }

    public static function save(array $input)
    {
        $id = $input['id'] ?? 0;
        $tax = get_taxonomy(self::taxonomy($input));
        if (!$tax || !current_user_can($tax->cap->edit_terms) || !current_user_can($tax->cap->manage_terms)) {
            return Registry::denied();
        }
        $term = $id ? self::term($input) : null;
        if (is_wp_error($term)) {
            return $term;
        }
        if ($id) {
            if (!current_user_can('edit_term', $id)) {
                return Registry::denied();
            }
            $state = self::termState($term);
            if (is_wp_error($state) || is_wp_error($check = Registry::stale($input, $state))) {
                return is_wp_error($state) ? $state : $check;
            }
        }
        $fields = $input['fields'];
        if ((!$id && !isset($fields['name'])) || (isset($fields['name']) && trim(wp_strip_all_tags($fields['name'])) === '')) {
            return Registry::error(__('Supply a Series or category name.', 'organize-series'));
        }
        if ($tax->name === 'series_group' && array_intersect(array_keys($fields), ['icon_id', 'category_ids', 'order'])) {
            return Registry::error(__('These fields apply only to Series, not Series Categories.', 'organize-series'));
        }
        if (isset($fields['parent']) && (!$tax->hierarchical || ($fields['parent'] && (!term_exists($fields['parent'], $tax->name) || $fields['parent'] === $id || term_is_ancestor_of($id, $fields['parent'], $tax->name))))) {
            return Registry::error(__('Use an existing category parent without a cycle.', 'organize-series'));
        }
        if (isset($fields['icon_id']) && $fields['icon_id'] && (!wp_attachment_is_image($fields['icon_id']) || !current_user_can('read_post', $fields['icon_id']))) {
            return Registry::denied();
        }
        if (isset($fields['category_ids'])) {
            $group = $id ? orgseries_group_id($id, false) : 0;
            if ($group && !Registry::editable($group)) {
                return Registry::denied();
            }
            $category = get_taxonomy('series_group');
            if (!$category || !current_user_can($category->cap->assign_terms)) {
                return Registry::denied();
            }
            foreach ($fields['category_ids'] as $categoryId) {
                if (!current_user_can('assign_term', $categoryId)) {
                    return Registry::denied();
                }
                if (!term_exists($categoryId, 'series_group')) {
                    return Registry::error(__('Unknown Series Category.', 'organize-series'));
                }
            }
        }
        if (isset($fields['description']) && !current_user_can('unfiltered_html')) {
            $fields['description'] = wp_kses_post($fields['description']);
        }
        if ($input['dry_run'] ?? false) {
            return ['dry_run' => true, 'id' => $id, 'fields' => (object) $fields];
        }
        $args = array_intersect_key($fields, array_flip(['name', 'slug', 'description', 'parent']));
        if (isset($args['description']) && !current_user_can('unfiltered_html')) {
            $args['description'] = wp_kses_post($args['description']);
        }
        $result = $id ? wp_update_term($id, $tax->name, wp_slash($args)) : wp_insert_term(wp_slash($args['name']), $tax->name, wp_slash($args));
        if (is_wp_error($result)) {
            return $result;
        }
        $id = (int) $result['term_id'];
        $complete = true;
        if (array_key_exists('icon_id', $fields)) {
            if ($fields['icon_id']) {
                $icon = str_replace(seriesicons_url(), '', wp_get_attachment_url($fields['icon_id']));
                seriesicons_write($id, $icon);
                $complete = (string) series_get_icons($id) === $icon;
            } else {
                seriesicons_delete($id);
                $complete = !series_get_icons($id);
            }
        }
        if (array_key_exists('order', $fields)) {
            $complete = ppseries_set_term_order($id, $fields['order']) && $complete;
        }
        if (array_key_exists('category_ids', $fields)) {
            $groupId = orgseries_group_id($id);
            $result = wp_set_object_terms($groupId, $fields['category_ids'], 'series_group');
            $complete = !is_wp_error($result) && !array_diff($fields['category_ids'], self::groupIds($id)) && !array_diff(self::groupIds($id), $fields['category_ids']) && $complete;
        }
        $read = self::read(['id' => $id, 'taxonomy' => $input['taxonomy'] ?? 'series']);
        return is_wp_error($read) ? ['id' => $id, 'complete' => false] : $read + ['complete' => $complete];
    }

    public static function delete(array $input)
    {
        $term = self::term($input);
        if (is_wp_error($term)) {
            return $term;
        }
        $tax = get_taxonomy($term->taxonomy);
        if (!current_user_can($tax->cap->delete_terms) || !current_user_can('delete_term', $term->term_id)) {
            return Registry::denied();
        }
        $state = self::termState($term);
        if (is_wp_error($state) || is_wp_error($check = Registry::stale($input, $state))) {
            return is_wp_error($state) ? $state : $check;
        }
        $group = $term->taxonomy === ppseries_get_series_slug() ? orgseries_group_id($term->term_id, false) : 0;
        if ($group && !current_user_can('delete_post', $group)) {
            return Registry::denied();
        }
        $ids = self::members($term->term_id, $term->taxonomy);
        if ($term->taxonomy === ppseries_get_series_slug()) {
            $cleanupIds = ppseries_get_order_meta_post_ids($term->term_id, 1001);
            if (is_wp_error($cleanupIds)) {
                return $cleanupIds;
            }
            $ids = array_unique(array_merge($ids, $cleanupIds));
        }
        if ($tax->hierarchical) {
            $children = get_terms(['taxonomy' => $tax->name, 'parent' => $term->term_id, 'hide_empty' => false, 'fields' => 'ids', 'number' => 1001]);
            if (is_wp_error($children) || count($children) > 1000) {
                return Registry::error(__('Use native administration for this category hierarchy.', 'organize-series'), 413);
            }
            foreach ($children as $child) {
                if (!current_user_can('edit_term', $child)) {
                    return Registry::denied();
                }
            }
        }
        foreach ($ids as $id) {
            if (!Registry::editable($id)) {
                return Registry::denied();
            }
        }
        if ($input['dry_run'] ?? false) {
            return ['dry_run' => true, 'id' => $term->term_id];
        }
        $result = wp_delete_term($term->term_id, $term->taxonomy);
        return is_wp_error($result) ? $result : ['id' => $term->term_id, 'complete' => (bool) $result && !term_exists($term->term_id, $term->taxonomy)];
    }

    private static function post(int $id)
    {
        $post = get_post($id);
        $tax = get_taxonomy(ppseries_get_series_slug());
        return $post && in_array($post->post_type, $tax->object_type, true) && current_user_can('read_post', $id) && current_user_can('edit_post', $id) ? $post : null;
    }

    private static function postState(int $id)
    {
        $post = get_post($id);
        $terms = wp_get_post_series($id);
        $states = [];
        foreach ($terms as $termId) {
            $state = self::termState(get_term($termId, ppseries_get_series_slug()));
            if (is_wp_error($state)) {
                return $state;
            }
            $states[] = $state;
        }
        return [(array) $post, $states, get_post_meta($id, SPOST_SHORTTITLE_KEY, true)];
    }

    public static function postRead(array $input)
    {
        $post = self::post($input['id']);
        if (!$post) {
            return Registry::denied();
        }
        $state = self::postState($post->ID);
        if (is_wp_error($state)) {
            return $state;
        }
        $items = [];
        foreach (wp_get_post_series($post->ID) as $id) {
            $items[] = ['series_id' => $id, 'part' => (int) wp_series_part($post->ID, $id)];
        }
        return ['id' => $post->ID, 'items' => $items, 'short_title' => (string) get_post_meta($post->ID, SPOST_SHORTTITLE_KEY, true), 'blocks' => self::blocks(parse_blocks($post->post_content)), 'expected_hash' => Registry::hash($state)];
    }

    public static function postUpdate(array $input)
    {
        $post = self::post($input['id']);
        $tax = get_taxonomy(ppseries_get_series_slug());
        if (!$post || !Registry::editable($post->ID) || !current_user_can($tax->cap->assign_terms)) {
            return Registry::denied();
        }
        $state = self::postState($post->ID);
        if (is_wp_error($state) || is_wp_error($check = Registry::stale($input, $state))) {
            return is_wp_error($state) ? $state : $check;
        }
        if (!isset($input['items'])) {
            $short = isset($input['short_title']) ? sanitize_text_field($input['short_title']) : null;
            if ($input['dry_run'] ?? false) {
                return ['dry_run' => true, 'id' => $post->ID, 'short_title' => $short ?? (string) get_post_meta($post->ID, SPOST_SHORTTITLE_KEY, true)];
            }
            if ($short !== null) {
                ppseries_set_post_short_title($post->ID, $short);
            }
            $read = self::postRead(['id' => $post->ID]);
            return is_wp_error($read) ? ['id' => $post->ID, 'complete' => false] : $read + ['complete' => $short === null || $read['short_title'] === $short];
        }
        $old = wp_get_post_series($post->ID);
        $series = isset($input['items']) ? array_column($input['items'], 'series_id') : $old;
        if (count(array_unique($series)) !== count($series) || (count($series) > 1 && !(Settings::addon('multiples') && class_exists('osMulti')))) {
            return Registry::error(__('Multiple membership requires the active Pro Multiple Series feature; duplicate Series IDs are invalid.', 'organize-series'));
        }
        $tokens = array_column($input['series_tokens'] ?? [], 'expected_hash', 'id');
        if (count($tokens) !== count($input['series_tokens'] ?? [])) {
            return Registry::error(__('Supply one token per Series.', 'organize-series'));
        }
        foreach ($series as $id) {
            $term = get_term($id, $tax->name);
            if (!$term || is_wp_error($term)) {
                return Registry::error(__('Unknown Series.', 'organize-series'));
            }
            if (!current_user_can('assign_term', $id)) {
                return Registry::denied();
            }
            if (!in_array($id, $old)) {
                $termState = self::termState($term);
                if (is_wp_error($termState) || is_wp_error($check = Registry::stale(['expected_hash' => $tokens[$id] ?? ''], $termState))) {
                    return is_wp_error($termState) ? $termState : $check;
                }
            }
        }
        $affected = self::affected(array_merge($old, $series), true);
        if (is_wp_error($affected)) {
            return $affected;
        }
        $affected = array_values(array_unique(array_merge($affected, [$post->ID])));
        $parts = $expectedParts = [];
        foreach ($input['items'] as $item) {
            $seriesId = $item['series_id'];
            $current = (int) wp_series_part($post->ID, $seriesId);
            if (isset($item['part'])) {
                $parts[$seriesId] = $item['part'];
            }
            if (isset($item['part']) || !in_array($seriesId, $old)) {
                $part = $item['part'] ?? 0;
                if (ppseries_is_counted_series_post($post->ID)) {
                    $order = ppseries_get_normalized_series_order($seriesId, 0, $post->ID, $part ?: $current);
                    $expectedParts[$seriesId] = array_search($post->ID, $order, true) + 1;
                } else {
                    $return = ppseries_get_series_return($post->ID, $seriesId);
                    $expectedParts[$seriesId] = $part && !($return && (int) $return['part'] === $part) ? $part : $current;
                }
            }
        }
        if ($input['dry_run'] ?? false) {
            return ['dry_run' => true, 'id' => $post->ID, 'series_ids' => $series, 'parts' => (object) $expectedParts, 'affected_post_ids' => $affected];
        }
        $result = ppseries_set_post_series_data($post->ID, $series, $parts, isset($input['short_title']) ? sanitize_text_field($input['short_title']) : null);
        $read = self::postRead(['id' => $post->ID]);
        $actual = wp_get_post_series($post->ID);
        $complete = $result === true && !array_diff($series, $actual) && !array_diff($actual, $series);
        foreach ($expectedParts as $seriesId => $part) {
            $complete = (int) wp_series_part($post->ID, $seriesId) === $part && $complete;
        }
        if (isset($input['short_title'])) {
            $complete = (string) get_post_meta($post->ID, SPOST_SHORTTITLE_KEY, true) === sanitize_text_field($input['short_title']) && $complete;
        }
        return is_wp_error($read) ? ['id' => $post->ID, 'complete' => false] : $read + ['complete' => $complete];
    }

    private static function postContentState(array $ids): string
    {
        $state = [];
        foreach ($ids as $id) {
            $post = get_post($id);
            $state[$id] = $post ? Registry::hash((array) $post) : null;
        }
        return Registry::hash($state);
    }

    private static function bulkState(array $rows)
    {
        $state = $seriesIds = [];
        foreach ($rows as $row) {
            $postState = self::postState($row['id']);
            if (is_wp_error($postState)) {
                return $postState;
            }
            $state['posts'][$row['id']] = Registry::hash($postState);
            $seriesIds = array_merge($seriesIds, wp_get_post_series($row['id']), array_column($row['items'] ?? [], 'series_id'));
        }
        foreach (array_unique($seriesIds) as $id) {
            $term = get_term($id, ppseries_get_series_slug());
            if (!$term || is_wp_error($term)) {
                return Registry::error(__('A target Series no longer exists.', 'organize-series'), 409);
            }
            $termState = self::termState($term);
            if (is_wp_error($termState)) {
                return $termState;
            }
            $state['series'][$id] = Registry::hash($termState);
        }
        return $state;
    }

    public static function bulkPostUpdate(array $input)
    {
        $rows = $input['items'];
        $ids = array_column($rows, 'id');
        if (count(array_unique($ids)) !== count($ids)) {
            return Registry::error(__('Each bulk post target must appear only once.', 'organize-series'));
        }
        $previews = $watched = [];
        foreach ($rows as $row) {
            $preview = self::postUpdate(array_merge($row, ['dry_run' => true]));
            if (is_wp_error($preview)) {
                return $preview;
            }
            $watched = array_values(array_unique(array_merge($watched, [$row['id']], $preview['affected_post_ids'] ?? [])));
            if (count($watched) > 1000) {
                return Registry::error(__('Use native administration for more than 1,000 affected posts.', 'organize-series'), 413);
            }
            $previews[] = $preview;
        }
        $guard = self::bulkState($rows);
        if (is_wp_error($guard)) {
            return $guard;
        }
        if ($input['dry_run'] ?? false) {
            return ['dry_run' => true, 'items' => $previews];
        }
        $postContent = self::postContentState($watched);
        $applied = [];
        foreach ($rows as $row) {
            $current = self::bulkState($rows);
            if (is_wp_error($current) || Registry::hash($current) !== Registry::hash($guard)) {
                return ['complete' => false, 'applied_ids' => $applied, 'failed_id' => $row['id'], 'message' => __('The batch changed. Read every affected object before retrying.', 'organize-series')];
            }
            // Earlier native numbering changes are owned by this validated batch.
            $row['expected_hash'] = $current['posts'][$row['id']];
            $row['series_tokens'] = [];
            foreach (array_column($row['items'] ?? [], 'series_id') as $id) {
                $hash = $current['series'][$id];
                $row['series_tokens'][] = ['id' => $id, 'expected_hash' => $hash];
            }
            $row['dry_run'] = false;
            $result = self::postUpdate($row);
            if (is_wp_error($result) || empty($result['complete'])) {
                return ['complete' => false, 'applied_ids' => $applied, 'failed_id' => $row['id'], 'message' => __('A native bulk update could not be fully confirmed. Read affected objects before retrying.', 'organize-series')];
            }
            $applied[] = $row['id'];
            if (self::postContentState($watched) !== $postContent) {
                return ['complete' => false, 'applied_ids' => $applied, 'failed_id' => $row['id'], 'message' => __('Post content changed during the native batch. Read affected posts before retrying.', 'organize-series')];
            }
            $guard = self::bulkState($rows);
            if (is_wp_error($guard)) {
                return ['complete' => false, 'applied_ids' => $applied, 'failed_id' => $row['id']];
            }
        }
        return ['complete' => true, 'applied_ids' => $applied];
    }

    public static function manage(array $input)
    {
        $term = self::term(['id' => $input['id']]);
        if (is_wp_error($term)) {
            return $term;
        }
        $state = self::termState($term);
        if (is_wp_error($state) || is_wp_error($check = Registry::stale($input, $state))) {
            return is_wp_error($state) ? $state : $check;
        }
        $members = self::members($term->term_id, $term->taxonomy);
        if (is_wp_error($members)) {
            return $members;
        }
        $ids = $input['post_ids'] ?? $members;
        if (array_diff($ids, $members)) {
            return Registry::error(__('Every target must belong to this Series.', 'organize-series'));
        }
        $operation = $input['operation'];
        if (($operation !== 'sync-menu-order' && !current_user_can('publish_posts')) || !current_user_can(get_taxonomy($term->taxonomy)->cap->edit_terms)) {
            return Registry::denied();
        }
        if (isset($input['post_ids']) && in_array($operation, ['unpublish', 'mark-published', 'date-asc', 'date-desc'], true)) {
            return Registry::error(__('This operation applies to the Series; omit post_ids.', 'organize-series'));
        }
        if (isset($input['date']) && $operation !== 'publish') {
            return Registry::error(__('Publication dates apply only to publish operations.', 'organize-series'));
        }
        if (in_array($operation, ['trash', 'publish'], true) && !isset($input['post_ids'])) {
            return Registry::error(__('Supply explicit post_ids for this operation.', 'organize-series'));
        }
        $menuParts = [];
        if ($operation === 'sync-menu-order') {
            $menuRows = ppseries_get_menu_order_posts($term->term_id);
            if (!is_array($menuRows)) {
                return Registry::error(__('Native menu-order targets could not be read.', 'organize-series'), 500);
            }
            foreach ($menuRows as $row) {
                $menuParts[(int) $row->ID] = (int) $row->series_part;
            }
            $eligible = array_keys($menuParts);
            if (isset($input['post_ids']) && array_diff($ids, $eligible)) {
                return Registry::error(__('Menu-order sync accepts only native published, numbered posts.', 'organize-series'));
            }
            $ids = isset($input['post_ids']) ? $ids : $eligible;
        }
        $counted = array_values(array_filter($members, 'ppseries_is_counted_series_post'));
        $expected = $operation === 'pending-order' ? array_values(array_filter($members, static fn($id) => in_array(get_post_status($id), ppseries_publisher_pending_statuses(), true))) : $counted;
        if (in_array($operation, ['order', 'pending-order'], true) && (array_diff($ids, $expected) || array_diff($expected, $ids))) {
            return Registry::error(__('Supply the complete native ordered post set; no hidden targets are changed.', 'organize-series'));
        }
        if (in_array($operation, ['date-asc', 'date-desc'], true)) {
            $ids = ppseries_publisher_get_series_post_ids_by_date($term->term_id, $operation === 'date-asc' ? 'ASC' : 'DESC');
        }
        if ($operation === 'trash') {
            foreach ($ids as $id) {
                if (!current_user_can('delete_post', $id)) {
                    return Registry::denied();
                }
            }
        }
        if ($operation === 'publish') {
            if (!current_user_can('publish_posts') || !$ids) {
                return Registry::denied();
            }
            foreach ($ids as $id) {
                if (!current_user_can('publish_post', $id) || !in_array(get_post_status($id), ['pending', 'draft', 'future'], true)) {
                    return Registry::denied();
                }
            }
        }
        if ($operation === 'unpublish') {
            $ids = ppseries_publisher_get_unpublish_ids($term->term_id);
            if (is_wp_error($ids)) {
                return $ids;
            }
        }
        if ($operation === 'mark-published') {
            $ids = [];
        }
        $changesStatus = in_array($operation, ['publish', 'unpublish', 'trash'], true);
        $series = [$term->term_id];
        if ($changesStatus) {
            foreach ($ids as $id) {
                $series = array_merge($series, wp_get_post_series($id));
            }
            $affected = self::affected($series, true);
            if (is_wp_error($affected)) {
                return $affected;
            }
            $affected = array_values(array_unique(array_merge($affected, $ids)));
        } else {
            $affected = $operation === 'mark-published' ? [] : (in_array($operation, ['date-asc', 'date-desc'], true) ? $counted : $ids);
        }
        foreach ($affected as $id) {
            if (!Registry::editable($id)) {
                return Registry::denied();
            }
        }
        $tokens = array_column($input['series_tokens'] ?? [], 'expected_hash', 'id');
        if (count($tokens) !== count($input['series_tokens'] ?? [])) {
            return Registry::error(__('Supply one token per Series.', 'organize-series'));
        }
        foreach (array_diff(array_unique($series), [$term->term_id]) as $otherId) {
            $otherState = self::termState(get_term($otherId, ppseries_get_series_slug()));
            if (is_wp_error($otherState) || is_wp_error($check = Registry::stale(['expected_hash' => $tokens[$otherId] ?? ''], $otherState))) {
                return is_wp_error($otherState) ? $otherState : $check;
            }
        }
        $date = isset($input['date']) ? \DateTimeImmutable::createFromFormat('!Y-m-d H:i', $input['date'], wp_timezone()) : new \DateTimeImmutable('now', wp_timezone());
        if (!$date || (isset($input['date']) && $date->format('Y-m-d H:i') !== $input['date'])) {
            return Registry::error(__('Use a valid site-local publication date.', 'organize-series'));
        }
        if ($input['dry_run'] ?? false) {
            return ['dry_run' => true, 'operation' => $operation, 'post_ids' => $ids, 'affected_post_ids' => $affected];
        }
        $published = (array) get_option('im_published_series', []);
        $unpublished = (array) get_option('im_unpublished_series', []);
        if ($operation === 'publish') {
            series_issue_manager_publish($term->term_id, $ids, ['aa' => $date->format('Y'), 'mm' => $date->format('m'), 'jj' => $date->format('d'), 'hh' => $date->format('H'), 'mn' => $date->format('i')], $published, $unpublished);
        } elseif ($operation === 'unpublish') {
            series_issue_manager_unpublish($term->term_id, $published, $unpublished);
        } elseif ($operation === 'mark-published') {
            ppseries_publisher_mark_published($term->term_id, $published, $unpublished);
            return ['complete' => in_array($term->term_id, (array) get_option('im_published_series')) && !in_array($term->term_id, (array) get_option('im_unpublished_series')), 'series' => self::read(['id' => $term->term_id])];
        } elseif ($operation === 'trash') {
            foreach ($ids as $id) {
                if (!Registry::editable($id) || !current_user_can('delete_post', $id)) {
                    return ['complete' => false, 'failed_id' => $id];
                }
                wp_trash_post($id);
            }
        } elseif ($operation === 'pending-order') {
            series_issue_manager_pending_order($term->term_id, $ids);
        } elseif (in_array($operation, ['order', 'date-asc', 'date-desc'], true)) {
            series_issue_manager_part($term->term_id, $ids, in_array($operation, ['date-asc', 'date-desc'], true));
        } else {
            foreach ($ids as $id) {
                if (!Registry::editable($id) || ppseries_sync_post_menu_order($id, $menuParts[$id]) === false) {
                    return ['complete' => false, 'failed_id' => $id];
                }
            }
        }
        $applied = [];
        foreach ($ids as $index => $id) {
            $matches = true;
            if ($operation === 'publish') {
                $expectedDate = (new \DateTimeImmutable($date->format('Y-m-d H:i:00'), new \DateTimeZone('UTC')))->modify('-' . ($index + 1) . ' seconds')->format('Y-m-d H:i:s');
                $matches = in_array(get_post_status($id), ['publish', 'future'], true) && get_post_field('post_date', $id) === $expectedDate && get_post_field('post_date_gmt', $id) === get_gmt_from_date($expectedDate);
            } elseif ($operation === 'unpublish') {
                $matches = in_array(get_post_status($id), ['draft', 'pending'], true);
            } elseif ($operation === 'trash') {
                $matches = get_post_status($id) === 'trash';
            } elseif ($operation === 'order') {
                $matches = (int) wp_series_part($id, $term->term_id) === $index + 1;
            } elseif (in_array($operation, ['date-asc', 'date-desc'], true)) {
                $actualOrder = array_values(array_intersect(ppseries_get_numbered_series_post_ids($term->term_id), $ids));
                $matches = $actualOrder === $ids;
            } elseif ($operation === 'pending-order') {
                $matches = (int) get_post_meta($id, apply_filters('orgseries_pending_part_key', '_pending_series_part', $term->term_id), true) === $index + 1;
            } else {
                $matches = (int) get_post_field('menu_order', $id) === $menuParts[$id];
            }
            if ($matches) {
                $applied[] = $id;
            }
        }
        $complete = count($applied) === count($ids);
        if (in_array($operation, ['publish', 'unpublish'], true)) {
            $complete = in_array($term->term_id, (array) get_option($operation === 'publish' ? 'im_published_series' : 'im_unpublished_series')) && !in_array($term->term_id, (array) get_option($operation === 'publish' ? 'im_unpublished_series' : 'im_published_series')) && $complete;
        }
        return ['complete' => $complete, 'applied_ids' => $applied, 'series' => self::read(['id' => $term->term_id])];
    }

    public static function blockSchemas(): array
    {
        $schemas = [];
        foreach (\WP_Block_Type_Registry::get_instance()->get_all_registered() as $name => $block) {
            if (str_starts_with($name, 'publishpress-series/')) {
                $keys = $name === 'publishpress-series/series-list' ? array_keys(\PublishPress\Series\Blocks::getSeriesListAttributes()) : ['layoutId', 'seriesId'];
                $schemas[$name] = Registry::object(array_intersect_key($block->attributes, array_flip($keys)));
            }
        }
        return $schemas;
    }

    private static function blocks(array $blocks, array $parent = []): array
    {
        $result = [];
        foreach ($blocks as $index => $block) {
            $path = array_merge($parent, [$index]);
            if (str_starts_with($block['blockName'] ?? '', 'publishpress-series/')) {
                $result[] = ['path' => $path, 'name' => $block['blockName'], 'attributes' => (object) array_intersect_key($block['attrs'], self::blockSchemas()[$block['blockName']]['properties'] ?? [])];
            }
            $result = array_merge($result, self::blocks($block['innerBlocks'], $path));
        }
        return $result;
    }

    public static function blockUpdate(array $input)
    {
        $post = self::post($input['id']);
        if (!$post || !Registry::editable($post->ID)) {
            return Registry::denied();
        }
        $state = self::postState($post->ID);
        if (is_wp_error($state) || is_wp_error($check = Registry::stale($input, $state))) {
            return is_wp_error($state) ? $state : $check;
        }
        $schema = self::blockSchemas()[$input['block_name']] ?? null;
        if (!$schema) {
            return Registry::error();
        }
        $attrs = Registry::validate($input['attributes'], $schema);
        if (is_wp_error($attrs)) {
            return $attrs;
        }
        $allowed = get_allowed_block_types(new \WP_Block_Editor_Context(['post' => $post]));
        if ($allowed === false || (is_array($allowed) && !in_array($input['block_name'], $allowed, true))) {
            return Registry::denied();
        }
        if (!empty($attrs['layoutId'])) {
            $module = str_replace('publishpress-series/', '', $input['block_name']);
            $definition = Layouts::modules()[$module] ?? null;
            if (!$definition || (!Layouts::readable($attrs['layoutId'], $definition['type']) || get_post_status($attrs['layoutId']) !== 'publish')) {
                return Registry::denied();
            }
        }
        if (!empty($attrs['seriesId']) && !term_exists($attrs['seriesId'], ppseries_get_series_slug())) {
            return Registry::error(__('Unknown Series.', 'organize-series'));
        }
        $blocks = parse_blocks($post->post_content);
        if (isset($input['path'])) {
            if (!$input['path']) {
                return Registry::error();
            }
            $target = &$blocks;
            $path = $input['path'];
            $last = array_pop($path);
            foreach ($path as $index) {
                if (!isset($target[$index])) {
                    return Registry::error();
                }
                $target = &$target[$index]['innerBlocks'];
            }
            if (!isset($target[$last]) || $target[$last]['blockName'] !== $input['block_name']) {
                return Registry::error(__('Read the current Series block path before editing.', 'organize-series'), 409);
            }
            $target[$last]['attrs'] = array_merge($target[$last]['attrs'], $attrs);
            unset($target);
        } else {
            $blocks[] = ['blockName' => $input['block_name'], 'attrs' => $attrs, 'innerBlocks' => [], 'innerHTML' => '', 'innerContent' => []];
        }
        if ($input['dry_run'] ?? false) {
            return ['dry_run' => true, 'blocks' => self::blocks($blocks)];
        }
        $content = serialize_blocks($blocks);
        // Editor policy filters can change content or permissions during validation.
        $current = self::postState($post->ID);
        if (is_wp_error($current) || is_wp_error($check = Registry::stale($input, $current))) {
            return is_wp_error($current) ? $current : $check;
        }
        if (!self::post($post->ID) || !Registry::editable($post->ID)) {
            return Registry::denied();
        }
        $result = wp_update_post(wp_slash(['ID' => $post->ID, 'post_content' => $content]), true);
        if (is_wp_error($result)) {
            return $result;
        }
        if (get_post_field('post_content', $post->ID) !== $content) {
            return Registry::unconfirmed();
        }
        return self::postRead(['id' => $post->ID]);
    }
}
