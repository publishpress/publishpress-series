<?php
if (!defined('OS_PUBLISHER_VERSION')) {
    define('OS_PUBLISHER_VERSION', '2.2.5.rc.000');
}

if (!function_exists('series_issue_manager_part')) {
    function series_issue_manager_part($series_ID, $post_IDs)
    {
        $post_ids = ppseries_publisher_parse_post_ids($post_IDs);
        if ($post_ids === [] || !ppseries_publisher_user_can_all_posts($post_ids, 'edit_post')) {
            return;
        }

        foreach ($post_ids as $post_ID) {
            if (
                in_array(get_post_status($post_ID), ['draft', 'future', 'pending'], true)
                && !current_user_can('publish_post', $post_ID)
            ) {
                return;
            }
        }

        $post_IDs = implode(',', $post_ids);

        //delete all series part
        foreach ($post_ids as $post_ID) {
            $part_key = apply_filters('orgseries_part_key', SERIES_PART_KEY, $series_ID);
            delete_post_meta($post_ID, $part_key);
            add_post_meta($post_ID, $part_key, '');
        }

        // $post_IDs should have all pending posts' IDs in the series
        $counter = 0;
        $current_sn = 0;
        foreach (explode(',', $post_IDs) as $post_ID) {
            if (in_array(get_post_status($post_ID), ['draft', 'future', 'pending'])) {
                wp_update_post(
                    array(
                        'ID' => $post_ID,
                        'post_date' => date('Y-m-d H:i:s', strtotime(current_time('mysql'))),
                        'post_date_gmt' => '',
                        'post_status' => 'publish'
                    )
                );
                $all_series = wp_get_object_terms($post_IDs, ppseries_get_series_slug(), ['fields' => 'ids']);
                foreach ($all_series as $all_serie) {
                    if ((int) $all_serie !== $series_ID) {
                        set_series_order($all_serie, $post_ID, 0, true);
                    }
                }
            }
            $current_sn++;
            $post_ID = (int) $post_ID;
            $post = get_post($post_ID);
            publisher_wp_set_post_series($post, true, $post_ID, $series_ID, false);
            $counter++;
        }
    }
}

if (!function_exists('series_issue_manager_pending_order')) {
    function series_issue_manager_pending_order($series_ID, $post_IDs)
    {
        $post_ids = ppseries_publisher_parse_post_ids($post_IDs);
        if ($post_ids === [] || !ppseries_publisher_user_can_all_posts($post_ids, 'edit_post')) {
            return;
        }

        $current_part = 0;
        foreach ($post_ids as $post_ID) {
            $current_part++;
            $part_key = apply_filters('orgseries_pending_part_key', '_pending_series_part', $series_ID);
            update_post_meta($post_ID, $part_key, $current_part);
        }
    }
}

if (!function_exists('series_issue_manager_publish')) {
    function series_issue_manager_publish($series_ID, $post_IDs, $pub_time, &$published, &$unpublished)
    {
        $post_ids = ppseries_publisher_parse_post_ids($post_IDs);
        if ($post_ids === [] || !ppseries_publisher_user_can_all_posts($post_ids, 'publish_post')) {
            return;
        }

        // take the series out of the unpublished list
        $key = array_search($series_ID, $unpublished);
        if (false !== $key) {
            array_splice($unpublished, $key, 1);
            update_option('im_unpublished_series', $unpublished);
        }
        //if ( !in_array( $series_ID, $published ) )
        {
            // add to the published list
            $published[] = $series_ID;
            sort($published);
            update_option('im_published_series', $published);

            // see if we have a valid publication date/time
            $publish_at = strtotime($pub_time['aa'] . '-' . $pub_time['mm'] . '-' . $pub_time['jj'] . ' ' . $pub_time['hh'] . ':' . $pub_time['mn']);

            if (!$publish_at) {
                $publish_at = strtotime(current_time('mysql'));
            }

            // $post_IDs should have all pending posts' IDs in the series
            $post_ids_in_series = get_objects_in_term($series_ID, ppseries_get_series_slug());
            $counter = 0;
            $current_sn = count($post_ids_in_series) - count($post_ids);

            foreach ($post_ids as $post_ID) {
                $current_sn++;
                $post = get_post($post_ID);
                if (!$post) {
                    return;
                }
                // set the date to about the appropriate time, keeping a small gap so posts stay in order
                wp_update_post(
                    array(
                        'ID' => $post->ID,
                        'post_date' => date('Y-m-d H:i:s', $publish_at - ($counter + 1)),
                        'post_date_gmt' => '',
                        'post_status' => 'publish'
                    )
                );

                if ($publish_at > strtotime(current_time('mysql'))) {
                    // scheduled
                    publisher_wp_set_post_series($post, true, $post_ID, $series_ID, false);
                } else {
                    publisher_wp_set_post_series($post, true, $post_ID, $series_ID, false);
                }
                $counter++;
            }
        }
    }
}

if (!function_exists('series_issue_manager_unpublish')) {
    function series_issue_manager_unpublish($series_ID, &$published, &$unpublished)
    {
        $posts = get_objects_in_term($series_ID, ppseries_get_series_slug());
        if (is_wp_error($posts) || !ppseries_publisher_user_can_all_posts($posts, 'edit_post')) {
            return;
        }

        // take the series out of the published list
        $key = array_search($series_ID, $published);
        if (false !== $key) {
            array_splice($published, $key, 1);
            update_option('im_published_series', $published);
        }
        //if ( !in_array( $series_ID, $unpublished ) )
        {
            // add to the unpublished list
            $unpublished[] = $series_ID;
            sort($unpublished);
            update_option('im_unpublished_series', $unpublished);

            // change all published posts in the series to pending
            foreach ($posts as $post) {
                if (!empty(get_post_status($post)) && get_post_status($post) !== 'draft') {
                    wp_update_post(
                        array(
                            'ID' => $post,
                            'post_status' => 'pending'
                        )
                    );
                    publisher_wp_set_post_series($post, true, $post, $series_ID, true);
                }
            }
        }
    }
}


if (!function_exists('publisher_wp_set_post_series')) {
    function publisher_wp_set_post_series($post, $update, $post_ID = 0, $series_id = array(), $remove_part = false, $part = false)
    {
        $post_ID = (int) $post_ID;
        $post_series = is_array($series_id) ? $series_id : array($series_id);
        $post_series = os_strarr_to_intarr($post_series);


        foreach ($post_series as $ser_id) {
            if ($remove_part) {
                $s_pt = '';
            } else {
                $s_pt = wp_series_part($post_ID, $ser_id);
            }

            if ($remove_part) {
                $series_part_key = apply_filters('orgseries_part_key', SERIES_PART_KEY, $ser_id);
                delete_post_meta($post_ID, $series_part_key);
            } else {
                set_series_order($ser_id, $post_ID, $s_pt, true);
            }
        }

        return;
    }
}

if (!function_exists('series_issue_manager_publish_intercept')) {
    function series_issue_manager_publish_intercept($post_ID, $post)
    {
        /*
        $unpublished = get_option( 'im_unpublished_series' );
        $publishable = TRUE;
        // check if post is in an unpublished series

        foreach ( get_the_series($post_ID) as $series ) {
         if ( in_array( $series->term_id, $unpublished ) ) {
          $publishable = FALSE;
          break;
        }
        }
        // if post is in an unpublished series, change its status to 'pending' instead of 'publish'
        if ( !$publishable ) {
        if ($post->post_status != 'publish') return;

        wp_update_post( array(
          'ID' => $post_ID,
          'post_status' => 'pending'
        ) );
        }
        return;*/
    }
}

if (!function_exists('series_issue_manager_add_series_form')) {
    function series_issue_manager_add_series_form()
    {
        $published = get_option('im_published_series');
        $unpublished = get_option('im_unpublished_series'); ?>
        <div class="form-field" style="display:none;">
            <label for="series_publish">
                <p><?php _e('Create as unpublished:', 'organize-series') ?>
                    <input style="float:left; width: 20px;" name="series_publish" id="series_publish" type="checkbox" value="unpublish" />
                </p>
                <p><?php _e('When checked, all posts you assign to this series will remain unpublished until you publish the entire series.', 'organize-series'); ?>
                </p>
            </label>
        </div><?php
    }
}


if (!function_exists('series_issue_set_publish_status')) {
    function series_issue_set_publish_status($series_id, $taxonomy_id)
    {
        global $_POST;
        $series_publish = isset($_POST['series_publish']) && is_scalar($_POST['series_publish'])
            ? sanitize_text_field(wp_unslash($_POST['series_publish']))
            : null;
        //If "Unpublish" is selected, put series Id into Unpublished array so that new posts in this
        //Series are not accidentally published
        if ($series_publish == 'unpublish') {
            $unpublished = get_option('im_unpublished_series');

            if (!in_array($series_id, $unpublished)) {
                // add to the unpublished list
                $unpublished[] = $series_id;
                sort($unpublished);
                update_option('im_unpublished_series', $unpublished);
            }
        }
    }
}



/**
 * Secondary admin notices function for use with admin_notices hook.
 *
 * Constructs admin notice HTML.
 *
 * @param  string $message Message to use in admin notice. Optional. Default empty string.
 * @param  bool   $success Whether or not a success. Optional. Default true.
 * @return mixed
 */
function pps_publisher_admin_notices_helper($message = '', $success = true)
{
    $class = [];
    $class[] = $success ? 'updated' : 'error';
    $class[] = 'notice is-dismissible';

    $messagewrapstart = '<div id="message" class="' . esc_attr(implode(' ', $class)) . '"><p>';

    $messagewrapend = '</p></div>';

    $action = '';

    /**
     * Filters the custom admin notice for pps_publisher.
     *
     * @param string $value Complete HTML output for notice.
     * @param string $action Action whose message is being generated.
     * @param string $message The message to be displayed.
     * @param string $messagewrapstart Beginning wrap HTML.
     * @param string $messagewrapend Ending wrap HTML.
     */
    return apply_filters(
        'pps_publisher_admin_notice',
        $messagewrapstart . $message . $messagewrapend,
        $action,
        $message,
        $messagewrapstart,
        $messagewrapend
    );
}


function pps_publisher_unpublished_success_message_admin_notice()
{
    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    echo pps_publisher_admin_notices_helper(
        esc_html__('The posts in your series were successfully unpublished.', 'organize-series')
    );
}


function pps_publisher_order_success_message_admin_notice()
{
    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    echo pps_publisher_admin_notices_helper(
        esc_html__('Congratulations. Your series order was updated successfully.', 'organize-series')
    );
}

function pps_publisher_order_published_success_message_admin_notice()
{
    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    echo pps_publisher_admin_notices_helper(
        esc_html__('Congratulations. Your posts were published successfully.', 'organize-series')
    );
}


function pps_publisher_delete_success_message_admin_notice()
{
    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    echo pps_publisher_admin_notices_helper(
        esc_html__('Post moved to the Trash.', 'organize-series')
    );
}


function pps_publisher_published_success_message_admin_notice()
{
    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    echo pps_publisher_admin_notices_helper(
        esc_html__('Congratulations. Your posts were published successfully.', 'organize-series')
    );
}

function pps_publisher_scheduled_success_message_admin_notice()
{
    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    echo pps_publisher_admin_notices_helper(
        esc_html__('Congratulations. Your posts were scheduled successfully.', 'organize-series')
    );
}

function ppseries_publisher_parse_post_ids($raw)
{
    if (is_array($raw)) {
        $parts = $raw;
    } else {
        $parts = explode(',', (string) $raw);
    }

    $ids = [];
    foreach ($parts as $part) {
        $id = absint($part);
        if ($id > 0) {
            $ids[] = $id;
        }
    }

    return array_values(array_unique($ids));
}

function ppseries_publisher_user_can_all_posts($post_ids, $capability)
{
    foreach (ppseries_publisher_parse_post_ids($post_ids) as $post_id) {
        if (!current_user_can($capability, $post_id)) {
            return false;
        }
    }

    return true;
}

function ppseries_publisher_die_forbidden()
{
    wp_die(
        esc_html__('Sorry, you are not allowed to perform this action.', 'organize-series'),
        '',
        ['response' => 403]
    );
}

function ppseries_publisher_is_post_request()
{
    $method = isset($_SERVER['REQUEST_METHOD'])
        ? strtoupper(sanitize_text_field(wp_unslash($_SERVER['REQUEST_METHOD'])))
        : '';

    return $method === 'POST';
}

function ppseries_publisher_nonce_field()
{
    wp_nonce_field('pps-publisher-manage-issues');
}

function ppseries_publisher_require_manage_series_nonce()
{
    check_admin_referer('pps-publisher-manage-issues');
}

function ppseries_publisher_require_series_admin()
{
    if (!current_user_can('manage_publishpress_series')) {
        ppseries_publisher_die_forbidden();
    }
}

function ppseries_publisher_redirect(array $args = [])
{
    $url = add_query_arg(
        array_merge(
            [
                'page' => 'manage-issues',
            ],
            $args
        ),
        admin_url('edit.php')
    );

    wp_safe_redirect($url);
    exit;
}

function pps_publisher_filter_removable_query_args_notice(array $args)
{
    $args[] = 'pps_publisher_notice';
    return $args;
}

function ppseries_publisher_admin_init()
{
    if (!isset($_REQUEST['page']) || $_REQUEST['page'] !== 'manage-issues') {
        return;
    }

    if (!current_user_can('publish_posts')) {
        return;
    }

    $notice = isset($_GET['pps_publisher_notice']) ? sanitize_key(wp_unslash($_GET['pps_publisher_notice'])) : '';
    $notice_callbacks = [
        'unpublished' => 'pps_publisher_unpublished_success_message_admin_notice',
        'published' => 'pps_publisher_published_success_message_admin_notice',
        'scheduled' => 'pps_publisher_scheduled_success_message_admin_notice',
        'order' => 'pps_publisher_order_success_message_admin_notice',
        'order_published' => 'pps_publisher_order_published_success_message_admin_notice',
        'deleted' => 'pps_publisher_delete_success_message_admin_notice',
    ];

    if ($notice === 'pending_order') {
        add_action('admin_notices', function () {
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            echo pps_publisher_admin_notices_helper(
                esc_html__('Congratulations. Your series order was updated successfully.', 'organize-series')
            );
        });
        add_filter('removable_query_args', 'pps_publisher_filter_removable_query_args_notice');
    } elseif (isset($notice_callbacks[$notice])) {
        add_action('admin_notices', $notice_callbacks[$notice]);
        add_filter('removable_query_args', 'pps_publisher_filter_removable_query_args_notice');
    }

    if (
        (
            (isset($_REQUEST['action']) && $_REQUEST['action'] === 'pps-publisher-delete-posts')
            || (isset($_REQUEST['part_action']) && $_REQUEST['part_action'] === 'pps-publisher-delete-posts')
        )
        && isset($_REQUEST['series_post'])
        && isset($_REQUEST['_wpnonce'])
    ) {
        $nonce = sanitize_text_field(wp_unslash($_REQUEST['_wpnonce']));
        if (
            !wp_verify_nonce($nonce, 'bulk-series-parts')
            && !wp_verify_nonce($nonce, 'bulk-series-pendings')
        ) {
            ppseries_publisher_die_forbidden();
        }

        $raw_series_posts = wp_unslash($_REQUEST['series_post']);
        if (is_array($raw_series_posts)) {
            $raw_series_posts = array_map('sanitize_text_field', $raw_series_posts);
        } else {
            $raw_series_posts = sanitize_text_field($raw_series_posts);
        }
        $post_ids = ppseries_publisher_parse_post_ids($raw_series_posts);
        if ($post_ids === [] || !ppseries_publisher_user_can_all_posts($post_ids, 'delete_post')) {
            ppseries_publisher_die_forbidden();
        }

        foreach ($post_ids as $post_id) {
            wp_trash_post($post_id);
        }

        $series_id = isset($_REQUEST['series_ID']) ? absint($_REQUEST['series_ID']) : 0;
        $view = (isset($_REQUEST['action']) && $_REQUEST['action'] === 'list') ? 'list' : 'part';
        $redirect = ['pps_publisher_notice' => 'deleted'];
        if ($series_id > 0) {
            $redirect['action'] = $view;
            $redirect['series_ID'] = $series_id;
        }
        ppseries_publisher_redirect($redirect);
    }

    $action = isset($_REQUEST['action']) ? sanitize_key(wp_unslash($_REQUEST['action'])) : '';
    $has_posts = !empty($_REQUEST['posts']);
    $mutating = in_array($action, ['publish', 'unpublish', 'ignore'], true)
        || ($action === 'order' && $has_posts)
        || ($action === 'list' && $has_posts);

    if (!$mutating) {
        return;
    }

    if (!ppseries_publisher_is_post_request()) {
        return;
    }

    ppseries_publisher_require_manage_series_nonce();

    $series_id = isset($_POST['series_ID']) ? absint($_POST['series_ID']) : 0;
    if ($series_id <= 0) {
        ppseries_publisher_die_forbidden();
    }

    $published = get_option('im_published_series');
    $unpublished = get_option('im_unpublished_series');
    if (!is_array($published)) {
        $published = [];
    }
    if (!is_array($unpublished)) {
        $unpublished = [];
    }

    $raw_posts = isset($_POST['posts']) ? sanitize_text_field(wp_unslash($_POST['posts'])) : '';
    $post_ids = ppseries_publisher_parse_post_ids($raw_posts);

    switch ($action) {
        case 'unpublish':
            ppseries_publisher_require_series_admin();
            $posts = get_objects_in_term($series_id, ppseries_get_series_slug());
            if (is_wp_error($posts) || !ppseries_publisher_user_can_all_posts($posts, 'edit_post')) {
                ppseries_publisher_die_forbidden();
            }
            series_issue_manager_unpublish($series_id, $published, $unpublished);
            ppseries_publisher_redirect(['pps_publisher_notice' => 'unpublished']);
            break;

        case 'publish':
            ppseries_publisher_require_series_admin();
            if ($post_ids === []) {
                ppseries_publisher_redirect(['action' => 'list', 'series_ID' => $series_id]);
            }
            if (!ppseries_publisher_user_can_all_posts($post_ids, 'publish_post')) {
                ppseries_publisher_die_forbidden();
            }

            $pub_time = [
                'mm' => isset($_POST['mm']) ? sanitize_text_field(wp_unslash($_POST['mm'])) : null,
                'jj' => isset($_POST['jj']) ? sanitize_text_field(wp_unslash($_POST['jj'])) : null,
                'aa' => isset($_POST['aa']) ? sanitize_text_field(wp_unslash($_POST['aa'])) : null,
                'hh' => isset($_POST['hh']) ? sanitize_text_field(wp_unslash($_POST['hh'])) : null,
                'mn' => isset($_POST['mn']) ? sanitize_text_field(wp_unslash($_POST['mn'])) : null,
            ];
            $publish_at = strtotime($pub_time['aa'] . '-' . $pub_time['mm'] . '-' . $pub_time['jj'] . ' ' . $pub_time['hh'] . ':' . $pub_time['mn']);
            $notice_key = ($publish_at && $publish_at > strtotime(current_time('mysql'))) ? 'scheduled' : 'published';

            series_issue_manager_publish($series_id, implode(',', $post_ids), $pub_time, $published, $unpublished);
            ppseries_publisher_redirect(['pps_publisher_notice' => $notice_key]);
            break;

        case 'ignore':
            ppseries_publisher_require_series_admin();
            $key = array_search($series_id, $published);
            if (false !== $key) {
                array_splice($published, $key, 1);
                update_option('im_published_series', $published);
            }
            $key = array_search($series_id, $unpublished);
            if (false !== $key) {
                array_splice($unpublished, $key, 1);
                update_option('im_unpublished_series', $unpublished);
            }
            ppseries_publisher_redirect();
            break;

        case 'order':
            if ($post_ids === []) {
                ppseries_publisher_redirect(['action' => 'part', 'series_ID' => $series_id]);
            }
            if (!ppseries_publisher_user_can_all_posts($post_ids, 'edit_post')) {
                ppseries_publisher_die_forbidden();
            }

            $subaction = isset($_POST['subaction']) ? sanitize_key(wp_unslash($_POST['subaction'])) : '';
            if ($subaction === 'pending_order') {
                series_issue_manager_pending_order($series_id, implode(',', $post_ids));
                ppseries_publisher_redirect([
                    'action' => 'part',
                    'series_ID' => $series_id,
                    'pps_publisher_notice' => 'pending_order',
                ]);
            }

            foreach ($post_ids as $post_id) {
                $status = get_post_status($post_id);
                if (
                    in_array($status, ['draft', 'future', 'pending'], true)
                    && !current_user_can('publish_post', $post_id)
                ) {
                    ppseries_publisher_die_forbidden();
                }
            }

            series_issue_manager_part($series_id, implode(',', $post_ids));
            ppseries_publisher_redirect([
                'action' => 'part',
                'series_ID' => $series_id,
                'pps_publisher_notice' => ($subaction === 'published') ? 'order_published' : 'order',
            ]);
            break;

        case 'list':
            if ($post_ids === []) {
                ppseries_publisher_redirect(['action' => 'list', 'series_ID' => $series_id]);
            }
            if (!ppseries_publisher_user_can_all_posts($post_ids, 'edit_post')) {
                ppseries_publisher_die_forbidden();
            }

            $subaction = isset($_POST['subaction']) ? sanitize_key(wp_unslash($_POST['subaction'])) : '';
            if ($subaction === 'pending_order') {
                series_issue_manager_pending_order($series_id, implode(',', $post_ids));
            }
            ppseries_publisher_redirect([
                'action' => 'list',
                'series_ID' => $series_id,
                'pps_publisher_notice' => 'pending_order',
            ]);
            break;
    }
}

add_action('' . ppseries_get_series_slug() . '_add_form_fields', 'series_issue_manager_add_series_form');
//add_filter('save_post', 'series_issue_manager_publish_intercept',3,2);
add_action('created_' . ppseries_get_series_slug() . '', 'series_issue_set_publish_status', 2, 2);
add_action('admin_init', 'ppseries_publisher_admin_init');



class PPS_Publisher_Admin
{
    // class instance
    public static $instance;

    // WP_List_Table object
    public $series_part_table;
    public $series_pending_table;
    public $series_publish_table;

    /**
     * Constructor
     *
     * @return void
     * @author Olatechpro
     */
    public function __construct()
    {
        add_action('admin_menu', [$this, 'admin_menu']);
    }

    /**
     * Singleton instance
     */
    public static function get_instance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Add WP admin menu for Tags
     *
     * @return void
     * @author Olatechpro
     */
    public function admin_menu()
    {
        $page = add_submenu_page(
            'edit.php',
            __('Manage Series Issues', 'organize-series'),
            __('Manage Series', 'organize-series'),
            'publish_posts',
            'manage-issues',
            [$this, 'series_issue_manager_admin']
        );
        add_action("admin_print_scripts-$page", [$this, 'series_issue_manager_scripts']);

        add_action("load-$page", [$this, 'screen_option']);
    }

    public function series_issue_manager_scripts()
    {

        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-ui-sortable');

        wp_enqueue_script("series_im_sort_articles", plugin_dir_url(__FILE__) . "/js/series_im_sort_articles.js", array('jquery-ui-sortable'), ORG_SERIES_VERSION, true);
        wp_enqueue_style('series_im_sort_articles', plugin_dir_url(__FILE__) . '/css/series_im_sort_articles.css', array(), ORG_SERIES_VERSION, 'all');
    }

    /**
     * Screen options
     */
    public function screen_option()
    {
        if (isset($_GET['action']) && $_GET['action'] === 'list') {
            $option = 'per_page';
            $args = [
                'label' => esc_html__('Number of items per page', 'organize-series'),
                'default' => 999,
                'option' => 'pp_series_publisher_per_page'
            ];
            include_once 'series-publish-post-table.php';
            $this->series_publish_table = new PPS_Publisher_Post_Publish_Table();
            add_screen_option($option, $args);
        }
        if (isset($_GET['action']) && ($_GET['action'] === 'part' || $_GET['action'] === 'order')) {
            include_once 'series-part-post-table.php';
            include_once 'series-pending-post-table.php';
            $this->series_part_table = new PPS_Publisher_Post_Part_Table();
            $this->series_pending_table = new PPS_Publisher_Post_Pending_Table();
        }
    }

    public function series_issue_manager_admin()
    {
        $published = get_option('im_published_series');
        $unpublished = get_option('im_unpublished_series');
        $series = get_series('orderby=name&hide_empty=0');

        // Make sure the options exist
        if ($published === false) {
            $published = array();
            update_option('im_published_series', $published);
        }
        if ($unpublished === false) {
            $unpublished = array();
            update_option('im_unpublished_series', $unpublished);
        }

        $series_ID = isset($_GET['series_ID']) ? absint($_GET['series_ID']) : 0;
        $action = isset($_GET['action']) ? sanitize_key(wp_unslash($_GET['action'])) : '';

        if ($series_ID > 0 && in_array($action, ['part', 'order'], true)) {
            $this->ppseries_publisher_part_output($series_ID);
            $this->ppseries_publisher_pending_output($series_ID);
        } elseif ($series_ID > 0 && $action === 'list') {
            $this->ppseries_publisher_publish_output($series_ID);
        } else {
            include_once 'series_im_admin_main.php';
        }
    }

    public function ppseries_publisher_publish_output($series_ID)
    {
        $series = get_term($series_ID);
        $this->series_publish_table->prepare_items();
        ?>

        <div class="wrap pp-series-publisher-wrap series-publish">

            <h1><?php esc_html_e('Publishing Series:', 'organize-series'); ?>
                <?php echo esc_html($series->name); ?>
            </h1>
            <?php
        if (isset($_REQUEST['s']) && $search = esc_attr(sanitize_text_field(wp_unslash($_REQUEST['s'])))) {
            /* translators: %s: search keywords */
            printf(' <span class="subtitle">' . esc_html__(
                'Search results for &#8220;%s&#8221;',
                'organize-series'
            ) . '</span>', esc_html($search));
        }
        ?>
            <div id="poststuff">

                <div id="post-body" class="metabox-holder columns-2">

                    <div id="post-body-content" style="position: relative;">

                        <hr class="wp-header-end">
                        <div id="ajax-response"></div>
                        <form class="search-form wp-clearfix series-publisher-search" method="get">
                            <?php $this->series_publish_table->search_box(esc_html__('Search', 'organize-series'), 'search'); ?>
                        </form>
                        <div class="clear"></div>

                        <form action="<?php echo esc_url(add_query_arg('', '')); ?>" method="post">
                            <?php

                        if (!empty($_REQUEST['orderby'])) {
                            echo '<input type="hidden" name="orderby" value="' . esc_attr(sanitize_text_field($_REQUEST['orderby'])) . '" />';
                        }
                        if (!empty($_REQUEST['order'])) {
                            echo '<input type="hidden" name="order" value="' . esc_attr(sanitize_text_field($_REQUEST['order'])) . '" />';
                        }
                        if (!empty($_REQUEST['page'])) {
                            echo '<input type="hidden" name="page" value="' . esc_attr(sanitize_text_field($_REQUEST['page'])) . '" />';
                        }
        ?>
                            <?php $this->series_publish_table->display(); //Display the table
        ?>
                        </form>
                        <div class="form-wrap edit-term-notes">
                            <p><?php esc_html__('Description here.', 'simple-tags') ?></p>
                        </div>
                    </div>

                    <div id="postbox-container-1" class="postbox-container">
                        <div id="side-sortables" class="meta-box-sortables ui-sortable" style="">


                        <?php if (current_user_can('manage_publishpress_series')) : ?>
                        <div id="submitdiv" class="postbox">
                                <div class="postbox-header">
                                    <h2 class="hndle ui-sortable-handle"><?php esc_html_e('Publish Series', 'organize-series'); ?>
                                    </h2>
                                </div>
                                <form method="post" action="<?php echo esc_url(admin_url('edit.php?page=manage-issues')); ?>">
                                    <?php ppseries_publisher_nonce_field(); ?>
                                    <div class="hidden-fields">
                                        <input type="hidden" name="page" id="im_publish_page" value="manage-issues" />
                                        <input type="hidden" name="action" id="im_publish_action" value="publish" />
                                        <input type="hidden" name="series_ID" id="im_publish_series_ID" value="<?php echo esc_attr($series_ID); ?>" />
                                        <input type="hidden" name="posts" class="im_publish_posts" value="" />
                                    </div>
                                    <div class="inside">
                                        <div id="minor-publishing">
                                            <div id="misc-publishing-actions">
                                                <div class="misc-pub-section misc-pub-section-last" style="margin:0;">
                                                    <p><?php _e('Publication Date/Time:', 'organize-series'); ?></p>
                                                    <div id='timestampdiv'>
                                                        <?php
                                                        global $wp_locale;
                                                        $time_adj = time() + (get_option('gmt_offset') * 3600);
                                                        $jj = gmdate('d', $time_adj);
                                                        $mm = gmdate('m', $time_adj);
                                                        $aa = gmdate('Y', $time_adj);
                                                        $hh = gmdate('H', $time_adj);
                                                        $mn = gmdate('i', $time_adj);
                                                        $ss = gmdate('s', $time_adj);
                                                        $publish_month = "<select id=\"mm\" name=\"mm\">\n";
                                                        for ($i = 1; $i < 13; $i = $i + 1) {
                                                            $publish_month .= "\t\t\t" . '<option value="' . zeroise($i, 2) . '"';
                                                            if ($i == $mm) {
                                                                $publish_month .= ' selected="selected"';
                                                            }
                                                            $publish_month .= '>' . $wp_locale->get_month($i) . "</option>\n";
                                                        }
                                                        $publish_month .= '</select>';
                                                        $publish_day = '<input type="text" id="jj" name="jj" value="' . esc_attr($jj) . '" size="2" maxlength="2" autocomplete="off"  />';
                                                        $publish_year = '<input type="text" id="aa" name="aa" value="' . esc_attr($aa) . '" size="4" maxlength="5" autocomplete="off"  />';
                                                        $hour = '<input type="text" id="hh" name="hh" value="' . esc_attr($hh) . '" size="2" maxlength="2" autocomplete="off"  />';
                                                        $minute = '<input type="text" id="mn" name="mn" value="' . esc_attr($mn) . '" size="2" maxlength="2" autocomplete="off"  />';
                                                        printf(__('%1$s%2$s, %3$s @ %4$s : %5$s'), $publish_month, $publish_day, $publish_year, $hour, $minute); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                                                        ?>
                                                    </div>
                                                </div>
                                                <div class="clear"></div>
                                            </div>
                                        </div>
                                        <div id="major-publishing-actions">
                                            <div id="publishing-action"><input type="submit" value="<?php esc_attr_e('Publish Series', 'organize-series'); ?>" class="button-primary" id="" name="publish" onclick="var im_post_IDs = new Array(); jQuery('.pp-series-publisher-wrap table.series-parts tbody tr').each( function(){im_post_IDs.push(jQuery(this).attr('id').substring(5));});jQuery('.im_publish_posts').val(im_post_IDs.join(','));" /></div>
                                            <div class="clear"></div>
                                        </div>
                                    </div>
                                </form>
                        </div>
                        <?php endif; ?>

                            <div id="submitdiv" class="postbox">
                                <div class="postbox-header">
                                    <h2 class="hndle ui-sortable-handle"><?php esc_html_e('Series Order', 'organize-series'); ?>
                                    </h2>
                                </div>
                                <form method="post" action="<?php echo esc_url(admin_url('edit.php?page=manage-issues')); ?>">
                                    <?php ppseries_publisher_nonce_field(); ?>
                                    <div class="hidden-fields">
                                        <input type="hidden" name="page" id="im_publish_page" value="manage-issues" />
                                        <input type="hidden" name="action" id="im_publish_action" value="list" />
                                        <input type="hidden" name="subaction" id="im_publish_subaction" value="pending_order" />
                                        <input type="hidden" name="series_ID" id="im_publish_series_ID" value="<?php echo esc_attr($series_ID); ?>" />
                                        <input type="hidden" name="posts" class="im_publish_posts" value="" />
                                    </div>
                                    <div class="inside">
                                        <div id="minor-publishing"></div>
                                        <div id="major-publishing-actions">
                                            <div id="publishing-action"><input type="submit" value="<?php esc_attr_e('Update Order', 'organize-series'); ?>" class="button-primary" id="" name="publish" onclick="var im_post_IDs = new Array(); jQuery('.pp-series-publisher-wrap table.series-parts tbody tr').each( function(){im_post_IDs.push(jQuery(this).attr('id').substring(5));});jQuery('.im_publish_posts').val(im_post_IDs.join(','));" /></div>
                                            <div class="clear"></div>
                                        </div>
                                    </div>
                                </form>

                    </div>

                </div>

                <br class="clear" />
            </div>

        </div>

    <?php
    }


    public function ppseries_publisher_part_output($series_ID)
    {
        $series = get_term($series_ID);
        $this->series_part_table->prepare_items();
        ?>

        <div class="wrap pp-series-publisher-wrap series-order">

            <h1><?php esc_html_e('Series Order:', 'organize-series'); ?>
                <?php echo esc_html($series->name); ?>
            </h1>
            <p class="description"><?php _e('Drag the post names into the order you want them to be in the series, from the first part to the last part.', 'organize-series'); ?>
            </p>
            <div id="poststuff">
                <div id="post-body" class="metabox-holder columns-2">

                    <div id="post-body-content" style="position: relative;">
                        <form action="<?php echo esc_url(add_query_arg('', '')); ?>" method="post">
                            <?php

                    if (!empty($_REQUEST['orderby'])) {
                        echo '<input type="hidden" name="orderby" value="' . esc_attr(sanitize_text_field($_REQUEST['orderby'])) . '" />';
                    }
                    if (!empty($_REQUEST['order'])) {
                        echo '<input type="hidden" name="order" value="' . esc_attr(sanitize_text_field($_REQUEST['order'])) . '" />';
                    }
                    if (!empty($_REQUEST['page'])) {
                        echo '<input type="hidden" name="page" value="' . esc_attr(sanitize_text_field($_REQUEST['page'])) . '" />';
                    }
        ?>
                            <?php $this->series_part_table->display(); //Display the table
        ?>
                        </form>
                        <div class="form-wrap edit-term-notes">
                            <p><?php esc_html__('Description here.', 'simple-tags') ?></p>
                        </div>
                    </div>

                    <div id="postbox-container-1" class="postbox-container">
                        <div id="side-sortables" class="meta-box-sortables ui-sortable" style="">
                            <div id="submitdiv" class="postbox">
                                <div class="postbox-header">
                                    <h2 class="hndle ui-sortable-handle"><?php esc_html_e('Series Order', 'organize-series'); ?>
                                    </h2>
                                </div>
                                <form method="post" action="<?php echo esc_url(admin_url('edit.php?page=manage-issues')); ?>">
                                    <?php ppseries_publisher_nonce_field(); ?>
                                    <div class="hidden-fields">
                                        <input type="hidden" name="page" id="im_publish_page" value="manage-issues" />
                                        <input type="hidden" name="action" id="im_publish_action" value="order" />
                                        <input type="hidden" name="subaction" id="im_publish_subaction" value="order" />
                                        <input type="hidden" name="series_ID" id="im_publish_series_ID" value="<?php echo esc_attr($series_ID); ?>" />
                                        <input type="hidden" name="posts" id="im_publish_part_posts" value="" />
                                    </div>
                                    <div class="inside">
                                        <div id="minor-publishing"></div>
                                        <div id="major-publishing-actions">
                                            <div id="publishing-action"><input type="submit" value="<?php esc_attr_e('Update Order', 'organize-series'); ?>" class="button-primary" id="" name="publish" onclick="var im_post_IDs = new Array(); jQuery('.pp-series-publisher-wrap.series-order table tbody tr').each( function(){im_post_IDs.push(jQuery(this).attr('id').substring(5));});jQuery('#im_publish_part_posts').val(im_post_IDs.join(','));" />
                                            </div>
                                            <div class="clear"></div>
                                        </div>
                                    </div>
                                </form>
                            </div>

                        </div>
                    </div>

                </div>

                <br class="clear" />
            </div>

        </div><?php
    }

        public function ppseries_publisher_pending_output($series_ID)
        {
            $series = get_term($series_ID);
            $this->series_pending_table->prepare_items();
            ?>

    <div class="wrap pp-series-publisher-wrap series-order-pending">

        <h1><?php esc_html_e('Unpublished posts in series:', 'organize-series'); ?>
            <?php echo esc_html($series->name); ?>
        </h1>
        <p class="description"><?php _e('When these posts are published, they will be added after the last current post in the series.', 'organize-series'); ?>
        </p>
        <div id="poststuff">
            <div id="post-body" class="metabox-holder columns-2">

                <div id="post-body-content" style="position: relative;">
                    <form action="<?php echo esc_url(add_query_arg('', '')); ?>" method="post">
                        <?php

                                    if (!empty($_REQUEST['orderby'])) {
                                        echo '<input type="hidden" name="orderby" value="' . esc_attr(sanitize_text_field($_REQUEST['orderby'])) . '" />';
                                    }
                                    if (!empty($_REQUEST['order'])) {
                                        echo '<input type="hidden" name="order" value="' . esc_attr(sanitize_text_field($_REQUEST['order'])) . '" />';
                                    }
                                    if (!empty($_REQUEST['page'])) {
                                        echo '<input type="hidden" name="page" value="' . esc_attr(sanitize_text_field($_REQUEST['page'])) . '" />';
                                    }
            ?>
                        <?php $this->series_pending_table->display(); //Display the table
            ?>
                    </form>
                    <div class="form-wrap edit-term-notes">
                        <p><?php esc_html__('Description here.', 'simple-tags') ?></p>
                    </div>
                </div>

                <div id="postbox-container-1" class="postbox-container">
                    <div id="side-sortables" class="meta-box-sortables ui-sortable" style="">
                        <div id="submitdiv" class="postbox">
                            <div class="postbox-header">
                                <h2 class="hndle ui-sortable-handle"><?php esc_html_e('Unpublished Posts', 'organize-series'); ?>
                                </h2>
                            </div>
                            <form method="post" action="<?php echo esc_url(admin_url('edit.php?page=manage-issues')); ?>">
                                <?php ppseries_publisher_nonce_field(); ?>
                                <div class="hidden-fields">
                                    <input type="hidden" name="page" id="im_publish_page" value="manage-issues" />
                                    <input type="hidden" name="action" id="im_publish_action" value="order" />
                                    <input type="hidden" name="subaction" id="im_publish_subaction" value="published" />
                                    <input type="hidden" name="series_ID" id="im_publish_series_ID" value="<?php echo esc_attr($series_ID); ?>" />
                                    <input type="hidden" name="posts" class="im_publish_pending_posts" value="" />
                                </div>
                                <div class="inside">
                                    <div id="minor-publishing"></div>
                                    <div id="major-publishing-actions">
                                        <div id="publishing-action"><input type="submit" value="<?php esc_attr_e('Publish Unpublished Posts', 'organize-series'); ?>" class="button-primary" id="" name="publish" onclick="var im_post_IDs = new Array(); jQuery('.pp-series-publisher-wrap.series-order-pending table tbody tr').each( function(){im_post_IDs.push(jQuery(this).attr('id').substring(5));});jQuery('.im_publish_pending_posts').val(im_post_IDs.join(','));" />
                                        </div>
                                        <div class="clear"></div>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <div id="submitdiv" class="postbox">
                            <div class="postbox-header">
                                <h2 class="hndle ui-sortable-handle"><?php esc_html_e('Unpublished Series Order', 'organize-series'); ?>
                                </h2>
                            </div>
                            <form method="post" action="<?php echo esc_url(admin_url('edit.php?page=manage-issues')); ?>">
                                <?php ppseries_publisher_nonce_field(); ?>
                                <div class="hidden-fields">
                                    <input type="hidden" name="page" id="im_publish_page" value="manage-issues" />
                                    <input type="hidden" name="action" id="im_publish_action" value="order" />
                                    <input type="hidden" name="subaction" id="im_publish_subaction" value="pending_order" />
                                    <input type="hidden" name="series_ID" id="im_publish_series_ID" value="<?php echo esc_attr($series_ID); ?>" />
                                    <input type="hidden" name="posts" class="im_publish_pending_posts" value="" />
                                </div>
                                <div class="inside">
                                    <div id="minor-publishing"></div>
                                    <div id="major-publishing-actions">
                                        <div id="publishing-action"><input type="submit" value="<?php esc_attr_e('Update Order', 'organize-series'); ?>" class="button-primary" id="" name="publish" onclick="var im_post_IDs = new Array(); jQuery('.pp-series-publisher-wrap.series-order-pending table tbody tr').each( function(){im_post_IDs.push(jQuery(this).attr('id').substring(5));});jQuery('.im_publish_pending_posts').val(im_post_IDs.join(','));" />
                                        </div>
                                        <div class="clear"></div>
                                    </div>
                                </div>
                            </form>
                        </div>

                    </div>
                </div>

            </div>

            <br class="clear" />
        </div>

    </div><?php
        }

}

function init_pps_publisher()
{
    if (is_admin()) {
        new PPS_Publisher_Admin();
    }
}
add_action('publishpress_series_after_init', 'init_pps_publisher');
add_action('publishpress_series_pro_before_init', 'init_pps_publisher');
