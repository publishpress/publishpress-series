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

// Exit if accessed directly
if (!defined('ABSPATH')) {
    die('No direct script access allowed.');
}


class Base
{
    /**
     * The value for this field
     *
     * @var string
     */
    protected $value = '';

    /**
     * The constructor
     *
     * @param array $args {
     * @type  string $options_group_name The name for the field
     * @type  string $name The name for the field
     * @type  string $id The name for the field
     * @type  string $value The value for the field
     * @type  string $class The CSS class for the field
     * @type  string $placeholder The CSS class for the field
     * }
     */
    public function __construct($args)
    {
        $defaults = [
            'options_group_name' => 'myplugin',
            'name'               => 'myfield',
            'id'                 => null,
            'value'              => '',
            'class'              => '',
            'placeholder'        => '',
        ];
        $args     = wp_parse_args($args, $defaults);

        if (!isset($args['id']) || is_null($args['id']) || false === $args['id']) {
            $args['id'] = $args['name'];
        }

        $this->options_group_name = $args['options_group_name'];
        $this->name               = $args['name'];
        $this->id                 = $args['id'];
        $this->value              = $args['value'];
        $this->class              = $args['class'];
        $this->placeholder        = $args['placeholder'];
    }
}
