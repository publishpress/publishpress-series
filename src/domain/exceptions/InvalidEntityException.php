<?php

namespace OrganizeSeries\domain\exceptions;

use Exception;

class InvalidEntityException extends Exception
{

    /**
     * InvalidInterfaceException constructor.
     *
     * @param string         $invalid_entity
     * @param string         $expected_entity_fqcn
     * @param string         $message
     * @param int            $code
     * @param \Throwable|null $previous
     */
    public function __construct($invalid_entity, $expected_entity_fqcn = '', $message = '', $code = 0, $previous = null)
    {
        if ($expected_entity_fqcn !== '') {
            $invalid_entity = is_object($invalid_entity)
                ? get_class($invalid_entity)
                : (string) $invalid_entity;
            $message  = $message === ''
                ? $message
                : ' ';
            $message .= sprintf(
                esc_html__(
                    '%1$s is not a valid entity (expected: %2$s).',
                    'organize-series'
                ),
                $invalid_entity,
                $expected_entity_fqcn
            );
        }
        parent::__construct($message, $code, $previous);
    }
}