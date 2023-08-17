<?php
/**
 * @package WordPress-EDD-License-Integration
 * @author  PublishPress
 *
 * Copyright (c) 2018 PublishPress
 *
 * This file is part of WordPress-EDD-License-Integration
 *
 * WordPress-EDD-License-Integration is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * WordPress-EDD-License-Integration is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with WordPress-EDD-License-Integration.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace PublishPress\WordPressEDDLicense;

// Exit if accessed directly
if (!defined('ABSPATH')) {
    die('No direct script access allowed.');
}

/**
 * The config data for the services.
 *
 * @since      1.0.0
 * @package    WordPress-EDD-License-Integration
 * @author     PublishPress
 */
class ServicesConfig
{
    /**
     * The URL for the API.
     *
     * @var string
     */
    protected $apiUrl;

    /**
     * The license key for the EDD item.
     *
     * @var string
     */
    protected $licenseKey = '';

    /**
     * The status of the license.
     *
     * @var string
     */
    protected $licenseStatus = '';

    /**
     * The version of the plugin integrating with this library.
     *
     * @var string
     */
    protected $pluginVersion;

    /**
     * The item id on EDD for the current plugin integrating with this library.
     *
     * @var string
     */
    protected $eddItemId;

    /**
     * The plugin's author in EDD.
     *
     * @var string
     */
    protected $pluginAuthor;

    /**
     * The plugin's file in WordPress.
     *
     * @var string
     */
    protected $pluginFile;

    /**
     * Returns true if the current attributes are valid.
     *
     * @return bool
     * @throws Exception\InvalidParams
     * @since 1.2.0
     */
    public function validate()
    {
        if (!isset($this->apiUrl) || empty($this->apiUrl)) {
            throw new Exception\InvalidParams('Services require a non empty apiUrl attribute.');
        }

        if (!isset($this->pluginVersion) || empty($this->pluginVersion)) {
            throw new Exception\InvalidParams('Services require a non empty pluginVersion attribute.');
        }

        if (!isset($this->eddItemId) || empty($this->eddItemId)) {
            throw new Exception\InvalidParams('Services require a non empty eddItemId attribute.');
        }

        if (!isset($this->pluginAuthor) || empty($this->pluginAuthor)) {
            throw new Exception\InvalidParams('Services require a non empty pluginAuthor attribute.');
        }

        if (!isset($this->pluginFile) || empty($this->pluginFile)) {
            throw new Exception\InvalidParams('Services require a non empty pluginFile attribute.');
        }

        return true;
    }

    /**
     * @return string
     */
    public function getApiUrl()
    {
        return $this->apiUrl;
    }

    /**
     * @param string $apiUrl
     * @return ServicesConfig
     */
    public function setApiUrl($apiUrl)
    {
        $this->apiUrl = trailingslashit($apiUrl);

        return $this;
    }

    /**
     * @return string
     */
    public function getLicenseKey()
    {
        return $this->licenseKey;
    }

    /**
     * @param string $licenseKey
     * @return ServicesConfig
     */
    public function setLicenseKey($licenseKey)
    {
        $this->licenseKey = $licenseKey;

        return $this;
    }

    /**
     * @return string
     */
    public function getLicenseStatus()
    {
        return $this->licenseStatus;
    }

    /**
     * @param string $licenseStatus
     * @return ServicesConfig
     */
    public function setLicenseStatus($licenseStatus)
    {
        $this->licenseStatus = $licenseStatus;

        return $this;
    }

    /**
     * @return string
     */
    public function getPluginVersion()
    {
        return $this->pluginVersion;
    }

    /**
     * @param string $pluginVersion
     * @return ServicesConfig
     */
    public function setPluginVersion($pluginVersion)
    {
        $this->pluginVersion = $pluginVersion;

        return $this;
    }

    /**
     * @return string
     */
    public function getEddItemId()
    {
        return $this->eddItemId;
    }

    /**
     * @param string $eddItemId
     * @return ServicesConfig
     */
    public function setEddItemId($eddItemId)
    {
        $this->eddItemId = $eddItemId;

        return $this;
    }

    /**
     * @return string
     */
    public function getPluginAuthor()
    {
        return $this->pluginAuthor;
    }

    /**
     * @param string $pluginAuthor
     * @return ServicesConfig
     */
    public function setPluginAuthor($pluginAuthor)
    {
        $this->pluginAuthor = $pluginAuthor;

        return $this;
    }

    /**
     * @return string
     */
    public function getPluginFile()
    {
        return $this->pluginFile;
    }

    /**
     * @param string $pluginFile
     * @return ServicesConfig
     */
    public function setPluginFile($pluginFile)
    {
        $this->pluginFile = $pluginFile;

        return $this;
    }
}
