<?php

namespace OrganizeSeries\domain\model;


use stdClass;

/**
 * LicenseKeyFactory
 * For constructing a LicenseKey entity.
 *
 * @package OrganizeSeries\domain\model
 * @author  Darren Ethier
 * @since   1.0.0
 */
class LicenseKeyFactory
{

    /**
     * This creates a LicenseKey entity from the given data.
     * @param stdClass $license_data
     * @param string   $key  The license key associated with the license data.
     * @return LicenseKey
     */
	public function create(stdClass $license_data, $key) {
	    return new LicenseKey($license_data, $key);
    }
}