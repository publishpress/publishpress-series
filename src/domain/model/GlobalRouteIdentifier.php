<?php
namespace OrganizeSeries\domain\model;

/**
 * GlobalRouteIdentifier
 * This route identifier just instructs a route to load on every request.
 *
 * @package OrganizeSeries\domain\model
 * @author  Darren Ethier
 * @since   1.0.0
 */
class GlobalRouteIdentifier extends RouteIdentifier
{
    public function __construct() {
        parent::__construct(
            function() { return true; }
        );
    }
}