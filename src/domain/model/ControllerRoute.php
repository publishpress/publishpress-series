<?php

namespace OrganizeSeries\domain\model;

use InvalidArgumentException;
use OrganizeSeries\domain\interfaces\ControllerInterface;
use OrganizeSeries\domain\interfaces\RouteIdentifierInterface;
use OrganizeSeries\domain\interfaces\RouteInterface;

class ControllerRoute implements RouteInterface
{
    /**
     * @var string
     */
    private $controller_fully_qualified_classname;

    /**
     * @var RouteIdentifierInterface
     */
    private $route_identifier;


    /**
     * ControllerRoute constructor.
     *
     * @param string $fully_qualified_name
     * @param RouteIdentifierInterface           $route_identifier
     * @throws InvalidArgumentException
     */
    public function __construct(
        $fully_qualified_name,
        RouteIdentifierInterface $route_identifier
    ) {
        $this->setControllerFullyQualifiedClassname($fully_qualified_name);
        $this->setRouteIdentifier($route_identifier);
    }

    /**
     * @param $controller_fully_qualified_classname
     * @throws InvalidArgumentException
     */
    private function setControllerFullyQualifiedClassname($controller_fully_qualified_classname)
    {
        if (! in_array(
            'OrganizeSeries\domain\interfaces\ControllerInterface',
            class_implements($controller_fully_qualified_classname),
            true
        )) {
            throw new InvalidArgumentException(
                sprintf(
                    esc_html__(
                        'The provided object fully qualified class name (%1$s) must implement the %2$s interface.',
                        'organize-series'
                    ),
                    $controller_fully_qualified_classname,
                    ControllerInterface::class
                )
            );
        }
        $this->controller_fully_qualified_classname = $controller_fully_qualified_classname;
    }

    /**
     * @param RouteIdentifierInterface $route_identifier
     */
    private function setRouteIdentifier(RouteIdentifierInterface $route_identifier)
    {
        $this->route_identifier = $route_identifier;
    }

    /**
     * @return string
     */
    public function getFullyQualifiedClassName()
    {
        return $this->controller_fully_qualified_classname;
    }

    /**
     * @return RouteIdentifierInterface
     */
    public function getRouteIdentifier()
    {
        return $this->route_identifier;
    }
}