<?php

namespace OrganizeSeries\domain;

use DomainException;
use OrganizeSeries\domain\interfaces\AbstractMeta;

class Meta extends AbstractMeta {


    /**
     * Path to the admin view templates.
     * @return string
     * @throws DomainException
     */
    public function adminTemplatePath()
    {
        return $this->getBasePath() . 'src/views/admin/templates/';
    }


    /**
     * Url to the admin view templates.
     * @return string
     * @throws DomainException
     */
    public function adminTemplateUrl()
    {
        return $this->getBaseUrl() . 'src/views/admin/templates/';
    }


    /**
     * Path to the assets folder
     * @return string
     * @throws DomainException
     */
    public function assetsPath()
    {
        return $this->getBasePath() . 'assets/';
    }


    /**
     * Url to the assets folder
     * @return string
     * @throws DomainException
     */
    public function assetsUrl()
    {
        return $this->getBaseUrl() . 'assets/';
    }


    /**
     * The url to the api for licensing.
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
     * @throws DomainException
     */
    public function assetsDistPath()
    {
        return $this->assetsPath() . 'dist/';
    }


    /**
     * @return string
     * @throws DomainException
     */
    public function assetsDistUrl()
    {
        return $this->assetsUrl() . 'dist/';
    }
}