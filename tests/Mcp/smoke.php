<?php

/** Run only in a disposable series_mcp_* WordPress database, using a released Adapter. */

if (!defined('REST_REQUEST')) {
    define('REST_REQUEST', true);
}
require $argv[1];
if (strpos(DB_NAME, 'series_mcp_') !== 0) {
    throw new RuntimeException('An isolated Series database is required.');
}
require $argv[2] . '/vendor/autoload.php';
wp_set_current_user(1);
$assertions = 0;
function check($value, $message)
{
    global $assertions;
    ++$assertions;
    if (!$value) {
        throw new RuntimeException($message);
    }
}
function callSeries($name, $input = [])
{
    $ability = wp_get_ability('publishpress-series/' . $name);
    check($ability !== null, 'Missing ability: ' . $name);
    $tool = \WP\MCP\Domain\Tools\McpTool::fromAbility($ability);
    check(!is_wp_error($tool), 'Adapter rejected: ' . $name);
    return $tool->execute($input);
}
function success($value, $message)
{
    check(!is_wp_error($value), $message . (is_wp_error($value) ? ': ' . $value->get_error_message() : ''));
    return $value;
}
$choices = success(callSeries('get-content-choices'), 'Feature discovery');
check($choices['pro'] === (getenv('SERIES_MCP_MODE') === 'pro'), 'Edition mismatch');
$settings = success(callSeries('get-plugin-settings'), 'Settings discovery');
foreach ((array) $settings['fields'] as $key => $field) {
    if (property_exists($settings['settings'], $key)) {
        check(!is_wp_error(\PublishPress\Series\Mcp\Registry::validate($settings['settings']->$key, $field['schema'])), 'Native setting/schema mismatch: ' . $key);
    }
}

// Exercise the native Settings API filter too, including preservation on partial patches.
register_setting('orgseries_options', 'org_series_options', 'orgseries_validate');
$raw = get_option('org_series_options');
$raw['mcp_unrelated_fixture'] = ['keep' => 'unchanged'];
update_option('org_series_options', $raw);
$settings = success(callSeries('get-plugin-settings'), 'Fresh settings');
foreach ((array) $settings['fields'] as $key => $field) {
    if (!property_exists($settings['settings'], $key) || $key === 'kill_on_delete' || str_ends_with($key, '_selection') || in_array($key, ['enabled_pro_addons', 'post_types_for_series'], true)) {
        continue;
    }
    $value = $settings['settings']->$key;
    $before = get_option('org_series_options');
    success(callSeries('update-plugin-settings', ['expected_hash' => $settings['expected_hash'], 'settings' => (object) [$key => $value], 'dry_run' => true]), 'Setting preview: ' . $key);
    check(get_option('org_series_options') === $before, 'Preview wrote options: ' . $key);
    $settings = success(callSeries('update-plugin-settings', ['expected_hash' => $settings['expected_hash'], 'settings' => (object) [$key => $value]]), 'Native setting save: ' . $key);
    check($settings['settings']->$key === $value, 'Setting round trip mismatch: ' . $key);
    check(get_option('org_series_options')['mcp_unrelated_fixture'] === ['keep' => 'unchanged'], 'Unrelated native option lost');
}
$before = get_option('org_series_options');
foreach (['false', 0, [], (object) []] as $bad) {
    check(is_wp_error(callSeries('update-plugin-settings', ['expected_hash' => $settings['expected_hash'], 'settings' => (object) ['auto_tag_toggle' => $bad, 'series_toc_title' => 'Must not save']])), 'Malformed checkbox accepted');
    check(get_option('org_series_options') === $before, 'Mixed invalid patch wrote settings');
}
check(is_wp_error(callSeries('update-plugin-settings', ['expected_hash' => $settings['expected_hash'], 'settings' => (object) ['unknown' => true]])), 'Unknown setting accepted');
check(is_wp_error(callSeries('update-plugin-settings', ['expected_hash' => str_repeat('0', 64), 'settings' => (object) ['auto_tag_toggle' => false]])), 'Stale settings accepted');

// Every registered field is discovered from the actual native editors and round-tripped.
$createdLayouts = [];
foreach (['post-list-box', 'post-details', 'post-navigation'] as $module) {
    $layout = success(callSeries('get-layout', ['module' => $module]), 'Layout discovery: ' . $module);
    $create = success(callSeries('save-layout', ['module' => $module, 'title' => 'MCP ' . $module, 'settings' => (object) []]), 'Create layout: ' . $module);
    $createdLayouts[$module] = $create['id'];
    $definition = \PublishPress\Series\Mcp\Layouts::modules()[$module];
    $metaKey = $definition['prefix'] . 'layout_meta_value';
    update_post_meta($create['id'], $metaKey, ['unrelated_fixture' => 'keep']);
    $layout = success(callSeries('get-layout', ['module' => $module, 'id' => $create['id']]), 'Saved layout');
    foreach ((array) $layout['fields'] as $key => $field) {
        $schema = $field['schema'];
        $value = $layout['settings']->$key;
        check(!is_wp_error(\PublishPress\Series\Mcp\Registry::validate($value, $schema)), 'Native layout/schema mismatch: ' . $module . '/' . $key);
        if ($field['locked']) {
            check(is_wp_error(callSeries('save-layout', ['module' => $module, 'id' => $create['id'], 'expected_hash' => $layout['expected_hash'], 'settings' => (object) [$key => $value]])), 'Free edition lock bypassed: ' . $key);
            continue;
        }
        if (str_contains($key, 'image') && is_int($value)) {
            $value = 0;
        }
        $before = get_post_meta($create['id'], $metaKey, true);
        success(callSeries('save-layout', ['module' => $module, 'id' => $create['id'], 'expected_hash' => $layout['expected_hash'], 'settings' => (object) [$key => $value], 'dry_run' => true]), 'Layout preview: ' . $key);
        check(get_post_meta($create['id'], $metaKey, true) === $before, 'Layout preview wrote metadata');
        $layout = success(callSeries('save-layout', ['module' => $module, 'id' => $create['id'], 'expected_hash' => $layout['expected_hash'], 'settings' => (object) [$key => $value]]), 'Layout field save: ' . $module . '/' . $key);
        check($layout['settings']->$key === $value, 'Layout read-back mismatch: ' . $module . '/' . $key);
        check(get_post_meta($create['id'], $metaKey, true)['unrelated_fixture'] === 'keep', 'Unrelated layout metadata lost');
    }
    check(is_wp_error(callSeries('save-layout', ['module' => $module, 'id' => $create['id'], 'expected_hash' => $layout['expected_hash'], 'settings' => (object) ['unknown' => true]])), 'Unknown layout field accepted');
    check(is_wp_error(callSeries('save-layout', ['module' => $module, 'id' => $create['id'], 'expected_hash' => str_repeat('0', 64), 'settings' => (object) []])), 'Stale layout accepted');
    success(callSeries('transition-layout', ['module' => $module, 'id' => $create['id'], 'expected_hash' => $layout['expected_hash'], 'operation' => 'trash', 'dry_run' => true]), 'Trash preview');
    check(get_post_status($create['id']) === 'publish', 'Trash preview changed status');
}


// Both layout selection paths must use a published, readable object of the correct native type.
$settings = success(callSeries('get-plugin-settings'), 'Layout selection token');
$draftLayout = wp_insert_post(['post_type' => 'pps_post_list_box', 'post_status' => 'draft', 'post_title' => 'Unpublished layout']);
check(is_wp_error(callSeries('update-plugin-settings', ['expected_hash' => $settings['expected_hash'], 'settings' => (object) ['series_post_list_box_selection' => $draftLayout]])), 'Unpublished automatic layout accepted');
if ($choices['pro']) {
    check($choices['multiple_series'], 'Pro fixture did not load native Multiple Series');
    $nativeDefaults = apply_filters('org_series_settings', ['series_post_list_unpublished_post_template' => 'Keep custom template']);
    check($nativeDefaults['series_post_list_unpublished_post_template'] === 'Keep custom template', 'Pro defaults overwrote a saved template');
    $settings = success(callSeries('get-plugin-settings'), 'Pro settings token');
    $before = get_option('org_series_options');
    foreach (['false', (object) ['cpt' => true], ['unknown']] as $bad) {
        check(is_wp_error(callSeries('update-plugin-settings', ['expected_hash' => $settings['expected_hash'], 'settings' => (object) ['enabled_pro_addons' => $bad, 'series_toc_title' => 'Invalid mixed patch']])), 'Malformed Pro addon list accepted');
        check(get_option('org_series_options') === $before, 'Malformed Pro patch partially saved');
    }
    if (isset($settings['fields']->post_types_for_series)) {
        $settings = success(callSeries('update-plugin-settings', ['expected_hash' => $settings['expected_hash'], 'settings' => (object) ['post_types_for_series' => ['post', 'page']]]), 'Pro post types');
        check(get_option('org_series_options')['post_types_for_series'] === ['post', 'page'], 'Pro post types stored in wrong format');
    }
}
if (is_multisite()) {
    $siteAdmin = wp_insert_user(['user_login' => 'mcp_site_admin', 'user_pass' => 'synthetic-only', 'role' => 'administrator']);
    wp_set_current_user($siteAdmin);
    $settings = success(callSeries('get-plugin-settings'), 'Site Administrator settings');
    $beforeNetwork = get_site_option('pp_series_kill_on_delete');
    check(is_wp_error(callSeries('update-plugin-settings', ['expected_hash' => $settings['expected_hash'], 'settings' => (object) ['kill_on_delete' => true, 'series_toc_title' => 'Forbidden mixed network patch']])), 'Site Administrator changed network uninstall policy');
    check(get_site_option('pp_series_kill_on_delete') === $beforeNetwork, 'Denied network policy wrote');
    wp_set_current_user(1);
}

// A failed metadata step must retain and identify the already-created native layout.
$partialId = $createdLayouts['post-details'];
$layout = success(callSeries('get-layout', ['module' => 'post-details', 'id' => $partialId]), 'Partial layout token');
$denyMeta = static fn($check, $id, $key) => (int) $id === $partialId && $key === 'pps_series_post_details_layout_meta_value' ? false : $check;
add_filter('update_post_metadata', $denyMeta, PHP_INT_MAX, 3);
$result = success(callSeries('save-layout', ['module' => 'post-details', 'id' => $partialId, 'expected_hash' => $layout['expected_hash'], 'title' => 'Native title saved before meta failure', 'settings' => (object) ['text_before' => 'Should not persist']]), 'Partial layout outcome');
remove_filter('update_post_metadata', $denyMeta, PHP_INT_MAX);
check(isset($result['complete']) && !$result['complete'] && $result['id'] === $partialId, 'Partial layout metadata failure misreported');
check(get_post_field('post_title', $partialId) === 'Native title saved before meta failure', 'Partial layout lost successful native title');
// Native term/category creation and omitted relationship preservation.
$category = success(callSeries('save-series', ['taxonomy' => 'category', 'fields' => (object) ['name' => 'MCP category']]), 'Create category');
$series = success(callSeries('save-series', ['fields' => (object) ['name' => 'MCP series', 'category_ids' => [$category['id']], 'order' => 7]]), 'Create series');
check($series['category_ids'] === [$category['id']], 'Category assignment did not persist');
$series = success(callSeries('save-series', ['id' => $series['id'], 'expected_hash' => $series['expected_hash'], 'fields' => (object) ['description' => 'Preserve group on edit']]), 'Update term');
check($series['category_ids'] === [$category['id']], 'Omitted category assignments were cleared');

$imageId = wp_insert_post(['post_type' => 'attachment', 'post_status' => 'inherit', 'post_mime_type' => 'image/png', 'post_title' => 'Synthetic image', 'guid' => home_url('/wp-content/uploads/mcp-fixture.png')]);
update_post_meta($imageId, '_wp_attached_file', 'mcp-fixture.png');
check(wp_attachment_is_image($imageId) && current_user_can('read_post', $imageId), 'Synthetic image fixture is not a readable native image');
$series = success(callSeries('save-series', ['id' => $series['id'], 'expected_hash' => $series['expected_hash'], 'fields' => (object) ['icon_id' => $imageId]]), 'Native Series icon');
check($series['icon'] !== '' && series_get_icons($series['id']) === $series['icon'], 'Icon not stored natively');
$icon = $series['icon'];
$series = success(callSeries('save-series', ['id' => $series['id'], 'expected_hash' => $series['expected_hash'], 'fields' => (object) ['description' => 'Preserve group on edit']]), 'Omitted icon patch');
check($series['icon'] === $icon, 'Omitted icon was cleared');
$members = [];
foreach (['First', 'Second', 'Third'] as $title) {
    $id = wp_insert_post(['post_title' => $title, 'post_status' => 'publish', 'post_type' => 'post', 'post_content' => '<!-- wp:paragraph --><p>Original content</p><!-- /wp:paragraph -->']);
    $post = success(callSeries('get-post-series', ['id' => $id]), 'Read post membership');
    $series = success(callSeries('get-series', ['id' => $series['id']]), 'Fresh target series');
    $post = success(callSeries('update-post-series', ['id' => $id, 'expected_hash' => $post['expected_hash'], 'items' => [(object) ['series_id' => $series['id']]], 'series_tokens' => [(object) ['id' => $series['id'], 'expected_hash' => $series['expected_hash']]], 'short_title' => $title . ' widget']), 'Assign post');
    check($post['complete'] && $post['short_title'] === $title . ' widget', 'Membership or short title not saved');
    $members[] = $id;
}
$series = success(callSeries('get-series', ['id' => $series['id']]), 'Order state');
$ordered = array_reverse($members);
$order = success(callSeries('manage-series-posts', ['id' => $series['id'], 'expected_hash' => $series['expected_hash'], 'operation' => 'order', 'post_ids' => $ordered]), 'Native Series ordering');
check($order['complete'], 'Series order not confirmed');
foreach ($ordered as $offset => $id) {
    check((int) wp_series_part($id, $series['id']) === $offset + 1, 'Native part mismatch');
}
// A denied peer must prevent all changes, even for an Administrator.
$denyPeer = static fn($caps, $cap, $user, $args) => $cap === 'edit_post' && (int) ($args[0] ?? 0) === $members[1] ? ['do_not_allow'] : $caps;
add_filter('map_meta_cap', $denyPeer, PHP_INT_MAX, 4);
$series = success(callSeries('get-series', ['id' => $series['id']]), 'Restricted Series state');
check(is_wp_error(callSeries('manage-series-posts', ['id' => $series['id'], 'expected_hash' => $series['expected_hash'], 'operation' => 'unpublish'])), 'Denied peer edit bypassed');
foreach ($members as $id) {
    check(get_post_status($id) === 'publish', 'Preflight denial partially unpublished posts');
}
remove_filter('map_meta_cap', $denyPeer, PHP_INT_MAX);
$post = success(callSeries('get-post-series', ['id' => $members[0]]), 'Block source');
$beforeContent = get_post_field('post_content', $members[0]);
$post = success(callSeries('update-series-block', ['id' => $members[0], 'expected_hash' => $post['expected_hash'], 'block_name' => 'publishpress-series/post-list-box', 'attributes' => (object) ['layoutId' => $createdLayouts['post-list-box'], 'seriesId' => $series['id']]]), 'Insert native Series block');
check(count($post['blocks']) === 1 && str_contains(get_post_field('post_content', $members[0]), 'Original content'), 'Block insertion lost unrelated content');
check(is_wp_error(callSeries('update-series-block', ['id' => $members[0], 'expected_hash' => $post['expected_hash'], 'block_name' => 'publishpress-series/post-list-box', 'attributes' => (object) ['layoutId' => (string) $createdLayouts['post-list-box']]])), 'Numeric-string layout ID accepted');
$before = get_post_field('post_content', $members[0]);
check(is_wp_error(callSeries('update-series-block', ['id' => $members[0], 'expected_hash' => $post['expected_hash'], 'block_name' => 'publishpress-series/post-list-box', 'attributes' => (object) ['unknown' => true]])), 'Unknown block attribute accepted');
check(get_post_field('post_content', $members[0]) === $before, 'Rejected block patch changed content');

// Native publish, schedule, pending order, archive state and deletion paths.
$series = success(callSeries('get-series', ['id' => $series['id']]), 'Unpublish token');
$preview = success(callSeries('manage-series-posts', ['id' => $series['id'], 'expected_hash' => $series['expected_hash'], 'operation' => 'unpublish', 'dry_run' => true]), 'Unpublish preview');
check(get_post_status($members[0]) === 'publish', 'Unpublish preview mutated post');
$result = success(callSeries('manage-series-posts', ['id' => $series['id'], 'expected_hash' => $series['expected_hash'], 'operation' => 'unpublish']), 'Unpublish series');
check($result['complete'] && get_post_status($members[0]) === 'pending', 'Native unpublish mismatch');
$series = success(callSeries('get-series', ['id' => $series['id']]), 'Pending order token');
$result = success(callSeries('manage-series-posts', ['id' => $series['id'], 'expected_hash' => $series['expected_hash'], 'operation' => 'pending-order', 'post_ids' => $ordered]), 'Pending order');
check($result['complete'], 'Pending order not confirmed');
$series = success(callSeries('get-series', ['id' => $series['id']]), 'Publish token');
$future = (new DateTimeImmutable('+2 days', wp_timezone()))->format('Y-m-d H:i');
check(is_wp_error(callSeries('manage-series-posts', ['id' => $series['id'], 'expected_hash' => $series['expected_hash'], 'operation' => 'publish', 'post_ids' => $ordered, 'date' => '2026-02-30 14:00'])), 'Invalid calendar date accepted');
$result = success(callSeries('manage-series-posts', ['id' => $series['id'], 'expected_hash' => $series['expected_hash'], 'operation' => 'publish', 'post_ids' => $ordered, 'date' => $future]), 'Schedule series');
check($result['complete'], 'Scheduled series unconfirmed');
foreach ($members as $id) {
    check(get_post_status($id) === 'future' && wp_next_scheduled('publish_future_post', [$id]), 'Native scheduling event missing');
}
// Native single-post edits can make a previous Series token stale.
$stale = success(callSeries('get-series', ['id' => $series['id']]), 'Stale Series fixture');
wp_update_post(['ID' => $members[0], 'post_title' => 'Changed after token']);
check(is_wp_error(callSeries('manage-series-posts', ['id' => $series['id'], 'expected_hash' => $stale['expected_hash'], 'operation' => 'unpublish'])), 'Stale Series accepted');
foreach ($members as $id) {
    wp_update_post(['ID' => $id, 'post_status' => 'publish', 'post_date' => current_time('mysql'), 'post_date_gmt' => current_time('mysql', true)]);
}
$series = success(callSeries('get-series', ['id' => $series['id']]), 'Date order token');
$result = success(callSeries('manage-series-posts', ['id' => $series['id'], 'expected_hash' => $series['expected_hash'], 'operation' => 'date-desc']), 'Native date order');
check($result['complete'], 'Date order mismatch');
$series = success(callSeries('get-series', ['id' => $series['id']]), 'Menu order token');
$result = success(callSeries('manage-series-posts', ['id' => $series['id'], 'expected_hash' => $series['expected_hash'], 'operation' => 'sync-menu-order']), 'Native menu projection');
check($result['complete'], 'Menu order mismatch');
// A permission changing between native rows must stop the remaining rows and report partial results.
$changed = false;
$denyAfterFirst = static function ($caps, $cap, $user, $args) use (&$changed, $members) {
    return $changed && $cap === 'edit_post' && (int) ($args[0] ?? 0) === $members[1] ? ['do_not_allow'] : $caps;
};
$afterSave = static function ($id) use (&$changed, $members) {
    if ((int) $id === $members[0]) {
        $changed = true;
    }
};
add_filter('map_meta_cap', $denyAfterFirst, PHP_INT_MAX, 4);
add_action('post_updated', $afterSave);
$series = success(callSeries('get-series', ['id' => $series['id']]), 'Partial transition token');
$result = success(callSeries('manage-series-posts', ['id' => $series['id'], 'expected_hash' => $series['expected_hash'], 'operation' => 'unpublish']), 'Partial native transition');
check(!$result['complete'] && get_post_status($members[0]) === 'pending' && get_post_status($members[1]) === 'publish', 'Native partial outcome or permission change misreported');
remove_filter('map_meta_cap', $denyAfterFirst, PHP_INT_MAX);
remove_action('post_updated', $afterSave);
// Lock another editor's post; MCP must not bypass the lock.
$editorId = wp_insert_user(['user_login' => 'mcp_editor', 'user_pass' => 'synthetic-only', 'role' => 'editor']);
update_post_meta($members[0], '_edit_lock', time() . ':' . $editorId);
$post = success(callSeries('get-post-series', ['id' => $members[0]]), 'Locked source token');
check(is_wp_error(callSeries('update-post-series', ['id' => $members[0], 'expected_hash' => $post['expected_hash'], 'short_title' => 'Locked'])), 'Native editor lock bypassed');
delete_post_meta($members[0], '_edit_lock');
$list = success(callSeries('list-series-posts', ['id' => $series['id'], 'per_page' => 1]), 'Member pagination');
check(count($list['items']) === 1 && $list['has_more'], 'Member page mismatch');
// Pro gates use the active native extensions and exact array/list formats.
$other = success(callSeries('save-series', ['fields' => (object) ['name' => 'Other Series']]), 'Second Series');
$post = success(callSeries('get-post-series', ['id' => $members[0]]), 'Multiple Series source');
$multiple = callSeries('update-post-series', ['id' => $members[0], 'expected_hash' => $post['expected_hash'], 'items' => [(object) ['series_id' => $series['id']], (object) ['series_id' => $other['id']]], 'series_tokens' => [(object) ['id' => $other['id'], 'expected_hash' => $other['expected_hash']]]]);
if ($choices['multiple_series']) {
    check(!is_wp_error($multiple) && count($multiple['items']) === 2, 'Active Pro multiple membership failed');
    $other = success(callSeries('get-series', ['id' => $other['id']]), 'Other Series token');
    $multiple = success(callSeries('update-post-series', ['id' => $members[0], 'expected_hash' => $multiple['expected_hash'], 'items' => [(object) ['series_id' => $series['id']]]]), 'Remove extra membership');
} else {
    check(is_wp_error($multiple), 'Free/disabled multiple edition gate bypassed');
}
// Deleting a category or Series leaves posts intact.
$other = success(callSeries('get-series', ['id' => $other['id']]), 'Delete empty Series token');
$unrelatedMetaKey = 'XseriesYpartZ' . $other['id'];
update_post_meta($members[0], $unrelatedMetaKey, 'keep');
$result = success(callSeries('delete-series', ['id' => $other['id'], 'expected_hash' => $other['expected_hash']]), 'Delete Series');
check($result['complete'] && get_post($members[0]), 'Series deletion deleted source content');
check(get_post_meta($members[0], $unrelatedMetaKey, true) === 'keep', 'Series deletion removed similarly named unrelated metadata');
$category = success(callSeries('get-series', ['taxonomy' => 'category', 'id' => $category['id']]), 'Delete category token');
$result = success(callSeries('delete-series', ['taxonomy' => 'category', 'id' => $category['id'], 'expected_hash' => $category['expected_hash']]), 'Delete category');
check($result['complete'] && term_exists($series['id'], ppseries_get_series_slug()), 'Category deletion deleted Series');
// Full native layout lifecycle and permanent-delete guard.
$module = 'post-list-box';
$id = $createdLayouts[$module];
$layout = success(callSeries('get-layout', ['module' => $module, 'id' => $id]), 'Layout lifecycle token');
check(is_wp_error(callSeries('transition-layout', ['module' => $module, 'id' => $id, 'expected_hash' => $layout['expected_hash'], 'operation' => 'delete'])), 'Permanent layout delete accepted outside trash');
success(callSeries('transition-layout', ['module' => $module, 'id' => $id, 'expected_hash' => $layout['expected_hash'], 'operation' => 'trash']), 'Trash layout');
$layout = success(callSeries('get-layout', ['module' => $module, 'id' => $id]), 'Trashed layout token');
success(callSeries('transition-layout', ['module' => $module, 'id' => $id, 'expected_hash' => $layout['expected_hash'], 'operation' => 'untrash']), 'Restore layout');
$layout = success(callSeries('get-layout', ['module' => $module, 'id' => $id]), 'Restored layout token');
success(callSeries('transition-layout', ['module' => $module, 'id' => $id, 'expected_hash' => $layout['expected_hash'], 'operation' => 'trash']), 'Trash layout again');
$layout = success(callSeries('get-layout', ['module' => $module, 'id' => $id]), 'Permanent delete token');
success(callSeries('transition-layout', ['module' => $module, 'id' => $id, 'expected_hash' => $layout['expected_hash'], 'operation' => 'delete']), 'Delete layout');
check(!get_post($id), 'Native permanent delete failed');


// Comparison audit: typed discovery defaults must also satisfy the advertised schema.
foreach (['post-list-box', 'post-details', 'post-navigation'] as $moduleName) {
    $discovery = success(callSeries('get-layout', ['module' => $moduleName]), 'Audit layout discovery');
    foreach ((array) $discovery['fields'] as $key => $definition) {
        if ($definition['default'] !== null) {
            check(!is_wp_error(\PublishPress\Series\Mcp\Registry::validate($definition['default'], $definition['schema'])), 'Layout default JSON mismatch: ' . $moduleName . '/' . $key);
        }
    }
}
$discovery = success(callSeries('get-plugin-settings'), 'Audit settings defaults');
foreach ((array) $discovery['fields'] as $key => $definition) {
    if ($definition['default'] !== null) {
        check(!is_wp_error(\PublishPress\Series\Mcp\Registry::validate($definition['default'], $definition['schema'])), 'Setting default JSON mismatch: ' . $key);
    }
}
$beforeText = get_option('org_series_options');
$beforeBase = $beforeText['series_custom_base'];
$unicode = success(callSeries('update-plugin-settings', ['expected_hash' => $discovery['expected_hash'], 'settings' => (object) ['series_custom_base' => 'leçons/séries', 'series_post_list_limit' => '-1']]), 'Native Unicode base and unlimited limit');
check($unicode['settings']->series_custom_base === 'leçons/séries' && $unicode['settings']->series_post_list_limit === '-1', 'Native text representations lost');
$unicode = success(callSeries('update-plugin-settings', ['expected_hash' => $unicode['expected_hash'], 'settings' => (object) ['series_custom_base' => $beforeBase]]), 'Restore base');
$denyHtml = static fn($caps, $cap) => $cap === 'unfiltered_html' ? ['do_not_allow'] : $caps;
add_filter('map_meta_cap', $denyHtml, PHP_INT_MAX, 2);
$beforeUnsafe = get_option('org_series_options');
check(is_wp_error(callSeries('update-plugin-settings', ['expected_hash' => $unicode['expected_hash'], 'settings' => (object) ['series_nextpost_nav_custom_text' => '<script>unsafe()</script>', 'series_toc_title' => 'Must not save']])), 'Non-template unsafe HTML bypassed denied unfiltered_html');
check(get_option('org_series_options') === $beforeUnsafe, 'Denied unsafe text mixed patch partially saved');
remove_filter('map_meta_cap', $denyHtml, PHP_INT_MAX);


$nativeSanitizerPriority = has_filter('sanitize_option_org_series_options', 'orgseries_validate');
remove_filter('sanitize_option_org_series_options', 'orgseries_validate');
$legacyOptions = get_option('org_series_options');
$legacyOptions['last_modified'] = 'synthetic-old-cache-marker';
update_option('org_series_options', $legacyOptions);
if ($nativeSanitizerPriority !== false) {
    add_filter('sanitize_option_org_series_options', 'orgseries_validate', $nativeSanitizerPriority);
}
$cacheToken = success(callSeries('get-plugin-settings'), 'Native cache marker token');
success(callSeries('update-plugin-settings', ['expected_hash' => $cacheToken['expected_hash'], 'settings' => (object) ['series_toc_title' => 'Native cache metadata audit']]), 'Native cache metadata save');
check(get_option('org_series_options')['last_modified'] !== 'synthetic-old-cache-marker', 'Native settings cache metadata was not refreshed');
// Slashed WordPress term APIs must preserve literal backslashes on create and update.
$slashName = 'MCP C:\\Guides';
$slashTerm = success(callSeries('save-series', ['fields' => (object) ['name' => $slashName, 'description' => 'Path C:\\docs']]), 'Create term with backslashes');
check($slashTerm['name'] === $slashName && $slashTerm['description'] === 'Path C:\\docs', 'Created term text lost backslashes');
$slashTerm = success(callSeries('save-series', ['id' => $slashTerm['id'], 'expected_hash' => $slashTerm['expected_hash'], 'fields' => (object) ['name' => $slashName . ' updated', 'description' => 'Path D:\\next']]), 'Update term with backslashes');
check($slashTerm['name'] === $slashName . ' updated' && $slashTerm['description'] === 'Path D:\\next', 'Updated term text lost backslashes');

// Bulk membership preflights every row before any native relationship write.
$bulkSeries = success(callSeries('save-series', ['fields' => (object) ['name' => 'Bulk audit Series']]), 'Bulk target Series');
$bulkIds = [];
$bulkRows = [];
foreach (['Bulk alpha', 'Bulk beta'] as $title) {
    $targetId = wp_insert_post(['post_type' => 'post', 'post_status' => 'publish', 'post_title' => $title]);
    $bulkIds[] = $targetId;
    $target = success(callSeries('get-post-series', ['id' => $targetId]), 'Bulk target state');
    $bulkRows[] = ['id' => $targetId, 'expected_hash' => $target['expected_hash'], 'items' => [['series_id' => $bulkSeries['id']]], 'series_tokens' => [['id' => $bulkSeries['id'], 'expected_hash' => $bulkSeries['expected_hash']]]];
}
$malformed = $bulkRows;
$malformed[1]['items'][0]['part'] = '2';
check(is_wp_error(callSeries('bulk-update-post-series', ['items' => $malformed])), 'Mixed invalid bulk list accepted');
check(!wp_get_post_series($bulkIds[0]) && !wp_get_post_series($bulkIds[1]), 'Mixed invalid bulk patch wrote relationships');
$duplicateRows = [$bulkRows[0], array_merge($bulkRows[0], ['short_title' => 'Duplicate'])];
check(is_wp_error(callSeries('bulk-update-post-series', ['items' => $duplicateRows])), 'Duplicate logical post targets accepted');
success(callSeries('bulk-update-post-series', ['items' => $bulkRows, 'dry_run' => true]), 'Bulk preview');
check(!wp_get_post_series($bulkIds[0]) && !wp_get_post_series($bulkIds[1]), 'Bulk preview wrote');
$bulkResult = success(callSeries('bulk-update-post-series', ['items' => $bulkRows]), 'Bulk membership save');
check($bulkResult['complete'] && $bulkResult['applied_ids'] === $bulkIds, 'Bulk native membership unconfirmed');
foreach ($bulkIds as $index => $targetId) {
    check((int) wp_series_part($targetId, $bulkSeries['id']) === $index + 1, 'Bulk placement changed native order');
}
$target = success(callSeries('get-post-series', ['id' => $bulkIds[0]]), 'Short title token');
$peerDeny = static fn($caps, $cap, $user, $args) => $cap === 'edit_post' && (int) ($args[0] ?? 0) === $bulkIds[1] ? ['do_not_allow'] : $caps;
add_filter('map_meta_cap', $peerDeny, PHP_INT_MAX, 4);
$target = success(callSeries('update-post-series', ['id' => $bulkIds[0], 'expected_hash' => $target['expected_hash'], 'short_title' => 'Widget C:\\guides']), 'Short title without unrelated peer writes');
check($target['complete'] && $target['short_title'] === 'Widget C:\\guides', 'Native short title lost backslashes');
remove_filter('map_meta_cap', $peerDeny, PHP_INT_MAX);
$denyShort = static fn($check, $id, $key) => (int) $id === $bulkIds[0] && $key === SPOST_SHORTTITLE_KEY ? false : $check;
add_filter('update_post_metadata', $denyShort, PHP_INT_MAX, 3);
$result = success(callSeries('update-post-series', ['id' => $bulkIds[0], 'expected_hash' => $target['expected_hash'], 'short_title' => 'Must not claim success']), 'Unconfirmed short title');
remove_filter('update_post_metadata', $denyShort, PHP_INT_MAX);
check(!$result['complete'] && $result['short_title'] === 'Widget C:\\guides', 'Metadata failure reported as complete');

// Bulk execution rechecks native object permissions after earlier rows save.
$rows = [];
foreach ($bulkIds as $targetId) {
    $target = success(callSeries('get-post-series', ['id' => $targetId]), 'Bulk title token');
    $rows[] = ['id' => $targetId, 'expected_hash' => $target['expected_hash'], 'short_title' => 'Bulk title ' . $targetId];
}
$revoked = false;
$revokeAfterSave = static function ($metaId, $id, $key) use (&$revoked, $bulkIds) {
    if ((int) $id === $bulkIds[0] && $key === SPOST_SHORTTITLE_KEY) {
        $revoked = true;
    }
};
// Capture the flag by reference for the permission callback.
$revokeSecond = static function ($caps, $cap, $user, $args) use (&$revoked, $bulkIds) {
    return $revoked && $cap === 'edit_post' && (int) ($args[0] ?? 0) === $bulkIds[1] ? ['do_not_allow'] : $caps;
};
add_action('updated_post_meta', $revokeAfterSave, 10, 3);
add_filter('map_meta_cap', $revokeSecond, PHP_INT_MAX, 4);
$partial = success(callSeries('bulk-update-post-series', ['items' => $rows]), 'Bulk permission partial result');
remove_action('updated_post_meta', $revokeAfterSave, 10);
remove_filter('map_meta_cap', $revokeSecond, PHP_INT_MAX);
check(!$partial['complete'] && $partial['applied_ids'] === [$bulkIds[0]] && $partial['failed_id'] === $bulkIds[1], 'Bulk permission partial result incorrect');
check((string) get_post_meta($bulkIds[1], SPOST_SHORTTITLE_KEY, true) === '', 'Revoked bulk target was changed');

// Native sync skips drafts/private posts and published posts without a numbered meta row.
$draftId = wp_insert_post(['post_type' => 'post', 'post_status' => 'draft', 'post_title' => 'Keep draft menu order', 'menu_order' => 91]);
$privateId = wp_insert_post(['post_type' => 'post', 'post_status' => 'private', 'post_title' => 'Keep private menu order', 'menu_order' => 92]);
foreach ([$draftId, $privateId] as $targetId) {
    wp_set_object_terms($targetId, [$bulkSeries['id']], ppseries_get_series_slug());
}
$partKey = apply_filters('orgseries_part_key', SERIES_PART_KEY, $bulkSeries['id']);
update_post_meta($draftId, $partKey, 99);
delete_post_meta($bulkIds[1], $partKey);
wp_update_post(['ID' => $bulkIds[1], 'menu_order' => 93]);
$seriesToken = success(callSeries('get-series', ['id' => $bulkSeries['id']]), 'Native menu sync token');
$preview = success(callSeries('manage-series-posts', ['id' => $bulkSeries['id'], 'expected_hash' => $seriesToken['expected_hash'], 'operation' => 'sync-menu-order', 'dry_run' => true]), 'Exact native menu preview');
check($preview['post_ids'] === [$bulkIds[0]], 'Menu preview included native-ineligible objects');
$result = success(callSeries('manage-series-posts', ['id' => $bulkSeries['id'], 'expected_hash' => $seriesToken['expected_hash'], 'operation' => 'sync-menu-order']), 'Exact native menu sync');
check($result['complete'] && (int) get_post_field('menu_order', $draftId) === 91 && (int) get_post_field('menu_order', $privateId) === 92 && (int) get_post_field('menu_order', $bulkIds[1]) === 93, 'Menu projection changed native-ineligible order');


// Pending-order and unpublish match native publication states and never restore Trash.
$trashedId = wp_insert_post(['post_type' => 'post', 'post_status' => 'publish', 'post_title' => 'Keep trashed audit post']);
wp_set_object_terms($trashedId, [$bulkSeries['id']], ppseries_get_series_slug());
wp_trash_post($trashedId);
$pendingIds = array_values(array_filter(array_map('intval', get_objects_in_term($bulkSeries['id'], ppseries_get_series_slug())), static fn($id) => in_array(get_post_status($id), ppseries_publisher_pending_statuses(), true)));
$seriesToken = success(callSeries('get-series', ['id' => $bulkSeries['id']]), 'Trash-aware pending token');
$result = success(callSeries('manage-series-posts', ['id' => $bulkSeries['id'], 'expected_hash' => $seriesToken['expected_hash'], 'operation' => 'pending-order', 'post_ids' => $pendingIds]), 'Native pending statuses');
check($result['complete'] && get_post_status($trashedId) === 'trash', 'Pending ordering touched Trash');
$denyInactive = static fn($caps, $cap, $user, $args) => $cap === 'edit_post' && in_array((int) ($args[0] ?? 0), [$draftId, $privateId, $trashedId], true) ? ['do_not_allow'] : $caps;
add_filter('map_meta_cap', $denyInactive, PHP_INT_MAX, 4);
$seriesToken = success(callSeries('get-series', ['id' => $bulkSeries['id']]), 'Scoped menu permission token');
success(callSeries('manage-series-posts', ['id' => $bulkSeries['id'], 'expected_hash' => $seriesToken['expected_hash'], 'operation' => 'sync-menu-order', 'dry_run' => true]), 'Menu sync should not require edit access to skipped native targets');
$marker = success(callSeries('manage-series-posts', ['id' => $bulkSeries['id'], 'expected_hash' => $seriesToken['expected_hash'], 'operation' => 'mark-published', 'dry_run' => true]), 'Marker-only preview');
check($marker['post_ids'] === [] && $marker['affected_post_ids'] === [], 'Marker preview exposed unrelated post IDs');
remove_filter('map_meta_cap', $denyInactive, PHP_INT_MAX);
$denyTrash = static fn($caps, $cap, $user, $args) => in_array($cap, ['edit_post', 'delete_post'], true) && (int) ($args[0] ?? 0) === $trashedId ? ['do_not_allow'] : $caps;
add_filter('map_meta_cap', $denyTrash, PHP_INT_MAX, 4);
$seriesToken = success(callSeries('get-series', ['id' => $bulkSeries['id']]), 'Unpublish with Trash');
$result = success(callSeries('manage-series-posts', ['id' => $bulkSeries['id'], 'expected_hash' => $seriesToken['expected_hash'], 'operation' => 'unpublish']), 'Unpublish skips native Trash');
remove_filter('map_meta_cap', $denyTrash, PHP_INT_MAX);
check($result['complete'] && get_post_status($trashedId) === 'trash', 'Unpublish implicitly restored a trashed post');

// A failed reschedule of an already-future post must not look successful from status alone.
$oldTimezone = get_option('timezone_string');
update_option('timezone_string', 'Asia/Kathmandu');
$scheduleTerm = success(callSeries('save-series', ['fields' => (object) ['name' => 'Reschedule verification audit']]), 'Reschedule term');
$futureId = wp_insert_post(['post_type' => 'post', 'post_status' => 'future', 'post_title' => 'Future audit', 'post_date' => (new DateTimeImmutable('+2 days', wp_timezone()))->format('Y-m-d H:i:s')]);
wp_set_object_terms($futureId, [$scheduleTerm['id']], ppseries_get_series_slug());
$scheduleTerm = success(callSeries('get-series', ['id' => $scheduleTerm['id']]), 'Fractional-hour schedule token');
$result = success(callSeries('manage-series-posts', ['id' => $scheduleTerm['id'], 'expected_hash' => $scheduleTerm['expected_hash'], 'operation' => 'publish', 'post_ids' => [$futureId], 'date' => (new DateTimeImmutable('+4 days', wp_timezone()))->format('Y-m-d H:i')]), 'Fractional-hour native schedule');
check($result['complete'] && get_post_field('post_date_gmt', $futureId) === get_gmt_from_date(get_post_field('post_date', $futureId)), 'Native timezone schedule mismatch');
$beforeDate = get_post_field('post_date', $futureId);
$scheduleTerm = success(callSeries('get-series', ['id' => $scheduleTerm['id']]), 'Reschedule token');
$skipSchedule = static fn($empty, $postarr) => (int) ($postarr['ID'] ?? 0) === $futureId ? true : $empty;
add_filter('wp_insert_post_empty_content', $skipSchedule, PHP_INT_MAX, 2);
$result = success(callSeries('manage-series-posts', ['id' => $scheduleTerm['id'], 'expected_hash' => $scheduleTerm['expected_hash'], 'operation' => 'publish', 'post_ids' => [$futureId], 'date' => (new DateTimeImmutable('+3 days', wp_timezone()))->format('Y-m-d H:i')]), 'Failed native reschedule result');
remove_filter('wp_insert_post_empty_content', $skipSchedule, PHP_INT_MAX);
check(!$result['complete'] && $result['applied_ids'] === [] && get_post_field('post_date', $futureId) === $beforeDate, 'Failed future reschedule reported complete');
update_option('timezone_string', $oldTimezone);
// Final object-level term denial and hidden cleanup targets must never be bypassed.
$orphanTerm = success(callSeries('save-series', ['fields' => (object) ['name' => 'Orphan cleanup audit']]), 'Orphan term');
$orphanId = wp_insert_post(['post_type' => 'post', 'post_status' => 'publish', 'post_title' => 'Unrelated orphan metadata']);
$orphanKey = SERIES_PART_KEY . '_' . $orphanTerm['id'];
update_post_meta($orphanId, $orphanKey, 2);
$orphanTerm = success(callSeries('get-series', ['id' => $orphanTerm['id']]), 'Orphan-aware token');
$denyOrphan = static fn($caps, $cap, $user, $args) => $cap === 'edit_post' && (int) ($args[0] ?? 0) === $orphanId ? ['do_not_allow'] : $caps;
add_filter('map_meta_cap', $denyOrphan, PHP_INT_MAX, 4);
check(is_wp_error(callSeries('delete-series', ['id' => $orphanTerm['id'], 'expected_hash' => $orphanTerm['expected_hash']])), 'Denied orphan cleanup target bypassed');
check(get_post_meta($orphanId, $orphanKey, true) !== '', 'Denied orphan metadata removed');
remove_filter('map_meta_cap', $denyOrphan, PHP_INT_MAX);
$parentCategory = success(callSeries('save-series', ['taxonomy' => 'category', 'fields' => (object) ['name' => 'Parent audit category']]), 'Parent category');
$childCategory = success(callSeries('save-series', ['taxonomy' => 'category', 'fields' => (object) ['name' => 'Child audit category', 'parent' => $parentCategory['id']]]), 'Child category');
$parentCategory = success(callSeries('get-series', ['taxonomy' => 'category', 'id' => $parentCategory['id']]), 'Hierarchy token');
$denyChild = static fn($caps, $cap, $user, $args) => $cap === 'edit_term' && (int) ($args[0] ?? 0) === $childCategory['id'] ? ['do_not_allow'] : $caps;
add_filter('map_meta_cap', $denyChild, PHP_INT_MAX, 4);
check(is_wp_error(callSeries('delete-series', ['taxonomy' => 'category', 'id' => $parentCategory['id'], 'expected_hash' => $parentCategory['expected_hash']])), 'Denied child reparenting bypassed');
remove_filter('map_meta_cap', $denyChild, PHP_INT_MAX);
check((int) get_term($childCategory['id'], 'series_group')->parent === $parentCategory['id'], 'Denied child was reparented');
$denyAssignment = static fn($caps, $cap, $user, $args) => $cap === 'assign_term' && (int) ($args[0] ?? 0) === $childCategory['id'] ? ['do_not_allow'] : $caps;
add_filter('map_meta_cap', $denyAssignment, PHP_INT_MAX, 4);
check(is_wp_error(callSeries('save-series', ['id' => $orphanTerm['id'], 'expected_hash' => $orphanTerm['expected_hash'], 'fields' => (object) ['category_ids' => [$childCategory['id']]]])), 'Denied individual category assignment bypassed');
remove_filter('map_meta_cap', $denyAssignment, PHP_INT_MAX);

// Truthy native lifecycle short circuits are not proof that a transition saved.
$life = success(callSeries('save-layout', ['module' => 'post-details', 'title' => 'Lifecycle audit', 'settings' => (object) []]), 'Lifecycle audit layout');
$skipTrash = static fn($result, $post) => (int) $post->ID === $life['id'] ? $post : $result;
add_filter('pre_trash_post', $skipTrash, PHP_INT_MAX, 2);
check(is_wp_error(callSeries('transition-layout', ['module' => 'post-details', 'id' => $life['id'], 'expected_hash' => $life['expected_hash'], 'operation' => 'trash'])), 'Unchanged layout lifecycle claimed complete');
remove_filter('pre_trash_post', $skipTrash, PHP_INT_MAX);
check(get_post_status($life['id']) === 'publish', 'Lifecycle fixture state unexpected');

// Native settings screen read access and WordPress's filtered save capability are separate.
$readerId = wp_insert_user(['user_login' => 'series_native_reader', 'user_pass' => 'synthetic-only', 'role' => 'editor']);
get_userdata($readerId)->add_cap('manage_publishpress_series');
wp_set_current_user($readerId);
$readSettings = success(callSeries('get-plugin-settings'), 'Native Series manager settings read');
check(!current_user_can('manage_options'), 'Reader fixture unexpectedly has settings-save capability');
check(is_wp_error(callSeries('update-plugin-settings', ['expected_hash' => $readSettings['expected_hash'], 'settings' => (object) ['series_toc_title' => 'Denied manager save'], 'dry_run' => true])), 'Read-only Series manager saved settings');
if (is_multisite()) {
    check(!property_exists($readSettings['settings'], 'kill_on_delete') && !property_exists($readSettings['fields'], 'kill_on_delete'), 'Network uninstall policy leaked outside its native visibility');
}
$customSettingsCap = static fn() => 'manage_publishpress_series';
add_filter('option_page_capability_orgseries_options', $customSettingsCap);
success(callSeries('update-plugin-settings', ['expected_hash' => $readSettings['expected_hash'], 'settings' => (object) ['series_toc_title' => 'Allowed native filtered save'], 'dry_run' => true]), 'Filtered native Settings API capability');
remove_filter('option_page_capability_orgseries_options', $customSettingsCap);
wp_set_current_user(1);

if ($choices['pro']) {
    $beforeLegacy = get_option('org_series_options');
    $legacy = $beforeLegacy;
    $legacy['enabled_pro_addons'] = ['cpt', 'shortcodes', 'extra-tokens', 'publisher', 'grouping'];
    update_option('org_series_options', $legacy);
    $GLOBALS['orgseries']->settings = get_option('org_series_options');
    $legacyRead = success(callSeries('get-plugin-settings'), 'Legacy Pro feature flags');
    check($legacyRead['settings']->enabled_pro_addons === ['cpt', 'shortcodes', 'extra-tokens'], 'Legacy non-toggle module flags broke the native feature control schema');
    check(!is_wp_error(\PublishPress\Series\Mcp\Registry::validate($legacyRead['settings']->enabled_pro_addons, $legacyRead['fields']->enabled_pro_addons['schema'])), 'Legacy feature projection violates the schema');
    success(callSeries('update-plugin-settings', ['expected_hash' => $legacyRead['expected_hash'], 'settings' => (object) ['series_toc_title' => 'Preserve unrelated legacy feature flags']]), 'Legacy feature unrelated patch');
    check(get_option('org_series_options')['enabled_pro_addons'] === $legacy['enabled_pro_addons'], 'Unrelated save erased stored legacy feature flags');
    update_option('org_series_options', $beforeLegacy);
    $GLOBALS['orgseries']->settings = get_option('org_series_options');
}

// Database read failures cannot masquerade as an empty, authorized cleanup set.
$dbTerm = success(callSeries('save-series', ['fields' => (object) ['name' => 'Database preflight audit']]), 'Database audit term');
$breakCleanupRead = static fn($query) => str_contains($query, 'SELECT DISTINCT post_id FROM') && str_contains($query, 'meta_key =') ? 'SELECT * FROM mcp_missing_cleanup_read_fixture' : $query;
global $wpdb;
$previousErrors = $wpdb->suppress_errors(true);
add_filter('query', $breakCleanupRead, PHP_INT_MAX);
$deniedDb = callSeries('delete-series', ['id' => $dbTerm['id'], 'expected_hash' => $dbTerm['expected_hash']]);
remove_filter('query', $breakCleanupRead, PHP_INT_MAX);
$wpdb->suppress_errors($previousErrors);
check(is_wp_error($deniedDb), 'Failed cleanup lookup authorized deletion');
check((bool) term_exists($dbTerm['id'], ppseries_get_series_slug()), 'Database preflight failure deleted a term');

// MCP input must not be accompanied by hidden writes through legacy form globals.
$contextTerm = success(callSeries('save-series', ['fields' => (object) ['name' => 'Request-isolation audit']]), 'Context term');
$contextCategory = success(callSeries('save-series', ['taxonomy' => 'category', 'fields' => (object) ['name' => 'Request-isolation category']]), 'Context category');
$contextPost = wp_insert_post(['post_type' => 'post', 'post_status' => 'publish', 'post_title' => 'Context post', 'post_content' => 'Keep this content']);
$contextPostState = success(callSeries('get-post-series', ['id' => $contextPost]), 'Context post token');
$contextSettings = success(callSeries('get-plugin-settings'), 'Context settings token');
$savedGet = $_GET;
$savedPost = $_POST;
$savedRequest = $_REQUEST;
$forged = ['is_series_save' => 1, 'post_series' => [$contextTerm['id']], 'series_part' => [$contextTerm['id'] => 1], 'serie_post_shorttitle' => ['Injected title'], 'term_order' => 777, 'tax_input' => ['series_group' => [$contextCategory['id']]], 'org_series_options' => ['kill_on_delete' => 1]];
$_GET = $_POST = $_REQUEST = $forged;
try {
    $contextTerm = success(callSeries('save-series', ['id' => $contextTerm['id'], 'expected_hash' => $contextTerm['expected_hash'], 'fields' => (object) ['description' => 'Only the validated description']]), 'Isolated term save');
    check($contextTerm['category_ids'] === [] && $contextTerm['order'] === 0, 'Legacy form globals injected term fields');
    $contextPostState = success(callSeries('update-series-block', ['id' => $contextPost, 'expected_hash' => $contextPostState['expected_hash'], 'block_name' => 'publishpress-series/post-details', 'attributes' => (object) []]), 'Isolated native post save');
    check(wp_get_post_series($contextPost) === [] && $contextPostState['short_title'] === '', 'Legacy form globals injected membership fields');
    success(callSeries('update-plugin-settings', ['expected_hash' => $contextSettings['expected_hash'], 'settings' => (object) ['series_toc_title' => 'Isolated settings save']]), 'Isolated native settings sanitizer');
    check($_GET === $forged && $_POST === $forged && $_REQUEST === $forged, 'Tool context failed to restore caller request globals');
} finally {
    $_GET = $savedGet;
    $_POST = $savedPost;
    $_REQUEST = $savedRequest;
}
// A native restore hook must not turn disappearance into successful restoration.
$restoreLayout = success(callSeries('save-layout', ['module' => 'post-details', 'title' => 'Restore disappearance fixture', 'settings' => (object) []]), 'Restore fixture');
wp_trash_post($restoreLayout['id']);
$restoreLayout = success(callSeries('get-layout', ['module' => 'post-details', 'id' => $restoreLayout['id']]), 'Restore fixture token');
$deleteDuringRestore = static function ($override, $post) use ($restoreLayout) {
    if ($post->ID === $restoreLayout['id']) {
        wp_delete_post($post->ID, true);
        return true;
    }
    return $override;
};
add_filter('pre_untrash_post', $deleteDuringRestore, 10, 2);
try {
    check(is_wp_error(callSeries('transition-layout', ['module' => 'post-details', 'id' => $restoreLayout['id'], 'expected_hash' => $restoreLayout['expected_hash'], 'operation' => 'untrash'])), 'Missing layout reported as restored');
} finally {
    remove_filter('pre_untrash_post', $deleteDuringRestore);
}

// Editor policy callbacks cannot silently overwrite a concurrently saved post.
$racePost = wp_insert_post(['post_type' => 'post', 'post_status' => 'draft', 'post_title' => 'Block concurrency fixture', 'post_content' => 'Original content']);
$raceState = success(callSeries('get-post-series', ['id' => $racePost]), 'Block concurrency token');
$changeDuringPolicy = static function ($allowed, $context) use ($racePost) {
    if ($context->post && $context->post->ID === $racePost) {
        wp_update_post(['ID' => $racePost, 'post_content' => 'Concurrent native edit']);
    }
    return $allowed;
};
add_filter('allowed_block_types_all', $changeDuringPolicy, 10, 2);
try {
    check(is_wp_error(callSeries('update-series-block', ['id' => $racePost, 'expected_hash' => $raceState['expected_hash'], 'block_name' => 'publishpress-series/post-details', 'attributes' => (object) []])), 'Block edit bypassed a concurrent native save');
    check(get_post_field('post_content', $racePost) === 'Concurrent native edit', 'Block edit overwrote concurrent content');
} finally {
    remove_filter('allowed_block_types_all', $changeDuringPolicy);
}

$raceState = success(callSeries('get-post-series', ['id' => $racePost]), 'Block permission token');
$denyRaceEdit = static fn($caps, $cap, $userId, $args) => $cap === 'edit_post' && (int) ($args[0] ?? 0) === $racePost ? ['do_not_allow'] : $caps;
$denyDuringPolicy = static function ($allowed) use ($denyRaceEdit) {
    add_filter('map_meta_cap', $denyRaceEdit, PHP_INT_MAX, 4);
    return $allowed;
};
add_filter('allowed_block_types_all', $denyDuringPolicy);
try {
    check(is_wp_error(callSeries('update-series-block', ['id' => $racePost, 'expected_hash' => $raceState['expected_hash'], 'block_name' => 'publishpress-series/post-details', 'attributes' => (object) []])), 'Block edit bypassed a revoked object capability');
    check(get_post_field('post_content', $racePost) === 'Concurrent native edit', 'Denied block edit changed content');
} finally {
    remove_filter('allowed_block_types_all', $denyDuringPolicy);
    remove_filter('map_meta_cap', $denyRaceEdit, PHP_INT_MAX);
}

// Reject malformed JSON for every discovered settings field before any mixed-patch write.
$formatSettings = success(callSeries('get-plugin-settings'), 'Format audit settings');
foreach ((array) $formatSettings['fields'] as $key => $field) {
    $schema = $field['schema'];
    $bad = match ($schema['type']) {
        'boolean' => 'false',
        'integer' => '12',
        'array' => (object) [],
        default => false,
    };
    $before = get_option('org_series_options');
    foreach ([$bad, null] as $invalid) {
        check(is_wp_error(callSeries('update-plugin-settings', ['expected_hash' => $formatSettings['expected_hash'], 'settings' => (object) array_merge(['series_toc_title' => 'Do not apply malformed patch'], [$key => $invalid])])), 'Malformed setting type accepted: ' . $key);
        check(get_option('org_series_options') === $before, 'Malformed setting type changed native data: ' . $key);
    }
    if (isset($schema['enum'])) {
        check(is_wp_error(callSeries('update-plugin-settings', ['expected_hash' => $formatSettings['expected_hash'], 'settings' => (object) [$key => '__unknown_choice__']])), 'Unknown setting choice accepted: ' . $key);
        check(get_option('org_series_options') === $before, 'Invalid choice changed native data: ' . $key);
    }
}

// Application-level permission denials remain effective on administrator sessions.
foreach (['manage_publishpress_series', 'manage_options'] as $capability) {
    $deny = static fn($caps, $cap) => $cap === $capability ? ['do_not_allow'] : $caps;
    add_filter('map_meta_cap', $deny, PHP_INT_MAX, 2);
    if ($capability === 'manage_publishpress_series') {
        check(is_wp_error(callSeries('get-plugin-settings')), 'Denied native settings read capability bypassed');
    } else {
        $settingsToken = success(callSeries('get-plugin-settings'), 'Native read permission remains independent');
        check(is_wp_error(callSeries('update-plugin-settings', ['expected_hash' => $settingsToken['expected_hash'], 'settings' => (object) ['series_toc_title' => 'Denied save'], 'dry_run' => true])), 'Denied native Settings API capability bypassed');
    }
    remove_filter('map_meta_cap', $deny, PHP_INT_MAX);
}
$user = wp_insert_user(['user_login' => 'mcp_subscriber', 'user_pass' => 'synthetic-fixture-only', 'role' => 'subscriber']);
wp_set_current_user($user);
check(is_wp_error(callSeries('get-content-choices')), 'Subscriber accessed private Series tools');
wp_set_current_user(1);
foreach (wp_get_abilities() as $ability) {
    if (str_starts_with($ability->get_name(), 'publishpress-series/')) {
        $meta = $ability->get_meta();
        check(empty($meta['public']) && empty($meta['show_in_rest']) && empty($meta['mcp']['public']), 'Public discovery enabled');
    }
}

$scopeFile = getenv('SERIES_MCP_CAPABILITIES_FILE');
if ($scopeFile && is_readable($scopeFile)) {
    require_once $scopeFile;
    update_option('cme_capabilities_application_password_capabilities', true);
    PP_Capabilities_Application_Password_Capabilities::instance();
    $scopeUserId = is_multisite() ? wp_insert_user(['user_login' => 'mcp_scope_admin', 'user_pass' => 'synthetic-only', 'role' => 'administrator']) : 1;
    wp_set_current_user($scopeUserId);
    $password = WP_Application_Passwords::create_new_application_password($scopeUserId, ['name' => 'Series isolated scope fixture']);
    check(!is_wp_error($password), 'Application-password fixture creation failed');
    $uuid = $password[1]['uuid'];
    $key = pp_capabilities_application_password_storage_key($scopeUserId, $uuid);
    update_option(pp_capabilities_application_password_option_name(), [$key => ['user_id' => $scopeUserId, 'uuid' => $uuid, 'denied' => ['manage_publishpress_series' => false]]]);
    do_action('application_password_did_authenticate', get_userdata($scopeUserId), $password[1]);
    check(is_wp_error(callSeries('get-plugin-settings')), 'Actual application-password restriction bypassed');
    update_option(pp_capabilities_application_password_option_name(), []);
    update_option('cme_capabilities_application_password_capabilities', false);
    WP_Application_Passwords::delete_application_password($scopeUserId, $uuid);
    unset($password);
    wp_set_current_user(1);
}

$bootstrap = dirname(__DIR__, 2) . '/includes-core/Mcp/bootstrap.php';
ob_start();
require $bootstrap;
check(ob_get_clean() === '', 'Duplicate bootstrap emitted output');
$command = escapeshellarg(PHP_BINARY) . ' -r ' . escapeshellarg('define("ABSPATH", "/isolated/"); require ' . var_export($bootstrap, true) . '; echo "missing-api-ok";');
check(trim((string) shell_exec($command)) === 'missing-api-ok', 'Missing Abilities API compatibility failed');
echo 'Series ' . getenv('SERIES_MCP_MODE') . ': ' . $assertions . " assertions passed through released Adapter.\n";
