<?php

namespace OrganizeSeries\domain\services\admin;

use DomainException;
use OrganizeSeries\application\Root;
use OrganizeSeries\domain\model\LicenseKey;

/**
 * LicenseKeyForm
 * Used for displaying and processing license key form.
 * @package OrganizeSeries\views\admin
 * @author  Darren Ethier
 * @since   1.0.0
 */
class LicenseKeyForm
{

    /**
     * @var LicenseKey
     */
	private $license_key;


    /**
     * The slug for the extension this form belongs to.
     * @var string
     */
	private $extension_slug;


    /**
     * LicenseKeyForm constructor.
     *
     * @param LicenseKey $license_key
     * @param string     $extension_slug
     */
	public function __construct(LicenseKey $license_key, $extension_slug)
	{
        $this->license_key = $license_key;
        $this->extension_slug = $extension_slug;
	}


    /**
     * Outputs the form for this license key
     *
     * @throws DomainException
     */
	public function printForm()
    {
        $license_key = $this->license_key;
        $extension_slug = $this->extension_slug;
        require Root::coreMeta()->adminTemplatePath() . 'license_key_form.template.php';
    }
}