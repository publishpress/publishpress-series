<?php

namespace OrganizeSeries\domain\exceptions;

use Exception;
use Throwable;

/**
 * LicenseKeyRequestError
 *
 *
 * @package OrganizeSeries\domain\exceptions
 * @author  Darren Ethier
 * @since   1.0.0
 */
class LicenseKeyRequestError extends Exception
{

    /**
     * LicenseKeyRequestError constructor.
     *
     * @param string $message
     * @param int    $code
     * @param null   $previous
     */
    public function __construct($message = '', $code = 0, $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}