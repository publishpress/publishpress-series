<?php
namespace OrganizeSeries\domain\model;

use InvalidArgumentException;
use OrganizeSeries\domain\interfaces\HasHooksInterface;
use OrganizeSeries\domain\interfaces\RouteIdentifierInterface;
use OrganizeSeries\domain\interfaces\RouteInterface;

class HasHooksRoute implements RouteInterface
{
    /**
     * @var string
     */
    private $fully_qualified_hooks_class_name;

    /**
     * @var RouteIdentifierInterface
     */
    private $route_identifier;


    /**
     * HasHooksRoute constructor.
     *
     * @param string $fully_qualified_class_name
     * @param RouteIdentifierInterface           $route_identifier
     * @throws InvalidArgumentException
     */
    public function __construct(
        $fully_qualified_class_name,
        RouteIdentifierInterface $route_identifier
    ) {
        $this->setFullyQualifiedClassName($fully_qualified_class_name);
        $this->setRouteIdentifier($route_identifier);
    }



    /**
     * @param RouteIdentifierInterface $route_identifier
     */
    private function setRouteIdentifier($route_identifier)
    {
        $this->route_identifier = $route_identifier;
    }

    /**
     * @param string $fully_qualified_hooks_class_name
     * @throws InvalidArgumentException
     */
    private function setFullyQualifiedClassName($fully_qualified_hooks_class_name)
    {
        if (! in_array(
            HasHooksInterface::class,
            class_implements($fully_qualified_hooks_class_name),
            true
        )) {
            throw new InvalidArgumentException(
                sprintf(
                    esc_html__(
                        'The provided object fully qualified class name (%1$s) must implement the %2$s interface.',
                        'organize-series'
                    ),
                    $fully_qualified_hooks_class_name,
                    HasHooksInterface::class
                )
            );
        }
        $this->fully_qualified_hooks_class_name = $fully_qualified_hooks_class_name;
    }

    /**
     * @return string
     */
    public function getFullyQualifiedClassName()
    {
        return $this->fully_qualified_hooks_class_name;
    }

    /**
     * @return RouteIdentifierInterface
     */
    public function getRouteIdentifier()
    {
        return $this->route_identifier;
    }

}