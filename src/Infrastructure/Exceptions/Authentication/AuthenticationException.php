<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Exceptions\Authentication;

use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\Enums\InfrastructureExceptionTypesEnum;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\InfrastructureException;

class AuthenticationException extends InfrastructureException
{
    public function __construct(string $message, \Throwable|null $previous = null)
    {
        parent::__construct(
            $message,
            InfrastructureExceptionTypesEnum::JwtAuthentication,
            $previous
        );
    }
}
