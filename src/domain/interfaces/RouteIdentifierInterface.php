<?php
namespace OrganizeSeries\domain\interfaces;

use OrganizeSeries\application\IncomingRequest;

interface RouteIdentifierInterface
{
    /**
     * Returns whether the route matches this route identifier.
     *
     * @param IncomingRequest $request
     * @return bool
     */
    public function isOnRoute(IncomingRequest $request);



    /**
     * Return whether the route is an admin route or not.
     * @return bool
     */
    public function isAdminRoute();
}