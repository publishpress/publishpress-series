<?php
/**
 * @package WordPress-EDD-License-Integration
 * @author PublishPress
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
use PublishPress\WordPressEDDLicense\Exception\InvalidRequest;
use WP_Error;

if (!defined('ABSPATH')) {
    die('No direct script access allowed.');
}

/**
 * Class for license
 */
class License
{
    /**
     * Constant for missing license status
     */
    const STATUS_EMPTY_LICENSE = '';

    /**
     * Constant for valid status
     */
    const STATUS_VALID = 'valid';

    /**
     * Constant for expired status
     */
    const STATUS_EXPIRED = 'expired';

    /**
     * Constant for revoked status
     */
    const STATUS_REVOKED = 'revoked';

    /**
     * Constant for missing status
     */
    const STATUS_MISSING = 'missing';

    /**
     * Constant for invalid status
     */
    const STATUS_INVALID = 'invalid';

    /**
     * Constant for failed status
     */
    const STATUS_FAILED = 'failed';

    /**
     * Constant for inactive status
     */
    const STATUS_SITE_INACTIVE = 'site_inactive';

    /**
     * Constant for mismatch status
     */
    const STATUS_ITEM_NAME_MISMATCH = 'item_name_mismatch';

    /**
     * Constant for no activations left status
     */
    const STATUS_NO_ACTIVATIONS_LEFT = 'no_activations_left';

    /**
     * @var Container
     */
    protected $container;

    /**
     * @var string
     */
    protected $eddApiUrl;

    /**
     * @var array
     */
    protected $messages;

    /**
     * The constructor
     *
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        add_action('admin_head', [$this, 'adminHeadAddStyle']);

        $this->container = $container;

        $this->eddApiUrl = $this->container['API_URL'];

        $this->messages = [
            'error-exception' => __(
                'Sorry, an error occurred. Please check the error log and contact the PublishPress support team.',
                'wp-edd-license-integration'
            ),
        ];
    }

    /**
     * @param string $url
     * @param array $body
     *
     * @return array|WP_Error
     */
    protected function makeRequest($url, $body)
    {
        return wp_remote_post(
            $url,
            [
                'timeout'   => 30,
                'sslverify' => true,
                'body'      => $body
            ]
        );
    }

    /**
     * @return string|void
     */
    protected function getHomeUrl()
    {
        return home_url();
    }

    /**
     * @param mixed $response
     * @return bool
     */
    protected function isWpError($response)
    {
        return is_wp_error($response);
    }

    /**
     * @param mixed $response
     * @return int|string
     */
    protected function getResponseCode($response)
    {
        return wp_remote_retrieve_response_code($response);
    }

    /**
     * @param string $message
     */
    protected function logError($message)
    {
        error_log($message);
    }

    protected function getResponseDecodedJsonBody($response)
    {
        return json_decode(wp_remote_retrieve_body($response));
    }

    /**
     * Method that validates a license key.
     *
     * @param string $license_key
     * @param string $item_id
     *
     * @return  mixed
     */
    public function validate_license_key($license_key, $item_id)
    {
        $result = false;

        try {
            $response = $this->makeRequest(
                $this->eddApiUrl,
                [
                    'edd_action' => "activate_license",
                    'license'    => $license_key,
                    'item_id'    => $item_id,
                    'url'        => $this->getHomeUrl(),
                ]
            );

            if ($this->isWpError($response)) {
                throw new InvalidRequest($response->get_error_message());
            }

            $responseCode = $this->getResponseCode($response);
            if (200 !== (int)$responseCode) {
                throw new InvalidRequest(
                    sprintf(
                        'Request returned response code %d',
                        $responseCode
                    )
                );
            }

            $license_data = $this->getResponseDecodedJsonBody($response);

            if (empty($license_data) || !is_object($license_data)) {
                $license_new_status = static::STATUS_INVALID;
            } else {
                if (isset($license_data->success) && true === $license_data->success) {
                    $license_new_status = static::STATUS_VALID;
                } else {
                    if (isset($license_data->license) && static::STATUS_INVALID === $license_data->license) {
                        $license_new_status = static::STATUS_INVALID;
                    } else {
                        $license_new_status = isset($license_data->error) && !empty($license_data->error) ? $license_data->error : static::STATUS_INVALID;
                    }
                }
            }

            if ($license_new_status !== static::STATUS_VALID) {
                $error_message = 'No message';

                if (isset($license_data->error)) {
                    $error_message = $license_data->error;
                }

                $this->logError(
                    sprintf(
                        '[PublishPress WordPressEDDLicense] License is set as %s. Error message: %s',
                        $license_new_status,
                        $error_message
                    )
                );
            }

            $result = $license_new_status;
        } catch (\Exception $e) {
            $this->logError(
                sprintf(
                    '[PublishPress WordPressEDDLicense] (%d) %s at %s:%d [API_URL="%s", $item_id="%s", url="%s"]',
                    $e->getCode(),
                    $e->getMessage(),
                    $e->getFile(),
                    $e->getLine(),
                    $this->container['API_URL'],
                    $item_id,
                    $this->getHomeUrl()
                )
            );

            $result = $this->messages['error-exception'];
        }

        return $result;
    }

    public function deactivate_license_key($license_key, $item_id)
    {
        try {
            $response = $this->makeRequest(
                $this->eddApiUrl,
                [
                    'edd_action' => "deactivate_license",
                    'license'    => $license_key,
                    'item_id'    => $item_id,
                    'url'        => $this->getHomeUrl(),
                ]
            );

            if ($this->isWpError($response)) {
                throw new InvalidRequest($response->get_error_message());
            }

            $responseCode = $this->getResponseCode($response);
            if (200 !== (int)$responseCode) {
                throw new InvalidRequest(
                    sprintf(
                        'Request returned response code %d',
                        $responseCode
                    )
                );
            }

            $license_data = $this->getResponseDecodedJsonBody($response);

            if (empty($license_data) || !is_object($license_data)) {
                $license_new_status = static::STATUS_INVALID;

                $this->logError('[PublishPress WordPressEDDLicense] Invalid response from licence server');
            } else {
                if (isset($license_data->success) && true === $license_data->success) {
                    $license_new_status = static::STATUS_VALID;
                } else {
                    if (isset($license_data->license) && static::STATUS_FAILED === $license_data->license) {
                        $license_new_status = static::STATUS_INVALID;
                    } else {
                        $license_new_status = isset($license_data->error) && !empty($license_data->error) ? $license_data->error : static::STATUS_INVALID;
                    }
                }
            }

            $result = $license_new_status;
        } catch (\Exception $e) {
            $this->logError(
                sprintf(
                    '[PublishPress WordPressEDDLicense] (%d) %s at %s:%d [API_URL="%s", $item_id="%s", url="%s"]',
                    $e->getCode(),
                    $e->getMessage(),
                    $e->getFile(),
                    $e->getLine(),
                    $this->container['API_URL'],
                    $item_id,
                    $this->getHomeUrl()
                )
            );

            $result = $this->messages['error-exception'];
        }

        return $result;
    }

    /**
     * Sanitize the license key, returning the clean key.
     *
     * @param string $license_key
     * @return string
     */
    public function sanitize_license_key($license_key)
    {
        return preg_replace('/[^a-z0-9\-_]/i', '', $license_key);
    }

    public function adminHeadAddStyle()
    {
        ?>
        <style>
            .pp-wp-edd-license-container .dashicons-warning,
            .pp-wp-edd-license-container .pp-wp-edd-license-label-danger {
                color: red;
            }

            .pp-wp-edd-license-container .dashicons-yes,
            .pp-wp-edd-license-container .pp-wp-edd-license-label-success {
                color: green;
            }
        </style>
        <?php
    }
}
