<?php

namespace OrganizeSeries\domain\interfaces;

use DomainException;
use Exception;

/**
 * AbstractMeta
 * The core plugin and add-ons can define a class extending this base for setting up basic meta information that should
 * be accessible everywhere.
 *
 * @package OrganizeSeries\domain\interfaces
 * @author  Darren Ethier
 * @since   1.0.0
 */
abstract class AbstractMeta
{
    /**
     * Equivalent to the `__FILE__` for the main plugin file.
     * @var string
     */
    private static $file;


    /**
     * The WordPress basename for the plugin.
     * @var string
     */
    private static $basename;


    /**
     * The base path for the plugin (within the src) folder.
     */
    private static $base_path;


    /**
     * The base url for the plugin (within the src) folder
     * @var string
     */
    private static $base_url;


    /**
     * The plugin version.
     * @var string
     */
    private static $version;



    public static function init($plugin_file, $version)
    {
        self::$file = $plugin_file;
        self::$version = $version;
        self::$basename = plugin_basename($plugin_file);
        self::$base_path = plugin_dir_path($plugin_file);
        self::$base_url = plugin_dir_url($plugin_file);
    }


    /**
     * @return string
     * @throws DomainException
     */
    public static function getFile()
    {
        self::verifyInitialized(__METHOD__);
        return self::$file;
    }

    /**
     * @return string
     * @throws DomainException
     */
    public static function getBasename()
    {
        self::verifyInitialized(__METHOD__);
        return self::$basename;
    }

    /**
     * @return mixed
     * @throws DomainException
     */
    public static function getBasePath()
    {
        self::verifyInitialized(__METHOD__);
        return self::$base_path;
    }

    /**
     * @return string
     * @throws DomainException
     */
    public static function getBaseUrl()
    {
        self::verifyInitialized(__METHOD__);
        return self::$base_url;
    }

    /**
     * @return string
     * @throws DomainException
     */
    public static function getVersion()
    {
        self::verifyInitialized(__METHOD__);
        return self::$version;
    }


    /**
     * @param string $method
     * @throws DomainException
     */
    private static function verifyInitialized($method)
    {
        if (self::$file === '') {
            throw new DomainException(
                sprintf(
                    esc_html__(
                        '%1$s needs to be called before %2$s can return a value.',
                        'organize-series'
                    ),
                    get_called_class() . '::init()',
                    "{$method}()"
                )
            );
        }
    }
}