<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Authentication\Token\Jwt\Exceptions;

use Mvreisg\GamebaseBackend\Domain\Authentication\Exceptions\AuthenticationException;

class JwtTokenAuthenticationException extends AuthenticationException
{
    public const EXCEPTION_CODE = 0;

    public function __construct(string $message = "", ?\Throwable $previous = null)
    {
        parent::__construct(
            $message,
            self::EXCEPTION_CODE,
            $previous
        );
    }
}
