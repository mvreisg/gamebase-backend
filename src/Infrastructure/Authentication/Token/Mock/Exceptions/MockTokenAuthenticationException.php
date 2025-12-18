<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Authentication\Token\Mock\Exceptions;

use Mvreisg\GamebaseBackend\Domain\Authentication\Exceptions\AuthenticationException;

class MockTokenAuthenticationException extends AuthenticationException
{
    public const EXCEPTION_CODE = 0;

    public function __construct(string $message = '', ?\Throwable $previous = null)
    {
        parent::__construct(
            $message,
            self::EXCEPTION_CODE,
            $previous
        );
    }
}
