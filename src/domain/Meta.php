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
    public static function adminTemplatePath()
    {
        return self::getBasePath() . 'domain/views/admin/templates/';
    }


    /**
     * Url to the admin view templates.
     * @return string
     * @throws DomainException
     */
    public static function adminTemplateUrl()
    {
        return self::getBaseUrl() . 'domain/views/admin/templates/';
    }


    /**
     * Path to the assets folder
     * @return string
     * @throws DomainException
     */
    public static function assetsPath()
    {
        return self::getBasePath() . 'src/assets/';
    }


    /**
     * Url to the assets folder
     * @return string
     * @throws DomainException
     */
    public static function assetsUrl()
    {
        return self::getBaseUrl() . 'src/assets/';
    }


    /**
     * The url to the api for licensing.
     * @return string
     */
    public static function licensingApiUri()
    {
        return defined('OS_LICENSING_URI')
            ? OS_LICENSING_URI
            : 'https://organizeseries.com';
    }
}