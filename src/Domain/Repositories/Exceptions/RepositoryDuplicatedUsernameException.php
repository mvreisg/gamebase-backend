<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Repositories\Exceptions;

abstract class RepositoryDuplicatedUsernameException extends \Exception
{
    public const EXCEPTION_CODE = 0;

    public function __construct(string $message = "", int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct(
            $message,
            $code,
            $previous
        );
    }
}
