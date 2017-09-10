<?php

namespace OrganizeSeries\application;

use Pimple\Container as PimpleContainer;

/**
 * Root
 * This provides access to base services that are used everywhere (i.e. DI container).
 * @package OrganizeSeries\application
 * @author  Darren Ethier
 * @since   1.0.0
 */
class Root
{
	/**
	 * @var Container
	 */
	private static $container;
	
	public static function container()
	{
		if (! self::$container instanceof Container)
		{
			self::$container = new Container(new PimpleContainer());
		}
		return self::$container;
	}
}