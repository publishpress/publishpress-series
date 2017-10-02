<?php

namespace OrganizeSeries\application;

use Closure;
use InvalidArgumentException;
use OrganizeSeries\domain\model\ExtensionMetaCollection;
use OrganizeSeries\domain\services\AjaxJsonResponseManager;
use OrganizeSeries\domain\services\CoreBootstrap;
use OrganizeSeries\domain\services\ExtensionsRegistry;
use OrganizeSeries\domain\services\NoticeManager;
use OrganizeSeries\domain\model\ClassOrInterfaceFullyQualifiedName;
use OrganizeSeries\domain\model\CombinedNoticeCollection;
use OrganizeSeries\domain\model\ControllerRouteCollection;
use OrganizeSeries\domain\model\HasHooksRouteCollection;
use OrganizeSeries\domain\model\LicenseKeyCollection;
use OrganizeSeries\domain\model\LicenseKeyFactory;
use OrganizeSeries\domain\model\RegisteredExtensions;
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

    /**
     * Container constructor.
     * All core dependencies are registered in here vs boostrap. Extensions should use registerDependency within their
     * bootstrap class to add definitions to the container.
     *
     * @param PimpleContainer $pimple
     */
    public function __construct(PimpleContainer $pimple)
    {
        $this->container                                   = $pimple;
        $this->container[AssetRegistry::class]             = function($container) {
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
        $this->container[NoticeManager::class]             = function ($container) {
            return new NoticeManager(
                $container[CombinedNoticeCollection::class],
                $container[SingleNoticeCollection::class]
            );
        };
        $this->container[AjaxJsonResponseManager::class]   = function ($container) {
            return new AjaxJsonResponseManager(
                $container[NoticeManager::class]
            );
        };
        $this->container[LicenseKeyRepository::class]      = function ($container) {
            return new LicenseKeyRepository(
                $container[LicenseKeyCollection::class],
                $container[LicenseKeyFactory::class]
            );
        };
        $this->container[RegisteredExtensions::class]      = function ($container) {
            return new RegisteredExtensions();
        };
        $this->container[LicenseKeyFormManager::class]     = function ($container) {
            return new LicenseKeyFormManager(
                $container[LicenseKeyRepository::class],
                $container[RegisteredExtensions::class],
                $container[AssetRegistry::class],
                $container[AjaxJsonResponseManager::class],
                $container[NoticeManager::class]
            );
        };
        $this->container[IncomingRequest::class]           = function($container) {
            return new IncomingRequest($_GET, $_POST, $_COOKIE);
        };
        $this->container[ControllerRouteCollection::class] = function($container) {
            return new ControllerRouteCollection();
        };
        $this->container[HasHooksRouteCollection::class]   = function($container) {
            return new HasHooksRouteCollection();
        };
        $this->container[Router::class] = function($container) {
            return new Router(
                $container[IncomingRequest::class],
                $container[ControllerRouteCollection::class],
                $container[HasHooksRouteCollection::class]
            );
        };
        $this->container[ExtensionsRegistry::class] = function($container) {
            return new ExtensionsRegistry(
                $container[RegisteredExtensions::class],
                $container[LicenseKeyRepository::class]
            );
        };
        $this->container[CoreBootstrap::class] = function($container) {
            return new CoreBootstrap(
                $container[ExtensionsRegistry::class],
                $container[Router::class],
                $this
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
     * Extensions can use this to register a dependency with the container.
     * @param ClassOrInterfaceFullyQualifiedName $main_class_name
     * @param Closure                            $dependency_callback
     */
    public function registerDependency(
        ClassOrInterfaceFullyQualifiedName $main_class_name,
        Closure $dependency_callback
    ) {
        $this->container[$main_class_name->__toString()] = $dependency_callback;
    }


    /**
     * This registers a parameter for the container.
     * Note, this will throw an error if the given parameter name already is registered.
     * unless you set the $allow_overwrite argument to true (defaults false)
     * Note, if $value is a closure, this will automatically wrap that using the Pimple `protect` method so the closure
     * is set as a parameter rather than pimple reading it as a closure.
     *
     * @param string $name
     * @param mixed  $value
     * @param bool   $allow_overwrite
     * @throws InvalidArgumentException
     */
    public function registerParameter($name, $value, $allow_overwrite = false) {
        //does it exist?
        if ($this->container->offsetExists($name)) {
            throw new InvalidArgumentException(
                sprintf(
                    esc_html__(
                        'The %1$s already has a parameter indexed with the name: %2$s.',
                        'organize-series'
                    ),
                    'Pimple\Container',
                    $name
                )
            );
        }
        $this->container[$name] = $value instanceof Closure
            ? $this->container->protect($value)
            : $value;
    }
}