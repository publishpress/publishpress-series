<?php
use OrganizeSeries\application\Root;
use OrganizeSeries\domain\model\ClassOrInterfaceFullyQualifiedName;
use OrganizeSeries\domain\services\CoreBootstrap;


//initialize Meta (path registration etc)
Root::initialize(__FILE__, ORG_SERIES_VERSION);
//register routes
Root::container()->make(
    new ClassOrInterfaceFullyQualifiedName(CoreBootstrap::class)
);
//this is the hook that all Organize Series Extensions should hook in on.
do_action('AHOS__bootstrapped');