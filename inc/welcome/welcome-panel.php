<?php
/**
 * Welcome experience for PublishPress Series.
 *
 * After activation the user goes to the Series settings page. A welcome panel
 * on that page shows the first steps: create a series, read the documentation.
 *
 * @package Publishpress Series
 */

if (!defined('ABSPATH')) {
    exit;
}

define('PPSERIES_WELCOME_REDIRECT_OPTION', 'ppseries_do_welcome_redirect');
define('PPSERIES_WELCOME_DISMISSED_META', 'ppseries_welcome_panel_dismissed');

add_action('admin_init', 'ppseries_welcome_redirect');
add_action('admin_enqueue_scripts', 'ppseries_welcome_assets');
add_action('publishpress_series_settings_after_title', 'ppseries_welcome_panel');
add_action('wp_ajax_ppseries_dismiss_welcome_panel', 'ppseries_dismiss_welcome_panel');

/**
 * Tell if the current screen is the Series settings page.
 *
 * @return bool
 */
function ppseries_is_series_settings_screen()
{
    $page = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : '';

    return 'orgseries_options_page' === $page;
}

/**
 * Send the user to the Series settings page one time after activation.
 */
function ppseries_welcome_redirect()
{
    if (!get_option(PPSERIES_WELCOME_REDIRECT_OPTION)) {
        return;
    }

    delete_option(PPSERIES_WELCOME_REDIRECT_OPTION);

    // Do not interrupt a bulk or a network activation.
    if (isset($_GET['activate-multi']) || is_network_admin() || wp_doing_ajax()) {
        return;
    }

    if (!current_user_can('manage_publishpress_series')) {
        return;
    }

    wp_safe_redirect(ppseries_series_settings_page());
    exit;
}

/**
 * Load the styles and the script of the welcome panel.
 */
function ppseries_welcome_assets()
{
    if (!ppseries_is_series_settings_screen() || !ppseries_welcome_panel_is_visible()) {
        return;
    }

    wp_enqueue_style(
        'ppseries-welcome',
        SERIES_PATH_URL . 'assets/css/welcome.css',
        [],
        ORG_SERIES_VERSION
    );

    wp_enqueue_script(
        'ppseries-welcome',
        SERIES_PATH_URL . 'assets/js/welcome.js',
        ['jquery'],
        ORG_SERIES_VERSION,
        true
    );

    wp_localize_script('ppseries-welcome', 'ppseriesWelcome', [
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce'   => wp_create_nonce('ppseries_welcome_nonce'),
    ]);
}

/**
 * Tell if the welcome panel must be shown to the current user.
 *
 * @return bool
 */
function ppseries_welcome_panel_is_visible()
{
    if (!current_user_can('manage_publishpress_series')) {
        return false;
    }

    if (get_user_meta(get_current_user_id(), PPSERIES_WELCOME_DISMISSED_META, true)) {
        return false;
    }

    // The panel is an empty state. It stops when the site has series.
    return ppseries_welcome_series_count() < 1;
}

/**
 * Count the series terms.
 *
 * @return int
 */
function ppseries_welcome_series_count()
{
    $count = wp_count_terms([
        'taxonomy'   => ppseries_get_series_slug(),
        'hide_empty' => false,
    ]);

    return is_wp_error($count) ? 0 : (int) $count;
}

/**
 * Show the welcome panel on the Series settings page.
 */
function ppseries_welcome_panel()
{
    if (!ppseries_welcome_panel_is_visible()) {
        return;
    }

    $series_url = admin_url('edit-tags.php?taxonomy=' . ppseries_get_series_slug());

    ?>
    <div class="ppseries-welcome-panel">
        <button type="button" class="ppseries-welcome-dismiss" aria-label="<?php esc_attr_e('Dismiss this panel', 'organize-series'); ?>">
            <span class="dashicons dashicons-no-alt" aria-hidden="true"></span>
        </button>

        <div class="ppseries-welcome-content">
            <h2 class="ppseries-welcome-title">
                <?php esc_html_e('Welcome! Create your first series', 'organize-series'); ?>
            </h2>
            <p class="ppseries-welcome-text">
                <?php esc_html_e('A series groups your posts together and shows your readers the reading order. Start with the Series screen to create your first series, then add posts to it.', 'organize-series'); ?>
            </p>

            <div class="ppseries-welcome-actions">
                <a class="button button-primary" href="<?php echo esc_url($series_url); ?>">
                    <?php esc_html_e('Create Your First Series', 'organize-series'); ?>
                </a>

                <a class="button" href="https://publishpress.com/knowledge-base/start-series/" target="_blank" rel="noopener noreferrer">
                    <?php esc_html_e('View Documentation', 'organize-series'); ?>
                </a>

                <?php if (!pp_series_is_pro_active()) : ?>
                    <a class="ppseries-welcome-upgrade" href="https://publishpress.com/links/series-banner" target="_blank" rel="noopener noreferrer">
                        <?php esc_html_e('Upgrade to Pro', 'organize-series'); ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php
}

/**
 * Save the dismissal of the welcome panel for the current user.
 */
function ppseries_dismiss_welcome_panel()
{
    check_ajax_referer('ppseries_welcome_nonce', 'nonce');

    if (!current_user_can('manage_publishpress_series')) {
        wp_send_json_error();
    }

    update_user_meta(get_current_user_id(), PPSERIES_WELCOME_DISMISSED_META, 1);

    wp_send_json_success();
}
