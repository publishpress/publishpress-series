<?php

namespace OrganizeSeries\application;

use OrganizeSeries\domain\model\LicenseKeyCollection;
use OrganizeSeries\domain\model\LicenseKeyFactory;
use OrganizeSeries\domain\model\LicenseKeyRepository;
use Pimple\Container as PimpleContainer;

class Container
{
	/**
	 * @var PimpleContainer;
	 */
	private $container;
	
	public function __construct(PimpleContainer $pimple)
	{
		$this->container = $pimple;
		//@see https://github.com/silexphp/Pimple
		$this->container['LicenseKeyCollection'] = function ($container) {
			return new LicenseKeyCollection();
		};
		$this->container['LicenseKeyFactory'] = function($container) {
			return new LicenseKeyFactory();
		};
		$this->container['LicenseKeyRepository'] = function ($container) {
			return new LicenseKeyRepository(
				$container['LicenseKeyCollection'],
				$container['LicenseKeyFactory']
			);
		};
	}
	
	
	/**
	 * Convenience wrapper to use for getting services.
	 *
	 * @param $classname
	 *
	 * @return mixed
	 */
	public function make($classname) {
		return $this->container[$classname];
	}
}