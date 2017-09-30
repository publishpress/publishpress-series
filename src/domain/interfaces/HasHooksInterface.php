<?php
namespace OrganizeSeries\domain\interfaces;

use OrganizeSeries\application\IncomingRequest;

interface HasHooksInterface
{
    /**
     * Classes implementing this interface should set any hooks on this method.
     * @param IncomingRequest $request
     */
    public function setHooks(IncomingRequest $request);
}