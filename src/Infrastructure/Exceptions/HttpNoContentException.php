<?php

namespace Mvreisg\GamebaseBackend\Infrastructure\Exceptions;

use Exception;
use Throwable;

/**
 * HTTP 204 status code class.
 * Throwed when the request is successful but have no body return.
 */
class HttpNoContentException extends Exception
{
    /**
     * HTTP 204 status code classconstructor.
     * @param string $message The exception message.
     * @param int $code The exception code.
     * @param Throwable|null $cause The exception cause object.
     */
    public function __construct(string $message, int $code = 0, Throwable|null $cause = null)
    {
        parent::__construct($message, $code, $cause);
    }
}
