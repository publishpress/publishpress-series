<?php
namespace OrganizeSeries\domain\model;

use OrganizeSeries\application\IncomingRequest;
use OrganizeSeries\domain\interfaces\AbstractAjaxRequest;

class LicenseKeyAjaxRequest extends AbstractAjaxRequest
{
    /**
     * This is whatever is set as the license key.
     * @var string
     */
    private $license_key;


    /**
     * Whatever is set as the extension slug.
     * @var string
     */
    private $extension;

    public function __construct(IncomingRequest $request)
    {
        $this->setLicenseKey($request);
        $this->setExtension($request);
        parent::__construct($request, $this->getNonceAction());
    }


    /**
     * Return the nonce action identifier.
     * @return string
     */
    public function getNonceAction() {
        return 'os_license_key_nonce_' . $this->extension;
    }


    /**
     * @return string
     */
    public function getLicenseKey()
    {
        return $this->license_key;
    }

    /**
     * @param IncomingRequest $request
     */
    private function setLicenseKey(IncomingRequest $request)
    {
        $this->license_key = sanitize_key($request->get('license_key', ''));
    }


    /**
     * @return string
     */
    public function getExtension()
    {
        return $this->extension;
    }


    /**
     * @param IncomingRequest $request
     */
    private function setExtension(IncomingRequest $request)
    {
        $this->extension = sanitize_key($request->get('extension', ''));
    }
}