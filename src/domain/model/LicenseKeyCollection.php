<?php

namespace OrganizeSeries\domain\model;

use OrganizeSeries\domain\exceptions\InvalidEntityException;
use OrganizeSeries\domain\interfaces\AbstractCollection;


/**
 * LicenseKeyCollection
 * For holding a collection of LicenseKey objects.
 *
 * @package OrganizeSeries\domain\model
 * @author  Darren Ethier
 * @since   2.5.9
 */
class LicenseKeyCollection extends AbstractCollection
{

    /**
     * LicenseKeyCollection constructor.
     */
    public function __construct()
	{
		parent::__construct(LicenseKey::class);
	}


    /**
     * @param LicenseKey $object
     * @param null   $identifier
     * @return bool
     * @throws InvalidEntityException
     */
    public function add($object, $identifier = null)
    {
        return parent::add($object, $identifier);
    }


    /**
     * @param string $identifier
     * @return LicenseKey
     */
    public function get($identifier)
    {
        return parent::get($identifier);
    }
}
