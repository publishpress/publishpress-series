<?php

/**
 * orgSeriesVersionCheck
 * Temporary class for warning people about their site is on an older version of PHP than what will be required in a
 * future version of the plugin
 *
 * @package OrganizeSeries
 * @author  Darren Ethier
 * @since   2.5.7
 */
class orgSeriesVersionCheck
{
    public function __construct(){
        if ($this->isOlderPhpVersion()) {
            $this->showNotice();
        }
    }

    /**
     * If current installed php version is less than 5.6.0 return true.
     * @return bool
     */
    private function isOlderPhpVersion()
    {
        return version_compare(PHP_VERSION, '5.6') === -1;
    }


    /**
     * Sets the admin_notice for the php version warning.
     */
    private function showNotice()
    {
        add_action('admin_notices', array($this, 'warnAboutPhpVersion'));
    }


    public function warnAboutPhpVersion()
    {
        echo '
<div class="notice notice-error">
    <p>
        Organize Series has detected that your WordPress site is running an out-dated version of PHP. An <em>upcoming release</em> of Organize Series will <strong>require PHP 5.6 or greater</strong> in order to work.  
    </p>
    <p>
        <strong>You should upgrade your PHP version.</strong><br>
        Newer versions of PHP are both faster and more secure. In fact, your version of PHP no longer receives updates, which is one reason Organize Series is bumping its version requirements.  Another reason Organize Series will be bumping it\'s version requirements is because the plugin is being refactored to work more efficiently using the available features of newer PHP versions.    
    </p>
    <p>
        <strong>How to Upgrade</strong>
        Most hosts provide an easy way to upgrade your PHP version, however, before you upgrade, it\'s important you make sure all your plugins and your theme work with the PHP version you plan on switching to.  You can read more about this <a href="https://kb.yoast.com/kb/site-ready-php-7/">here.</a>  Once you\'ve determined your site is ready to upgrade, you can follow the instructions your web host provides for upgrading your php version.  You can also <a href="https://kb.yoast.com/kb/example-mail-to-request-your-host-updating-your-php-version/">this email template </a> to assist with your communication with your host.  If your web host is not able to assist you with upgrading your PHP version, it may be time to consider a <a href="https://yoast.com/wordpress-hosting/">a new host.</a>
    </p>
    <p>
        <strong>This notice will disappear once the version of PHP on your site is updated to PHP 5.6 or greater.</strong>
    </p>
</div> 
        ';
    }
}
new orgSeriesVersionCheck();