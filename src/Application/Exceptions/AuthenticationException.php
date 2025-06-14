<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Exceptions;

use Exception;
use Throwable;

class AuthenticationException extends Exception
{
    public function __construct(string $message = '', int $code = 0, Throwable|null $cause = null)
    {
        parent::__construct($message, $code, $cause);
    }
}
