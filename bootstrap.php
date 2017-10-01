<?php
use OrganizeSeries\application\Root;
use OrganizeSeries\application\Router;
use OrganizeSeries\application\RouteRegistrar;
use OrganizeSeries\domain\model\ClassOrInterfaceFullyQualifiedName;

Root::container()->make(
    new ClassOrInterfaceFullyQualifiedName(Router::class)
);
Root::container()->make(
    new ClassOrInterfaceFullyQualifiedName(RouteRegistrar::class)
);