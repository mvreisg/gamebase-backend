<?php

namespace Mvreisg\GamebaseBackend\Infrastructure\Exceptions;

use Exception;
use Throwable;

/**
 * HTTP JSON parse exception class.
 * Throwed when a error occurs in JSON parsing.
 */
class HttpJsonParseException extends Exception
{
    /**
     * HTTP JSON parse exception class constructor.
     * @param string $message The exception message.
     * @param int $code The exception code.
     * @param Throwable|null $cause The exception cause object.
     */
    public function __construct(string $message, int $code = 0, Throwable|null $cause = null)
    {
        parent::__construct($message, $code, $cause);
    }
}
