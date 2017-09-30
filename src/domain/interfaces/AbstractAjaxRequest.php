<?php
namespace OrganizeSeries\domain\interfaces;

use OrganizeSeries\application\IncomingRequest;
use OrganizeSeries\domain\exceptions\NonceFailException;

abstract class AbstractAjaxRequest implements AjaxRequestInterface
{
    const NONCE_KEY = 'nonce';
    private $request;

    public function __construct(IncomingRequest $request, $nonce_action)
    {
        $this->request = $request;
        $this->validateNonce($nonce_action);
    }


    private function validateNonce($nonce_action) {
        if (! $this->request->validateNonce($nonce_action, self::NONCE_KEY) ) {
            throw new NonceFailException;
        }
    }
}