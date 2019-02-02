<?php
namespace OrganizeSeries\domain\interfaces;

use OrganizeSeries\application\IncomingRequest;

/**
 * Interface ControllerInterface
 *
 * @package OrganizeSeries\domain\interfaces
 * @author  Darren Ethier
 * @since   2.5.9
 */
interface ControllerInterface
{
    /**
     * Should kick off anything in the controller for the route.
     *
     * @param IncomingRequest $request
     * @return mixed
     */
    public function execute(IncomingRequest $request);
}
