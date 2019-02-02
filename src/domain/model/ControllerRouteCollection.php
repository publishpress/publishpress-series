<?php
namespace OrganizeSeries\domain\model;

use OrganizeSeries\domain\interfaces\AbstractCollection;

/**
 * ControllerRouteCollection
 *
 *
 * @package OrganizeSeries\domain\model
 * @author  Darren Ethier
 * @since   2.5.9
 */
class ControllerRouteCollection extends AbstractCollection
{

    public function __construct()
    {
        parent::__construct(ControllerRoute::class);
    }
}
