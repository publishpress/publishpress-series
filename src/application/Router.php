<?php
namespace OrganizeSeries\application;

use OrganizeSeries\domain\interfaces\AbstractCollection;
use OrganizeSeries\domain\interfaces\ControllerInterface;
use OrganizeSeries\domain\interfaces\HasHooksInterface;
use OrganizeSeries\domain\model\ControllerRoute;
use OrganizeSeries\domain\model\ControllerRouteCollection;
use OrganizeSeries\domain\model\HasHooksRoute;
use OrganizeSeries\domain\model\HasHooksRouteCollection;

/**
 * Router
 * This class is a simple router for registering classes/controllers etc that get
 * loaded executed on routes.
 * Classes may be of two types.
 * 1. ControllerInterface (OrganizeSeries\domain\interfaces\ControllerInterface)
 *      These are classes that have a `execute` method and they take care of everything
 *      necessary on the route.  Acts like a traditional controller. Registered via a ControllerRoute object.
 * 2. HasHooksInterface (OrganizeSeries\domain\interfaces\HasHooksInterface)
 *      Classes implementing this interface have a `setHooks` method.  There may be more than one
 *      of these classes registered per route.  They have the responsibility of simply hooking into aspects of a given
 *      route (or routes) to enhance what's already there.  Typically Organize Series will be registering
 *      HasHooks classes for routes because they hook into functionality already loaded via a controller. HasHooks
 *      classes are always called BEFORE the controller is called.  These classes are registered via the
 *      HasHooksRoute value object.
 *
 * @package OrganizeSeries\application
 * @author  Darren Ethier
 * @since   1.0.0
 */
class Router {


    /**
     * @var ControllerRouteCollection
     */
    private $controller_route_collection;


    /**
     * @var HasHooksRouteCollection
     */
    private $has_hooks_route_collection;

    /**
     * @var IncomingRequest
     */
    private $request;


    /**
     * Router constructor.
     *
     * @param IncomingRequest           $request
     * @param ControllerRouteCollection $controller_route_collection
     * @param HasHooksRouteCollection   $has_hooks_route_collection
     */
    public function __construct(
        IncomingRequest $request,
        ControllerRouteCollection $controller_route_collection,
        HasHooksRouteCollection $has_hooks_route_collection
    ) {
        $this->request = $request;
        $this->controller_route_collection = $controller_route_collection;
        $this->has_hooks_route_collection = $has_hooks_route_collection;
        add_action('plugins_loaded', array($this, 'route'), 20);
    }


    /**
     * Takes care of checking each registered controller and has hook route and loading any matches for the incoming
     * request.
     */
    public function route()
    {
        $this->loadFromCollection($this->has_hooks_route_collection);
        $this->loadFromCollection($this->controller_route_collection);
    }


    /**
     * @param ControllerRoute $controller_route
     * @throws \OrganizeSeries\domain\exceptions\InvalidEntityException
     */
    public function registerControllerRoute(ControllerRoute $controller_route)
    {
        $this->controller_route_collection->add($controller_route);
    }


    /**
     * @param HasHooksRoute $has_hooks_route
     * @throws \OrganizeSeries\domain\exceptions\InvalidEntityException
     */
    public function registerHasHooksRoute(HasHooksRoute $has_hooks_route)
    {
        $this->has_hooks_route_collection->add($has_hooks_route);
    }


    /**
     * Takes the incoming Route collection and if the route identifier indicates a match, instantiates the related class.
     * Note: Important that the class of course is registered on the container!
     *
     * @param AbstractCollection $collection
     */
    private function loadFromCollection(AbstractCollection $collection) {
        foreach ($collection as $route) {
            if ($route->getRouteIdentifier()->isOnRoute($this->request)) {
                $route_executor = Root::container()->make($route->getFullyQualifiedClassName());
                $this->execute($route_executor);
            }
        }
    }


    /**
     * @param HasHooksInterface|ControllerInterface $route_executor
     */
    private function execute($route_executor)
    {
        if ($route_executor instanceof HasHooksInterface) {
            $route_executor->setHooks($this->request);
        } else {
            $route_executor->execute($this->request);
        }
    }
}