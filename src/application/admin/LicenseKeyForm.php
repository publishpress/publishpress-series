<?php

namespace OrganizeSeries\views\admin;

use OrganizeSeries\domain\model\LicenseKeyRepository;

/**
 * LicenseKeyForm
 * Used for displaying and processing license key form.
 * @package OrganizeSeries\views\admin
 * @author  Darren Ethier
 * @since   1.0.0
 */
class LicenseKeyForm
{
	
	private $license_key_repository;
	
	
	public function __construct(LicenseKeyRepository $license_key_repository)
	{
	
	}
}