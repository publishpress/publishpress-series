<?php
namespace OrganizeSeries\domain\interfaces;

use OrganizeSeries\application\IncomingRequest;

\defined('ABSPATH') || exit;

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
