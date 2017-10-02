<?php
namespace OrganizeSeries\domain\interfaces;

use OrganizeSeries\application\Container;
use OrganizeSeries\application\Router;
use OrganizeSeries\domain\services\ExtensionsRegistry;

abstract class AbstractBootstrap
{

    /**
     * @var ExtensionsRegistry
     */
    private $extensions_registry;


    /**
     * @var Router
     */
    private $router;


    /**
     * @var Container
     */
    private $container;

    public function __construct(
        ExtensionsRegistry $extensions_registry,
        Router $router,
        Container $container
    ) {
        $this->extensions_registry = $extensions_registry;
        $this->router = $router;
        $this->container = $container;
        if ($this->initialized()) {
            $this->registerDependencies();
            $this->registerRoutes();
        }
    }

    /**
     * Any special initialization logic should go in this method.
     * Examples of things that might happen here are any requirement checks etc.
     * @return bool  Return false if you want the bootstrap process to be halted after initializing.
     */
    abstract protected function initialized();


    /**
     * @return ExtensionsRegistry
     */
    public function getExtensionsRegistry()
    {
        return $this->extensions_registry;
    }



    /**
     * @return Router
     */
    public function getRouter()
    {
        return $this->router;
    }


    /**
     * Any registration of dependencies on the container should happen in this method.
     */
    abstract protected function registerDependencies();


    /**
     * Classes should register any routes with the router via this method.
     */
    abstract protected function registerRoutes();
}