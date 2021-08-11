<?php
namespace PublishPress\Series;

class PPSeriesCoreAdmin {
    function __construct() {

        if ( current_user_can( 'manage_options' ) )
        {
          if (is_admin())
          {
            require_once SERIES_PATH . '/vendor/publishpress/wordpress-version-notices/includes.php';
            add_filter(
                        \PPVersionNotices\Module\TopNotice\Module::SETTINGS_FILTER,
                        function ($settings) {
                            $settings['publishpress-series'] = [
                                'message'  => 'You\'re using PublishPress Series Free. The Pro version has more features and support. %sUpgrade to Pro%s',
                                'link'     => 'https://publishpress.com/links/series-banner',
                                'screens'  => [
                                    ['base' => 'settings_page_orgseries_options_page', 'id'   => 'settings_page_orgseries_options_page'],
                                    ['base' => 'edit-tags', 'id' => 'edit-series', 'taxonomy' => 'series' ],
                                    ['base' => 'term',      'id' => 'edit-series', 'taxonomy' => 'series' ]
                                ]
                            ];

                            return $settings;
                        }
                    );


        }

        }


    }

}
