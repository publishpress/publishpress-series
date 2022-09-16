<?php

use OrganizeSeries\application\Root;
use OrganizeSeries\domain\model\ClassOrInterfaceFullyQualifiedName;
use OrganizeSeries\GroupingAddon\domain\Meta;
use OrganizeSeries\GroupingAddon\domain\services\Bootstrap;


Root::initializeExtensionMeta(
    __FILE__,
    OS_GROUPING_VERSION,
    new ClassOrInterfaceFullyQualifiedName(
        Meta::class
    )
);
$fully_qualified_bootstrap_class = new ClassOrInterfaceFullyQualifiedName(Bootstrap::class);
Root::registerAndLoadExtensionBootstrap($fully_qualified_bootstrap_class);
