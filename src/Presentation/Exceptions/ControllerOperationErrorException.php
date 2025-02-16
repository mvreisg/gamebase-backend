<?php

namespace Mvreisg\GamebaseBackend\Presentation\Exceptions;

use Exception;
use Throwable;

/**
 * Controller operation erro exception class.
 * Throwed when the controller tries to make a operation then fails.
 */
class ControllerOperationErrorException extends Exception
{
    /**
     * Controller operation erro exception class constructor.
     * @param string $message The exception message.
     * @param int $code The exception code.
     * @param Throwable|null $cause The exception cause object.
     */
    public function __construct(string $message = '', int $code = 0, Throwable|null $cause = null)
    {
        parent::__construct($message, $code, $cause);
    }
}
