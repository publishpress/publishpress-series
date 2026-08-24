<?php

if (!class_exists('PluginUpdateEngineChecker')) :

    /**
     * A custom plugin update checker.
     * @deprecated 2.5.7   This is being replaced by EDD updater.
     *
     * @author Janis Elsts
     * @modified by Darren Ethier
     * @copyright 2010
     * @version 1.0
     * @access public
     */
    class PluginUpdateEngineChecker
    {
        private static $notice_registered = false;

        public function __construct()
        {
            global $pagenow;
                $page = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin screen detection for a deprecated notice.
            if (
                is_admin()
                    && ( ( 'orgseries_options_page' === $page )
                     || $pagenow === 'plugins.php'
                    )
                && ! self::$notice_registered
            ) {
                self::$notice_registered = true;
                add_action('admin_notices', array($this, 'deprecatedNotice'));
            }
        }


        public function deprecatedNotice()
        {
            ?>
            <div class="notice error">
                <p>
                    Publishpress Series Add-on licensing has changed.  This notice only appears by sites affected by this change.  You can read more about the change <a href="http://docs.organizeseries.com/article/77-licensing-changes-in-organize-series">here.</a>
                </p>
                <p>
                    The new fields for your license keys are found in the sidebar on this page, labelled "Extension Licenses".  This notice will disappear once all of your Publishpress Series add-ons have been updated.
                </p>
            </div>
            <?php
        }
    }

endif;

?>
