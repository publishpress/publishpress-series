<?php
namespace OrganizeSeries\domain\interfaces;

use OrganizeSeries\application\IncomingRequest;

\defined('ABSPATH') || exit;

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
