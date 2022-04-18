<?php
namespace PublishPress\Series;

class PPSeriesCoreAdmin {
    function __construct() {
        if (current_user_can('manage_publishpress_series')) {
            if (is_admin()) {
                require_once SERIES_PATH . '/vendor/publishpress/wordpress-version-notices/includes.php';
                add_filter(
                    \PPVersionNotices\Module\TopNotice\Module::SETTINGS_FILTER,
                    function ($settings) {
                            $settings['publishpress-series'] = [
                                'message'  => 'You\'re using PublishPress Series Free. The Pro version has more features and support. %sUpgrade to Pro%s',
                                'link'     => 'https://publishpress.com/links/series-banner',
                                'screens'  => [
                                    ['base' => 'toplevel_page_orgseries_options_page', 'id'   => 'toplevel_page_orgseries_options_page'],
                                    ['base' => 'edit-tags', 'id' => 'edit-series', 'taxonomy' => ppseries_get_series_slug() ],
                                    ['base' => 'term',      'id' => 'edit-series', 'taxonomy' => ppseries_get_series_slug() ],
                                    ['base' => 'posts_page_manage-issues',      'id' => 'posts_page_manage-issues' ]
                                ]
                            ];

                            return $settings;
                        }
                );
                add_filter(
                    \PPVersionNotices\Module\MenuLink\Module::SETTINGS_FILTER,
                    function ($settings) {
                        $settings['publishpress-taxopress'] = [
                            'parent' => 'orgseries_options_page',
                            'label'  => 'Upgrade to Pro',
                            'link'   => 'https://publishpress.com/links/series-banner',
                        ];

                        return $settings;
                });
            }
        }

        add_action('publishpress_series_admin_menu_page', [$this, 'publishpress_series_admin_menu_page']);
    }

    public function publishpress_series_admin_menu_page(){
        add_submenu_page(
            'orgseries_options_page',
            esc_html__('Series Group', 'organize-series'),
            esc_html__('Series Group', 'organize-series'),
            'manage_publishpress_series',
            'pp-series-pro-placeholders-series-group',
            [$this, 'placeholderPageSeriesGroup'],
            12
        );
    }

    public function placeholderPageSeriesGroup(){
        wp_register_style('pps-pro-placeholder-css', SERIES_PATH_URL . 'includes-core/pro-placeholder/assets/css/placeholder.css', [], ORG_SERIES_VERSION);
        wp_enqueue_style( 'pps-pro-placeholder-css' );
        include_once __DIR__ . '/pro-placeholder/views/series-group-placeholder.php';
    }

}