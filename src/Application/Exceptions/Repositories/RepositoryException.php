<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Exceptions\Repositories;

use Mvreisg\GamebaseBackend\Application\Exceptions\ApplicationException;
use Mvreisg\GamebaseBackend\Application\Exceptions\Enums\ApplicationExceptionTypesEnum;

class RepositoryException extends ApplicationException
{
    public function __construct(string $message, \Throwable|null $previous = null)
    {
        parent::__construct(
            $message,
            ApplicationExceptionTypesEnum::Repository,
            $previous
        );
    }
}
