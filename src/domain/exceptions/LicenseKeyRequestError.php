<?php

namespace OrganizeSeries\domain\exceptions;

use Exception;
use Throwable;

/**
 * LicenseKeyRequestError
 * Thrown when a key is not a valid license key.
 *
 * @package OrganizeSeries\domain\exceptions
 * @author  Darren Ethier
 * @since   2.5.9
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
