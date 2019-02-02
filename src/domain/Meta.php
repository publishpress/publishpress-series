<?php

namespace OrganizeSeries\domain;

use DomainException;
use OrganizeSeries\domain\interfaces\AbstractMeta;

/**
 * Meta
 *
 *
 * @package OrganizeSeries\domain
 * @author  Darren Ethier
 * @since   2.5.9
 */
class Meta extends AbstractMeta
{

    /**
     * Path to the admin view templates.
     *
     * @return string
     */
    public function adminTemplatePath()
    {
        return $this->getBasePath() . 'src/views/admin/templates/';
    }


    /**
     * Url to the admin view templates.
     *
     * @return string
     */
    public function adminTemplateUrl()
    {
        return $this->getBaseUrl() . 'src/views/admin/templates/';
    }


    /**
     * Path to the assets folder
     *
     * @return string
     */
    public function assetsPath()
    {
        return $this->getBasePath() . 'assets/';
    }


    /**
     * Url to the assets folder
     *
     * @return string
     */
    public function assetsUrl()
    {
        return $this->getBaseUrl() . 'assets/';
    }


    /**
     * The url to the api for licensing.
     *
     * @return string
     */
    public function licensingApiUri()
    {
        return defined('OS_LICENSING_URI')
            ? OS_LICENSING_URI
            : 'https://organizeseries.com';
    }


    /**
     * @return string
     */
    public function assetsDistPath()
    {
        return $this->assetsPath() . 'dist/';
    }


    /**
     * @return string
     */
    public function assetsDistUrl()
    {
        return $this->assetsUrl() . 'dist/';
    }
}
