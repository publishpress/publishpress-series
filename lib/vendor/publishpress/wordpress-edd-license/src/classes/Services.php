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

use Alledia\EDD_SL_Plugin_Updater;
use PublishPress\Pimple\ServiceProviderInterface;

// Exit if accessed directly
if (!defined('ABSPATH')) {
    die('No direct script access allowed.');
}

/**
 * The services for the dependency injection container.
 *
 * @since      1.2.0
 * @package    WordPress-EDD-License-Integration
 * @author     PublishPress
 */
class Services implements ServiceProviderInterface
{
    /**
     * An instance of the ServicesConfig class.
     *
     * @since      1.2.0
     * @var ServicesConfig
     */
    protected $config;

    /**
     * The constructor.
     *
     * @param ServicesConfig $config
     * @throws Exception\InvalidParams
     * @since      1.2.0
     */
    public function __construct(ServicesConfig $config)
    {
        $config->validate();

        $this->config = $config;
    }

    /**
     * Registers services on the given container.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param \Pimple\Container $container An Container instance
     */
    public function register(\PublishPress\Pimple\Container $pimple)
    {
        /*
         * The config
         */
        $pimple['config'] = function (Container $c) {
            return $this->config;
        };

        /*
         *
         * Define the constants.
         */
        $pimple['LIBRARY_VERSION'] = function (Container $c) {
            return '2.3.2';
        };

        $pimple['API_URL'] = function (Container $c) {
            return $c['config']->getApiUrl();
        };

        $pimple['LICENSE_KEY'] = function (Container $c) {
            return $c['config']->getLicenseKey();
        };

        $pimple['LICENSE_STATUS'] = function (Container $c) {
            return $c['config']->getLicenseStatus();
        };

        $pimple['PLUGIN_VERSION'] = function (Container $c) {
            return $c['config']->getPluginVersion();
        };

        $pimple['EDD_ITEM_ID'] = function (Container $c) {
            return $c['config']->getEddItemId();
        };

        $pimple['PLUGIN_AUTHOR'] = function (Container $c) {
            return $c['config']->getPluginAuthor();
        };

        $pimple['PLUGIN_FILE'] = function (Container $c) {
            return $c['config']->getPluginFile();
        };

        /*
         * Define the update manager.
         */
        $pimple['update_manager'] = function (Container $c) {
            return new EDD_SL_Plugin_Updater(
                $c['API_URL'],
                $c['PLUGIN_FILE'],
                [
                    'version'        => $c['PLUGIN_VERSION'],
                    'license'        => $c['LICENSE_KEY'],
                    'license_status' => $c['LICENSE_STATUS'],
                    'item_id'        => $c['EDD_ITEM_ID'],
                    'author'         => $c['PLUGIN_AUTHOR'],
                ]
            );
        };

        /*
         * Define the license manager.
         */
        $pimple['license_manager'] = function (Container $c) {
            return new License($c);
        };

        /*
         * Define the language service.
         */
        $pimple['language'] = function (Container $c) {
            return new Language($c);
        };
    }
}
