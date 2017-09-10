<?php

namespace OrganizeSeries\domain\exceptions;

use Exception;
use Throwable;

class LicenseKeyRequestError extends Exception
{
    public function __construct($message = '', $code = '', Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}