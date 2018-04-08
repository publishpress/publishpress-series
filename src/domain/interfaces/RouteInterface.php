<?php
namespace OrganizeSeries\domain\interfaces;

use OrganizeSeries\domain\model\ClassOrInterfaceFullyQualifiedName;

/**
 * Interface RouteInterface
 * Any route classes should implement this interface.
 *
 * @package OrganizeSeries\domain\interfaces
 * @subpackage
 * @author  Darren Ethier
 * @since   2.5.8
 */
interface RouteInterface
{
    /**
     * Should return the fqcn for the class used to execute the route.
     * @return ClassOrInterfaceFullyQualifiedName
     */
    public function getFullyQualifiedClassName();


    /**
     * @return RouteIdentifierInterface
     */
    public function getRouteIdentifier();
}