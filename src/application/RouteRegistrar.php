<?php

namespace OrganizeSeries\application;

use InvalidArgumentException;
use OrganizeSeries\domain\exceptions\InvalidEntityException;
use OrganizeSeries\domain\exceptions\InvalidInterfaceException;
use OrganizeSeries\domain\model\ClassOrInterfaceFullyQualifiedName;
use OrganizeSeries\domain\model\HasHooksRoute;
use OrganizeSeries\domain\model\RouteIdentifier;
use OrganizeSeries\domain\services\admin\LicenseKeyFormManager;

/**
 * RouteRegistrar
 * The purpose of this class is to register the core routes for Organize Series.
 *
 * @package OrganizeSeries\application
 * @author  Darren Ethier
 * @since   1.0.0
 */
class RouteRegistrar
{

    /**
     * @var Router
     */
    private $router;

    public function __construct(Router $router)
    {
        $this->router = $router;
        $this->registerControllers();
        $this->registerHasHooks();
    }


    /**
     * Register all controller routes
     */
    private function registerControllers()
    {
        //nothing here right yet.
    }


    /**
     * Register all HasHooks routes.
     *
     * @throws InvalidEntityException
     * @throws InvalidArgumentException
     * @throws InvalidInterfaceException
     */
    private function registerHasHooks()
    {
        $is_admin = is_admin();
        $this->router->registerHasHooksRoute(
            new HasHooksRoute(
                new ClassOrInterfaceFullyQualifiedName(LicenseKeyFormManager::class),
                new RouteIdentifier(
                    function (IncomingRequest $request) use ($is_admin) {
                        return $is_admin
                               && ($request->get('page', false) === 'orgseries_options_page'
                                   || $request->hasAjaxActions(
                                       array('os_license_key_activation', 'os_license_key_deactivation')
                                )
                               );
                    },
                    true
                )
            )
        );
    }
}