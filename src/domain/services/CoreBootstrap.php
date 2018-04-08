<?php
namespace OrganizeSeries\domain\services;

use DomainException;
use InvalidArgumentException;
use OrganizeSeries\application\IncomingRequest;
use OrganizeSeries\application\Root;
use OrganizeSeries\domain\exceptions\InvalidEntityException;
use OrganizeSeries\domain\exceptions\InvalidInterfaceException;
use OrganizeSeries\domain\interfaces\AbstractBootstrap;
use OrganizeSeries\domain\model\ClassOrInterfaceFullyQualifiedName;
use OrganizeSeries\domain\model\HasHooksRoute;
use OrganizeSeries\domain\model\RouteIdentifier;
use OrganizeSeries\domain\services\admin\LicenseKeyFormManager;

class CoreBootstrap extends AbstractBootstrap
{

    /**
     * Load legacy.  Eventually we'll put in here the php version check rather than in the main file.
     * @return bool
     */
    protected function initialized()
    {
        $this->requireLegacyOrganizeSeries();
        return true;
    }

    /**
     * @throws DomainException
     */
    private function requireLegacyOrganizeSeries()
    {
        /** ugh but necessary for back-compat until things are deprecated fully */
        global $orgseries;
        require_once Root::coreMeta()->getBasePath() . 'orgSeries-setup.php';
        require_once Root::coreMeta()->getBasePath() . 'orgSeries-options.php';
        require_once Root::coreMeta()->getBasePath() . 'orgSeries-rss.php';
        require_once Root::coreMeta()->getBasePath() . 'orgSeries-admin.php';
        require_once Root::coreMeta()->getBasePath() . 'orgSeries-icon.php';
        require_once Root::coreMeta()->getBasePath() . 'orgSeries-taxonomy.php';
        require_once Root::coreMeta()->getBasePath() . 'orgSeries-template-tags.php';
        require_once Root::coreMeta()->getBasePath() . 'orgSeries-utility.php';
        require_once Root::coreMeta()->getBasePath() . 'orgSeries-widgets.php';
        require_once Root::coreMeta()->getBasePath() . 'orgSeries-manage.php';
        require_once Root::coreMeta()->getBasePath() . 'inc/debug/plugin_activation_errors.php';
    }


    /**
     * All core dependencies are registered directly within the Container.
     */
    protected function registerDependencies()
    {
        //noop
    }


    /**
     * Register routes for core.
     * @throws InvalidEntityException
     * @throws InvalidArgumentException
     * @throws InvalidInterfaceException
     */
    protected function registerRoutes()
    {
        $is_admin = is_admin();
        $this->getRouter()->registerHasHooksRoute(
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
                    }
                )
            )
        );
    }
}