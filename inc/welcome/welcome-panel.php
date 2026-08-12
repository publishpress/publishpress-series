<?php
/**
 * Welcome experience for PublishPress Series.
 *
 * After activation the user goes to the Series settings page. That page shows
 * a thank-you notice and, while the site has no series, a get-started panel
 * with a preview of what a series looks like on the site.
 *
 * @package Publishpress Series
 */

if (!defined('ABSPATH')) {
    exit;
}

define('PPSERIES_WELCOME_REDIRECT_OPTION', 'ppseries_do_welcome_redirect');
define('PPSERIES_WELCOME_GREETING_OPTION', 'ppseries_show_welcome_greeting');
define('PPSERIES_WELCOME_DISMISSED_META', 'ppseries_welcome_greeting_dismissed');

add_action('admin_init', 'ppseries_welcome_redirect');
add_action('admin_enqueue_scripts', 'ppseries_welcome_assets');
add_action('publishpress_series_settings_after_title', 'ppseries_welcome_greeting');
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
 * Load the styles and the script of the welcome experience.
 */
function ppseries_welcome_assets()
{
    if (!ppseries_is_series_settings_screen() || !current_user_can('manage_publishpress_series')) {
        return;
    }

    if (!ppseries_welcome_greeting_is_visible() && ppseries_welcome_series_count() > 0) {
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
 * Tell if the thank-you notice must be shown to the current user.
 *
 * @return bool
 */
function ppseries_welcome_greeting_is_visible()
{
    if (!current_user_can('manage_publishpress_series')) {
        return false;
    }

    if (!get_option(PPSERIES_WELCOME_GREETING_OPTION)) {
        return false;
    }

    // The greeting is only for the start. It stops when the site has series.
    if (ppseries_welcome_series_count() > 0) {
        return false;
    }

    return !get_user_meta(get_current_user_id(), PPSERIES_WELCOME_DISMISSED_META, true);
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
 * Show the thank-you notice after activation.
 */
function ppseries_welcome_greeting()
{
    if (!ppseries_welcome_greeting_is_visible()) {
        return;
    }

    ?>
    <div class="ppseries-welcome-greeting">
        <button type="button" class="ppseries-welcome-dismiss" aria-label="<?php esc_attr_e('Dismiss this notice', 'organize-series'); ?>">
            <span class="dashicons dashicons-no-alt" aria-hidden="true"></span>
        </button>
        <p class="ppseries-welcome-greeting-title">
            <span aria-hidden="true">&#128075;</span>
            <?php esc_html_e('Thanks for installing PublishPress Series!', 'organize-series'); ?>
        </p>
        <p class="ppseries-welcome-greeting-text">
            <?php esc_html_e('You are all set. Create your first series to get started.', 'organize-series'); ?>
        </p>
    </div>
    <?php
}

/**
 * Show the get-started panel while the site has no series.
 */
function ppseries_welcome_panel()
{
    if (!current_user_can('manage_publishpress_series') || ppseries_welcome_series_count() > 0) {
        return;
    }

    $series_url = admin_url('edit-tags.php?taxonomy=' . ppseries_get_series_slug());

    ?>
    <div class="ppseries-welcome-panel">
        <div class="ppseries-welcome-intro">
            <p class="ppseries-welcome-eyebrow"><?php esc_html_e('Get started', 'organize-series'); ?></p>
            <h2 class="ppseries-welcome-title">
                <?php esc_html_e('Your series live here.', 'organize-series'); ?>
            </h2>
            <p class="ppseries-welcome-text">
                <?php esc_html_e('Group related posts into a series. Your readers get a post list, the part number, and links to the next and previous post. No code required.', 'organize-series'); ?>
            </p>

            <a class="ppseries-welcome-button" href="<?php echo esc_url($series_url); ?>">
                <?php esc_html_e('Create your first series', 'organize-series'); ?>
            </a>

            <ul class="ppseries-welcome-pills">
                <li><span class="dashicons dashicons-list-view" aria-hidden="true"></span><?php esc_html_e('Automatic post list', 'organize-series'); ?></li>
                <li><span class="dashicons dashicons-info-outline" aria-hidden="true"></span><?php esc_html_e('Part 2 of 5 details', 'organize-series'); ?></li>
                <li><span class="dashicons dashicons-leftright" aria-hidden="true"></span><?php esc_html_e('Next and previous links', 'organize-series'); ?></li>
            </ul>

            <p class="ppseries-welcome-links">
                <a href="https://publishpress.com/knowledge-base/start-series/" target="_blank" rel="noopener noreferrer">
                    <?php esc_html_e('View documentation', 'organize-series'); ?>
                </a>
                <?php if (!pp_series_is_pro_active()) : ?>
                    <a href="https://publishpress.com/links/series-banner" target="_blank" rel="noopener noreferrer">
                        <?php esc_html_e('Upgrade to Pro', 'organize-series'); ?>
                    </a>
                <?php endif; ?>
            </p>
        </div>

        <div class="ppseries-welcome-preview">
            <div class="ppseries-preview-head">
                <span class="ppseries-preview-label"><?php esc_html_e('Preview', 'organize-series'); ?></span>
                <div class="ppseries-preview-tabs" role="tablist">
                    <button type="button" class="ppseries-preview-tab is-active" data-preview="list" role="tab" aria-selected="true">
                        <?php esc_html_e('Post List', 'organize-series'); ?>
                    </button>
                    <button type="button" class="ppseries-preview-tab" data-preview="details" role="tab" aria-selected="false">
                        <?php esc_html_e('Post Details', 'organize-series'); ?>
                    </button>
                    <button type="button" class="ppseries-preview-tab" data-preview="navigation" role="tab" aria-selected="false">
                        <?php esc_html_e('Navigation', 'organize-series'); ?>
                    </button>
                </div>
            </div>

            <div class="ppseries-preview-stage">
                <div class="ppseries-preview-pane is-active" data-preview-pane="list">
                    <div class="ppseries-preview-card">
                        <p class="ppseries-preview-card-title"><?php esc_html_e('The WordPress Guide', 'organize-series'); ?></p>
                        <ol class="ppseries-preview-list">
                            <li><?php esc_html_e('Install WordPress', 'organize-series'); ?></li>
                            <li class="is-current"><?php esc_html_e('Choose a theme', 'organize-series'); ?></li>
                            <li><?php esc_html_e('Add your first posts', 'organize-series'); ?></li>
                            <li><?php esc_html_e('Set up the menus', 'organize-series'); ?></li>
                            <li><?php esc_html_e('Go live', 'organize-series'); ?></li>
                        </ol>
                    </div>
                </div>

                <div class="ppseries-preview-pane" data-preview-pane="details">
                    <div class="ppseries-preview-card">
                        <p class="ppseries-preview-note">
                            <?php
                            printf(
                                /* translators: 1: part number, 2: total posts, 3: series name */
                                esc_html__('This entry is part %1$s of %2$s in the series %3$s', 'organize-series'),
                                '<strong>2</strong>',
                                '<strong>5</strong>',
                                '<em>' . esc_html__('The WordPress Guide', 'organize-series') . '</em>'
                            );
                            ?>
                        </p>
                        <div class="ppseries-preview-lines">
                            <span></span><span></span><span class="is-short"></span>
                        </div>
                    </div>
                </div>

                <div class="ppseries-preview-pane" data-preview-pane="navigation">
                    <div class="ppseries-preview-card">
                        <div class="ppseries-preview-nav">
                            <span class="ppseries-preview-nav-item">
                                <small><?php esc_html_e('Previous in series', 'organize-series'); ?></small>
                                <?php esc_html_e('Install WordPress', 'organize-series'); ?>
                            </span>
                            <span class="ppseries-preview-nav-item is-next">
                                <small><?php esc_html_e('Next in series', 'organize-series'); ?></small>
                                <?php esc_html_e('Add your first posts', 'organize-series'); ?>
                            </span>
                        </div>
                        <div class="ppseries-preview-lines">
                            <span></span><span class="is-short"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
}

/**
 * Save the dismissal of the thank-you notice for the current user.
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
