<?php
namespace PublishPress\Series;

class PPSeriesCoreAdmin {
    function __construct() {
        if (current_user_can('manage_publishpress_series')) {
            if (is_admin()) {
                add_filter(
                    \PPVersionNotices\Module\TopNotice\Module::SETTINGS_FILTER,
                    function ($settings) {
                            $settings['publishpress-series'] = [
                                'message'  => esc_html__("You're using PublishPress Series Free. The Pro version has more features and support. %sUpgrade to Pro%s", 'organize-series'),
                                'link'     => 'https://publishpress.com/links/series-banner',
                                'screens'  => [
                                    ['base' => 'toplevel_page_orgseries_options_page', 'id'   => 'toplevel_page_orgseries_options_page'],
                                    ['base' => 'edit-tags', 'id' => 'edit-series', 'taxonomy' => ppseries_get_series_slug() ],
                                    ['base' => 'term',      'id' => 'edit-series', 'taxonomy' => ppseries_get_series_slug() ],
                                    ['base' => 'edit-tags', 'id' => 'edit-series_group', 'taxonomy' => 'series_group' ],
                                    ['base' => 'term',      'id' => 'edit-series_group', 'taxonomy' => 'series_group' ],
                                    ['base' => 'posts_page_manage-issues',      'id' => 'posts_page_manage-issues' ]
                                ]
                            ];

                            return $settings;
                        }
                );
                add_filter(
                    \PPVersionNotices\Module\MenuLink\Module::SETTINGS_FILTER,
                    function ($settings) {
                        $settings['publishpress-series'] = [
                            'parent' => 'orgseries_options_page',
                            'label'  => __('Upgrade to Pro', 'organize-series'),
                            'link'   => 'https://publishpress.com/links/series-banner',
                        ];

                        return $settings;
                });
            }
        }

        add_action('publishpress_series_admin_after_sidebar', [$this, 'publishpress_series_advertising_sidebar_banner']);
    }
    
    public function publishpress_series_advertising_sidebar_banner(){ 
        ?>
        
        <div class="ppseries-advertisement-right-sidebar">
            <div id="postbox-container-1" class="postbox-container">
            <div class="meta-box-sortables">
               
                <div class="advertisement-box-content postbox">
                    <div class="postbox-header">
                        <h3 class="advertisement-box-header hndle is-non-sortable">
                            <span><?php echo esc_html__('Need PublishPress Series Support?', 'organize-series'); ?></span>
                        </h3>
                    </div>
        
                    <div class="inside">
                        <p><?php echo esc_html__('If you need help or have a new feature request, let us know.', 'organize-series'); ?>
                            <a class="advert-link" href="https://wordpress.org/support/plugin/organize-series/" target="_blank">
                            <?php echo esc_html__('Request Support', 'organize-series'); ?> 
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" class="linkIcon">
                                    <path
                                        d="M18.2 17c0 .7-.6 1.2-1.2 1.2H7c-.7 0-1.2-.6-1.2-1.2V7c0-.7.6-1.2 1.2-1.2h3.2V4.2H7C5.5 4.2 4.2 5.5 4.2 7v10c0 1.5 1.2 2.8 2.8 2.8h10c1.5 0 2.8-1.2 2.8-2.8v-3.6h-1.5V17zM14.9 3v1.5h3.7l-6.4 6.4 1.1 1.1 6.4-6.4v3.7h1.5V3h-6.3z"
                                    ></path>
                                </svg>
                            </a>
                        </p>
                        <p>
                        <?php echo esc_html__('Detailed documentation is also available on the plugin website.', 'organize-series'); ?> 
                            <a class="advert-link" href="https://publishpress.com/knowledge-base/start-series/" target="_blank">
                            <?php echo esc_html__('View Knowledge Base', 'organize-series'); ?> 
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" class="linkIcon">
                                    <path
                                        d="M18.2 17c0 .7-.6 1.2-1.2 1.2H7c-.7 0-1.2-.6-1.2-1.2V7c0-.7.6-1.2 1.2-1.2h3.2V4.2H7C5.5 4.2 4.2 5.5 4.2 7v10c0 1.5 1.2 2.8 2.8 2.8h10c1.5 0 2.8-1.2 2.8-2.8v-3.6h-1.5V17zM14.9 3v1.5h3.7l-6.4 6.4 1.1 1.1 6.4-6.4v3.7h1.5V3h-6.3z"
                                    ></path>
                                </svg>
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>    
    </div>

        <?php
    }

}
