<?php

namespace Mvreisg\GamebaseBackend\Infrastructure\Exceptions;

use Exception;
use Throwable;

class HttpUnauthorizedException extends Exception
{
    public function __construct(string $message, int $code = 0, Throwable|null $cause = null)
    {
        parent::__construct($message, $code, $cause);
    }
}
