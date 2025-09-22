<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Exceptions\Authentication;

use Mvreisg\GamebaseBackend\Application\Exceptions\ApplicationException;
use Mvreisg\GamebaseBackend\Application\Exceptions\Enums\ApplicationExceptionTypesEnum;

class AuthenticationException extends ApplicationException
{
    public function __construct(string $message, \Throwable|null $cause = null)
    {
        parent::__construct(
            $message,
            ApplicationExceptionTypesEnum::Authentication,
            $cause
        );
    }
}
