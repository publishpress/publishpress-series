<?php

namespace OrganizeSeries\application;

use OrganizeSeries\domain\services\AjaxJsonResponseManager;
use OrganizeSeries\domain\services\NoticeManager;
use OrganizeSeries\domain\model\ClassOrInterfaceFullyQualifiedName;
use OrganizeSeries\domain\model\CombinedNoticeCollection;
use OrganizeSeries\domain\model\ControllerRouteCollection;
use OrganizeSeries\domain\model\HasHooksRouteCollection;
use OrganizeSeries\domain\model\LicenseKeyCollection;
use OrganizeSeries\domain\model\LicenseKeyFactory;
use OrganizeSeries\domain\model\LicenseKeyRegisteredExtensions;
use OrganizeSeries\domain\model\LicenseKeyRepository;
use OrganizeSeries\domain\model\SingleNoticeCollection;
use OrganizeSeries\domain\services\admin\LicenseKeyFormManager;
use OrganizeSeries\domain\services\AssetRegistry;
use Pimple\Container as PimpleContainer;

class Container
{
    /**
     * @var PimpleContainer;
     */
    private $container;

    public function __construct(PimpleContainer $pimple)
    {
        $this->container = $pimple;
        $this->container[AssetRegistry::class] = function($container) {
            return new AssetRegistry();
        };
        $this->container[LicenseKeyCollection::class] = function ($container) {
            return new LicenseKeyCollection();
        };
        $this->container[LicenseKeyFactory::class] = function ($container) {
            return new LicenseKeyFactory();
        };
        $this->container[SingleNoticeCollection::class] = function ($container) {
            return new SingleNoticeCollection();
        };
        $this->container[CombinedNoticeCollection::class] = function ($container) {
            return new CombinedNoticeCollection();
        };
        $this->container[NoticeManager::class] = function ($container) {
            return new NoticeManager(
                $container[CombinedNoticeCollection::class],
                $container[SingleNoticeCollection::class]
            );
        };
        $this->container[AjaxJsonResponseManager::class] = function ($container) {
            return new AjaxJsonResponseManager(
                $container[NoticeManager::class]
            );
        };
        $this->container[LicenseKeyRepository::class] = function ($container) {
            return new LicenseKeyRepository(
                $container[LicenseKeyCollection::class],
                $container[LicenseKeyFactory::class]
            );
        };
        $this->container[LicenseKeyRegisteredExtensions::class] = function ($container) {
            return new LicenseKeyRegisteredExtensions();
        };
        $this->container[LicenseKeyFormManager::class] = function ($container) {
            return new LicenseKeyFormManager(
                $container[LicenseKeyRepository::class],
                $container[LicenseKeyRegisteredExtensions::class],
                $container[AssetRegistry::class],
                $container[AjaxJsonResponseManager::class],
                $container[NoticeManager::class]
            );
        };
        $this->container[IncomingRequest::class] = function($container) {
            return new IncomingRequest($_GET, $_POST, $_COOKIE);
        };
        $this->container[ControllerRouteCollection::class] = function($container) {
            return new ControllerRouteCollection();
        };
        $this->container[HasHooksRouteCollection::class] = function($container) {
            return new HasHooksRouteCollection();
        };
        $this->container[Router::class] = function($container) {
            return new Router(
                $container[IncomingRequest::class],
                $container[ControllerRouteCollection::class],
                $container[HasHooksRouteCollection::class]
            );
        };
        $this->container[RouteRegistrar::class] = function($container) {
            return new RouteRegistrar(
                $container[Router::class]
            );
        };
    }


    /**
     * Convenience wrapper to use for getting services.
     *
     * @param ClassOrInterfaceFullyQualifiedName $fully_qualified_name
     * @return mixed
     */
    public function make(ClassOrInterfaceFullyQualifiedName $fully_qualified_name)
    {
        return $this->container[$fully_qualified_name->__toString()];
    }


    /**
     * So Organize Series extensions can register their own services on the container.
     * @return PimpleContainer
     */
    public function container()
    {
        return $this->container;
    }
}