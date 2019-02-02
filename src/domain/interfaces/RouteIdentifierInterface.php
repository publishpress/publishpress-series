<?php
namespace OrganizeSeries\domain\interfaces;

use OrganizeSeries\application\IncomingRequest;

/**
 * Interface RouteIdentifierInterface
 *
 *
 * @package OrganizeSeries\domain\interfaces
 * @author  Darren Ethier
 * @since   2.5.9
 */
interface RouteIdentifierInterface
{
    /**
     * Returns whether the route matches this route identifier.
     *
     * @param IncomingRequest $request
     * @return bool
     */
    public function isOnRoute(IncomingRequest $request);
}
