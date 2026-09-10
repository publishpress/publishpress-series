<?php
if ( !class_exists('PluginUpdateEngineChecker') ):
	
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
	class PluginUpdateEngineChecker {
	    
	    static private $notice_registered = false;
	    
		public function __construct()
		{
		    global $pagenow;
			if (is_admin()
			    && ( ( isset($_GET['page']) && $_GET['page'] === 'orgseries_options_page' )
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
                    <?php
                    printf(
                        /* translators: %s is a link to the licensing change documentation. */
                        esc_html__('PublishPress Series Add-on licensing has changed. This notice only appears on sites affected by this change. You can %s.', 'organize-series'),
                        '<a href="' . esc_url('http://docs.organizeseries.com/article/77-licensing-changes-in-organize-series') . '">' . esc_html__('read more about this licensing change', 'organize-series') . '</a>'
                    );
                    ?>
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
