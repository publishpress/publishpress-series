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

namespace PublishPress\WordPressEDDLicense\Setting\Field;

use PublishPress\WordPressEDDLicense\License;

// Exit if accessed directly
if (!defined('ABSPATH')) {
    die('No direct script access allowed.');
}


class License_key extends Base
{
    /**
     * @var string
     */
    protected $options_group_name;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $class;

    /**
     * @var string
     */
    protected $value;

    /**
     * @var string
     */
    protected $license_status = '';

    /**
     * @var string
     */
    protected $link_more_info = '';

    /**
     * The constructor
     *
     * @param array $args {
     * @type  string $options_group_name The options group name for the field
     * @type  string $name The name for the field
     * @type  string $id The id for the field
     * @type  string $value The value for the field
     * @type  string $class The CSS class for the field
     * @type  string $license_status The value for the field
     * @type  string $link_more_info The link for more info
     * }
     */
    public function __construct($args)
    {
        parent::__construct($args);

        $defaults = [
            'options_group_name' => '',
            'name' => '',
            'id' => '',
            'value' => '',
            'class' => '',
            'license_status' => License::STATUS_EMPTY_LICENSE,
            'link_more_info' => '',
        ];

        $args = wp_parse_args($args, $defaults);

        $this->options_group_name = $args['options_group_name'];
        $this->name               = $args['name'];
        $this->id                 = $args['id'];
        $this->value              = $args['value'];
        $this->class              = $args['class'];
        $this->license_status     = $args['license_status'];
        $this->link_more_info     = $args['link_more_info'];
    }

    /**
     * Returns a string version of this class, with the HTML code for the
     * field.
     *
     * @return string
     */
    public function __toString()
    {
        $license_status_class      = "pp-wp-edd-license-label-danger";
        $license_status_icon_class = "dashicons-warning";

        switch ($this->license_status) {
            case License::STATUS_EMPTY_LICENSE:
                $license_status_message = __("Missing license", 'wp-edd-license-integration');
                break;
            case License::STATUS_EXPIRED:
                $license_status_message = __("Your license key is expired", 'wp-edd-license-integration');
                break;
            case License::STATUS_REVOKED:
                $license_status_message = __("Your license key has been disabled", 'wp-edd-license-integration');
                break;
            case License::STATUS_MISSING:
            case License::STATUS_INVALID:
                $license_status_message = __("Invalid license", 'wp-edd-license-integration');
                break;
            case License::STATUS_SITE_INACTIVE:
                $license_status_message = __("Your license is not active for this URL", 'wp-edd-license-integration');
                break;
            case License::STATUS_ITEM_NAME_MISMATCH:
                $license_status_message = __(
                    "This appears to be an invalid license key for this product",
                    'wp-edd-license-integration'
                );
                break;
            case License::STATUS_NO_ACTIVATIONS_LEFT:
                $license_status_message = __(
                    "Your license key has reached its activation limit",
                    'wp-edd-license-integration'
                );
                break;
            case License::STATUS_VALID:
                $license_status_class      = "pp-wp-edd-license-label-success";
                $license_status_icon_class = "dashicons-yes";
                $license_status_message    = __("Activated", 'wp-edd-license-integration');
                break;
            default:
                $license_status_message = $this->license_status;
                break;
        }

        $replaces = [
            '{{ name }}'                      => $this->name,
            '{{ id }}'                        => $this->id,
            '{{ value }}'                     => $this->value,
            '{{ class }}'                     => $this->class,
            '{{ status_icon_class }}'         => $license_status_icon_class,
            '{{ status_class }}'              => $license_status_class,
            '{{ options_group_name }}'        => $this->options_group_name,
            '{{ link_more_info }}'            => $this->link_more_info,
            '{{ lang_status }}'               => __('Status', 'wp-edd-license-integration'),
            '{{ lang_status_message }}'       => $license_status_message,
            '{{ lang_click_here_read_more }}' => __(
                'Click here to read more about licenses.',
                'wp-edd-license-integration'
            ),
            '{{ lang_more_info }}'            => __('More information', 'wp-edd-license-integration'),
        ];

        return str_replace(
            array_keys($replaces),
            array_values($replaces),
            $this->get_template()
        );
    }

    /**
     * Returns the field HTML template.
     *
     * @return string
     */
    protected function get_template()
    {
        $html = '<div class="pp-wp-edd-license-container {{ class }}">';
        $html .= '<input type="text" name="{{ options_group_name }}[{{ name }}]" id="{{ options_group_name }}-{{ id }}" value="{{ value }}" class="regular-text" />';
        $html .= '<div class="pp-wp-edd-license-container-field-description">' . esc_html__('Enter the license key for being able to update the plugin.',
                                                                                    'publishpress-pro') . '</div>';
        $html .= '<br/><br/>';
        $html .= '<strong>{{ lang_status }}: <span class="dashicons {{ status_icon_class }}"></span> <span class="{{ status_class }}">{{ lang_status_message }}</span></strong>';
        $html .= '<br/><br/>';

        if (!License::STATUS_VALID === $this->license_status) {
            $html .= '<a href="{{ link_more_info }}" target="_blank" class="" rel="noopener noreferrer" style="display: inline-block; margin-left: 20px;" title="{{ lang_click_here_read_more }}">{{ lang_more_info }}</a>';
            $html .= '<br/><br/>';
        }

        $html .= '<hr></div>';

        return $html;
    }
}
