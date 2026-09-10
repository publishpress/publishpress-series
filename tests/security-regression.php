<?php
/**
 * Run on a disposable WordPress site:
 * wp --skip-plugins --skip-themes --require=tests/load-plugin.php eval-file tests/security-regression.php
 */
if (!defined('WP_CLI') || !WP_CLI) {
    exit;
}
define('DOING_AJAX', true);
require_once dirname(__DIR__) . '/inc/orgSeries_updates.php';

function pps_check($condition, $message)
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}
function pps_ajax($callback, $data)
{
    $_POST = wp_slash($data);
    $_REQUEST = $_POST;
    $stop = function () {
        return function () {
            throw new RuntimeException('ajax-response');
        };
    };
    add_filter('wp_die_ajax_handler', $stop);
    ob_start();
    try {
        call_user_func($callback);
    } catch (RuntimeException $error) {
        if ($error->getMessage() !== 'ajax-response') {
            throw $error;
        }
    } finally {
        $output = ob_get_clean();
        remove_filter('wp_die_ajax_handler', $stop);
        $_POST = $_REQUEST = [];
    }
    return json_decode($output, true);
}

$posts = [];
$terms = [];
$users = [];
$original_user = get_current_user_id();
$suffix = wp_generate_password(10, false);
$dismiss_option = 'pps_security_test_' . $suffix;
try {
    foreach (['administrator', 'subscriber'] as $role) {
        $id = wp_insert_user([
            'user_login' => 'pps_security_' . $role . '_' . $suffix,
            'user_pass' => wp_generate_password(),
            'role' => $role,
        ]);
        pps_check(!is_wp_error($id), 'Could not create test user');
        $users[$role] = $id;
    }
    wp_set_current_user($users['administrator']);
    foreach ([ppseries_get_series_slug(), 'series_group', 'series_group'] as $index => $taxonomy) {
        $term = wp_insert_term('Security test ' . $suffix . ' ' . $index, $taxonomy);
        pps_check(!is_wp_error($term), 'Could not create test term');
        $terms[] = [$term['term_id'], $taxonomy];
    }
    $series_id = $terms[0][0];
    $group_ids = [$terms[1][0], $terms[2][0]];
    $post_id = wp_insert_post(['post_title' => 'Security test ' . $suffix, 'post_status' => 'publish']);
    $posts[] = $post_id;
    wp_set_object_terms($post_id, [$series_id], ppseries_get_series_slug());
    $group_post = orgseries_group_id($series_id);
    wp_set_object_terms($group_post, $group_ids, 'series_group');

    global $wpdb;
    foreach (['ASC', 'DESC', 'DESC; SELECT SLEEP(1) --'] as $order) {
        $wpdb->last_error = '';
        $result = get_series_ordered(['order' => $order, 'orderby' => 'term_id', 'postTypes' => ['post'], 'hide_empty' => false]);
        pps_check($wpdb->last_error === '', 'Series query failed: ' . $wpdb->last_error);
        pps_check(in_array($series_id, array_map('intval', wp_list_pluck($result, 'term_id')), true), 'Series query lost the fixture');
        foreach (['get_series_groups', 'get_old_series_groups'] as $query) {
            $args = ['order' => $order, 'orderby' => 'name; SELECT SLEEP(1)', 'include' => $group_ids, 'hide_empty' => false, 'hierarchical' => false, 'fields' => 'ids'];
            $result = $query($args);
            pps_check($wpdb->last_error === '', $query . ' failed: ' . $wpdb->last_error);
            pps_check(count($result) === 2, $query . ' lost included groups');
            unset($args['include']);
            $args['exclude'] = [$group_ids[0]];
            $result = $query($args);
            pps_check($wpdb->last_error === '', $query . ' exclusion SQL failed');
            pps_check(!in_array($group_ids[0], array_map('intval', $result), true), $query . ' ignored exclusion');
        }
    }
    $wpdb->last_error = '';
    $result = get_series_ordered(['postTypes' => ["post') OR 1=1 --"], 'hide_empty' => true]);
    pps_check($wpdb->last_error === '' && !$result, 'Post-type SQL input was not treated as data');
    $parent_map = get_series_groups(['include' => $group_ids, 'hide_empty' => false, 'fields' => 'id=>parent']);
    pps_check($wpdb->last_error === '' && count($parent_map) === 2, 'Group parent projection failed');

    $layouts = [
        ['PPS_Series_Post_Details_Ajax', 'pps_post_details', 'pps_series_post_details_', 'series-post-details-nonce', 'import_layout', 'update_preview', 'background_color'],
        ['PPS_Series_Post_Navigation_Ajax', 'pps_post_navigation', 'pps_post_navigation_', 'series-post-navigation-nonce', 'import_layout', 'update_preview', 'link_background_color'],
        ['PPS_Post_List_Box_AJAX', 'pps_post_list_box', 'pps_post_list_box_', 'post-list-box-nonce', 'ajax_import_post_list_box', 'ajax_update_preview', 'background_color'],
    ];
    foreach ($layouts as [$class, $type, $prefix, $nonce_action, $import, $preview, $color_key]) {
        $layout_id = wp_insert_post(['post_title' => 'Security layout ' . $suffix, 'post_type' => $type, 'post_status' => 'publish']);
        $posts[] = $layout_id;
        $data = ['post_id' => $layout_id, 'nonce' => wp_create_nonce($nonce_action), 'settings' => [$color_key => '#19ab73', 'text' => "Editor's <b>text</b> C:\\docs"]];
        $response = pps_ajax([$class, $import], $data);
        pps_check(!empty($response['success']), $class . ' rejected authorized import');
        $meta = get_post_meta($layout_id, $prefix . 'layout_meta_value', true);
        pps_check($meta[$color_key] === '#19ab73' && $meta['text'] === "Editor's text C:\\docs", $class . ' damaged or failed to sanitize imported fields');

        $data['settings'] = http_build_query([$color_key => '#19ab73', 'show_series_title' => 1, 'show_post_title' => 1]);
        $data['series_id'] = $series_id;
        $response = pps_ajax([$class, $preview], $data);
        pps_check(!empty($response['success']), $class . ' preview failed');
        pps_check(stripos($response['data']['preview'], '#19ab73') !== false, $class . ' stripped the encoded color from preview');

        wp_set_current_user($users['subscriber']);
        $data['nonce'] = wp_create_nonce($nonce_action);
        $data['settings'] = [$color_key => '#ffffff'];
        $response = pps_ajax([$class, $import], $data);
        pps_check(isset($response['success']) && !$response['success'], $class . ' allowed a subscriber import');
        pps_check(get_post_meta($layout_id, $prefix . 'layout_meta_value', true) === $meta, $class . ' changed settings without edit permission');
        wp_set_current_user($users['administrator']);
        $data['nonce'] = 'invalid';
        $response = pps_ajax([$class, $import], $data);
        pps_check($response === null, $class . ' accepted an invalid nonce');
    }
    $updater = (new ReflectionClass('PluginUpdateChecker'))->newInstanceWithoutConstructor();
    $updater->dismiss_upgrade = $dismiss_option;
    pps_ajax([$updater, 'dashboard_dismiss_upgrade'], ['version' => 'test', 'nonce' => 'invalid']);
    pps_check(get_option($dismiss_option) === false, 'Dismissal accepted an invalid nonce');
    wp_set_current_user($users['subscriber']);
    pps_ajax([$updater, 'dashboard_dismiss_upgrade'], ['version' => 'test', 'nonce' => wp_create_nonce($dismiss_option)]);
    pps_check(get_option($dismiss_option) === false, 'Subscriber dismissed upgrade');
    wp_set_current_user($users['administrator']);
    pps_ajax([$updater, 'dashboard_dismiss_upgrade'], ['version' => "3.1.4'<b>test</b>", 'nonce' => wp_create_nonce($dismiss_option)]);
    pps_check(get_option($dismiss_option) === ["3.1.4'test"], 'Authorized dismissal failed sanitization');
    WP_CLI::success('Security regressions passed: SQL, layout imports/previews, permissions, and upgrade dismissal.');
} finally {
    $_POST = $_GET = $_REQUEST = [];
    wp_set_current_user($users['administrator'] ?? $original_user);
    foreach ($posts as $id) {
        wp_delete_post($id, true);
    }
    foreach ($terms as [$id, $taxonomy]) {
        wp_delete_term($id, $taxonomy);
    }
    delete_option($dismiss_option);
    require_once ABSPATH . 'wp-admin/includes/user.php';
    foreach ($users as $id) {
        wp_delete_user($id);
    }
    wp_set_current_user($original_user);
}
