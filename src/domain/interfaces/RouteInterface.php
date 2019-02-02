<?php
namespace OrganizeSeries\domain\interfaces;

/**
 * Interface RouteInterface
 * Any route classes should implement this interface.
 *
 * @package OrganizeSeries\domain\interfaces
 * @subpackage
 * @author  Darren Ethier
 * @since   2.5.9
 */
interface RouteInterface
{
    /**
     * Should return the fqcn for the class used to execute the route.
     * @return string
     */
    public function getFullyQualifiedClassName();


    /**
     * @return RouteIdentifierInterface
     */
    public function getRouteIdentifier();
}
