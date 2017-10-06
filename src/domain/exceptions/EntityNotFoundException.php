<?php
namespace OrganizeSeries\domain\exceptions;

use Exception;
use InvalidArgumentException;


/**
 * EntityNotFoundException
 * Used when expecting a specific entity and its not found.
 *
 * @package OrganizeSeries\domain\exceptions
 * @author  Darren Ethier
 * @since   1.0.0
 */
class EntityNotFoundException extends InvalidArgumentException
{

    /**
     * EntityNotFoundException constructor.
     *
     * @param string $expected_entity
     * @param string $message
     * @param int $code
     * @param Exception       $previous
     */
    public function __construct($expected_entity, $message = '', $code = 0, $previous = null)
    {
        $message = sprintf(
            esc_html__(
                'Unable to retrieve an instance of %1$s. Not found.',
                'organize-series'
             )
        ) . "\n" . $message;
        parent::__construct($message, $code, $previous);
    }
}