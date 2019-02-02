<?php
namespace OrganizeSeries\domain\model;

use OrganizeSeries\domain\interfaces\AbstractCollection;

/**
 * HasHooksRouteCollection
 *
 *
 * @package OrganizeSeries\domain\model
 * @author  Darren Ethier
 * @since   2.5.9
 */
class HasHooksRouteCollection extends AbstractCollection
{

    public function __construct()
    {
        parent::__construct(HasHooksRoute::class);
    }
}
