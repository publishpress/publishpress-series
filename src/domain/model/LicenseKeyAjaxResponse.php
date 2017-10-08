<?php
namespace OrganizeSeries\domain\model;

class LicenseKeyAjaxResponse extends AjaxJsonResponse
{
    /**
     * LicenseKeyAjaxResponse constructor.
     *
     * @param string     $action
     * @param LicenseKey $license_key
     */
    public function __construct($action, LicenseKey $license_key)
    {
        parent::__construct(
            $action,
            $license_key->isSuccess(),
            $this->getMetaContent($license_key)
        );
    }


    private function getMetaContent(LicenseKey $license_key)
    {
        if ($license_key->isSuccess()) {
            if ($license_key->getExpires() !== '') {
                return '<p>'
                       . '<span class="dashicons dashicons-yes os-key-active"></span>'
                       . '<strong>Expires:</strong> ' . $license_key->getExpires()
                       . '</p>';
            }
            return '&nbsp;';
        }
        return '<p>'
            . '<span class="dashicons dashicons-no os-key-inactive"></span>'
            . '</p>';

    }
}