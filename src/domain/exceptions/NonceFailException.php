<?php
namespace OrganizeSeries\domain\exceptions;

use Exception;
use InvalidArgumentException;

/**
 * NonceFailException
 *
 *
 * @package OrganizeSeries\domain\exceptions
 * @author  Darren Ethier
 * @since   2.5.9
 */
class NonceFailException extends InvalidArgumentException
{
    /**
     * NonceFailException constructor.
     *
     * @param string $message
     * @param int    $code
     * @param Exception|null   $previous
     */
    public function __construct($message = '', $code = 0, $previous = null) {
        if ($message === '') {
            $message = esc_html__(
                'Nonce fail.',
                'organize-series'
            );
        }
        parent::__construct($message, $code, $previous);
    }
}
