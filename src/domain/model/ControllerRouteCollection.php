<?php
namespace OrganizeSeries\domain\model;

use OrganizeSeries\domain\interfaces\AbstractCollection;

class ControllerRouteCollection extends AbstractCollection
{

    public function __construct()
    {
        parent::__construct(
            new ClassOrInterfaceFullyQualifiedName(
                ControllerRoute::class
            )
        );
    }
}