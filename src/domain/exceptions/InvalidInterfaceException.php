<?php

namespace OrganizeSeries\domain\exceptions;

use Exception;
use Throwable;

class InvalidInterfaceException extends Exception
{

    /**
     * InvalidInterfaceException constructor.
     *
     * @param string         $failing_fqcn  The string that is not qualifying to a a class/interface.
     * @param string         $message
     * @param int            $code
     * @param Throwable|null $previous
     */
	public function __construct($failing_fqcn = '', $message = '', $code = 0, Throwable $previous = null)
	{
		if ($failing_fqcn !== '') {
			$message  = $message === ''
				? $message
				: ' ';
			$message .= sprintf(
				esc_html__(
					'%s does not exist or is not reachable.',
					'organize-series'
				),
				$failing_fqcn
			);
		}
		parent::__construct($message, $code, $previous);
	}
}