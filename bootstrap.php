<?php
use OrganizeSeries\application\Root;
use OrganizeSeries\application\RouteRegistrar;
use OrganizeSeries\domain\Meta;
use OrganizeSeries\domain\model\ClassOrInterfaceFullyQualifiedName;

//initialize Meta (path registration etc)
Meta::init(__FILE__, ORG_SERIES_VERSION);
//register routes
Root::container()->make(
    new ClassOrInterfaceFullyQualifiedName(RouteRegistrar::class)
);