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
    protected $file;


    /**
     * The WordPress basename for the plugin.
     * @var string
     */
    protected $basename;


    /**
     * The base path for the plugin (within the src) folder.
     */
    protected $base_path;


    /**
     * The base url for the plugin (within the src) folder
     * @var string
     */
    protected $base_url;


    /**
     * The plugin version.
     * @var string
     */
    protected $version;


    public function __construct($plugin_file, $version)
    {
        $this->file = $plugin_file;
        $this->version = $version;
        $this->basename = plugin_basename($plugin_file);
        $this->base_path = plugin_dir_path($plugin_file);
        $this->base_url = plugin_dir_url($plugin_file);
    }


    /**
     * @return string
     * @throws DomainException
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @return string
     * @throws DomainException
     */
    public function getBasename()
    {
        return $this->basename;
    }

    /**
     * @return mixed
     * @throws DomainException
     */
    public function getBasePath()
    {
        return $this->base_path;
    }

    /**
     * @return string
     * @throws DomainException
     */
    public function getBaseUrl()
    {
        return $this->base_url;
    }

    /**
     * @return string
     * @throws DomainException
     */
    public function getVersion()
    {
        return $this->version;
    }
}