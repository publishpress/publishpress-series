<?php

namespace OrganizeSeries\domain\exceptions;

use Exception;
use Throwable;

class InvalidEntityException extends Exception
{

    /**
     * InvalidInterfaceException constructor.
     *
     * @param string         $invalid_entity_fqcn  The string that is not qualifying to a a class/interface.
     * @param string         $message
     * @param int            $code
     * @param Throwable|null $previous
     */
    public function __construct($invalid_entity_fqcn = '', $message = '', $code = 0, Throwable $previous = null)
    {
        if ($invalid_entity_fqcn !== '') {
            $message  = $message === ''
                ? $message
                : ' ';
            $message .= sprintf(
                esc_html__(
                    '%s is not a valid entity.',
                    'organize-series'
                ),
                $invalid_entity_fqcn
            );
        }
        parent::__construct($message, $code, $previous);
    }
}