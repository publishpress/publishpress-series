<?php
namespace OrganizeSeries\domain\interfaces;

use OrganizeSeries\application\IncomingRequest;
use OrganizeSeries\domain\exceptions\NonceFailException;

/**
 * AbstractAjaxRequest
 *
 * @package OrganizeSeries\domain\interfaces
 * @author  Darren Ethier
 * @since   2.5.9
 */
abstract class AbstractAjaxRequest implements AjaxRequestInterface
{
    const NONCE_KEY = 'nonce';
    private $request;


    /**
     * AbstractAjaxRequest constructor.
     *
     * @param IncomingRequest $request
     * @param string                $nonce_action
     * @throws NonceFailException
     */
    public function __construct(IncomingRequest $request, $nonce_action)
    {
        $this->request = $request;
        $this->validateNonce($nonce_action);
    }


    /**
     * Validate whether the incoming request has a valid nonce for the given nonce action.
     *
     * @param string $nonce_action
     * @throws NonceFailException
     */
    private function validateNonce($nonce_action) {
        if (! $this->request->validateNonce($nonce_action, self::NONCE_KEY) ) {
            throw new NonceFailException;
        }
    }
}
