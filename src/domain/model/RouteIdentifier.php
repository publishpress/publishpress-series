<?php
namespace OrganizeSeries\domain\model;

use Closure;
use const FILTER_VALIDATE_BOOLEAN;
use InvalidArgumentException;
use OrganizeSeries\application\IncomingRequest;
use OrganizeSeries\domain\interfaces\RouteIdentifierInterface;

class RouteIdentifier implements RouteIdentifierInterface
{
    /**
     * The callback used to determine whether on the route or not.
     * @var Closure
     */
    private $is_on_route_callback;


    /**
     * Used to indicate whether this is an admin route or not.
     * @var bool
     */
    private $is_admin;


    /**
     * RouteIdentifier constructor.
     *
     * @param Closure $is_on_route_callback
     * @param bool    $is_admin
     * @throws InvalidArgumentException
     */
    public function __construct($is_on_route_callback, $is_admin)
    {
        $this->setIsOnRouteCallback($is_on_route_callback);
        $this->setIsAdmin($is_admin);
    }

    /**
     * Returns whether the route matches this route identifier.
     *
     * @param IncomingRequest $request
     * @return bool
     */
    public function isOnRoute(IncomingRequest $request)
    {
        $route_callback = $this->is_on_route_callback;
        return $route_callback($request);
    }

    /**
     * Return whether the route is an admin route or not.
     *
     * @return bool
     */
    public function isAdminRoute()
    {
        return $this->is_admin;
}

    /**
     * @param Closure $is_on_route_callback
     * @throws InvalidArgumentException
     */
    private function setIsOnRouteCallback($is_on_route_callback)
    {
        if (! $is_on_route_callback instanceof Closure) {
            throw new InvalidArgumentException(
                sprintf(
                    esc_html__(
                        'The incoming value for %1$s is expected to be a closure.  It was not.',
                        'organize-series'
                    ),
                    '$is_on_route_callback'
                )
            );
        }
        $this->is_on_route_callback = $is_on_route_callback;
    }

    /**
     * @param bool $is_admin
     */
    private function setIsAdmin($is_admin)
    {
        $this->is_admin = filter_var($is_admin, FILTER_VALIDATE_BOOLEAN);
    }
}