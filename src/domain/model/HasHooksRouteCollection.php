<?php
namespace OrganizeSeries\domain\model;

use OrganizeSeries\domain\interfaces\AbstractCollection;

class HasHooksRouteCollection extends AbstractCollection
{

    public function __construct()
    {
        parent::__construct(
            new ClassOrInterfaceFullyQualifiedName(
                HasHooksRoute::class
            )
        );
    }
}