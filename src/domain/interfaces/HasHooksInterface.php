<?php
namespace OrganizeSeries\domain\interfaces;

use OrganizeSeries\application\IncomingRequest;

/**
 * Interface HasHooksInterface
 *
 *
 * @package OrganizeSeries\domain\interfaces
 * @author  Darren Ethier
 * @since   2.5.9
 */
interface HasHooksInterface
{
    /**
     * Classes implementing this interface should set any hooks on this method.
     * @param IncomingRequest $request
     */
    public function setHooks(IncomingRequest $request);
}
