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

namespace PublishPress\WordPressEDDLicense\Exception;

use Exception;

// Exit if accessed directly
if (!defined('ABSPATH')) {
    die('No direct script access allowed.');
}


/**
 * InvalidParams exception.
 *
 * @since      1.0.0
 * @package    WordPress-EDD-License-Integration
 * @author     PublishPress
 */
class InvalidParams extends Exception
{

}
