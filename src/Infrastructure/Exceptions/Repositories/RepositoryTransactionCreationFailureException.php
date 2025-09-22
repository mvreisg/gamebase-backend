<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Exceptions\Repositories;

use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\Enums\InfrastructureExceptionTypesEnum;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\Repositories\RepositoryException;

class RepositoryTransactionCreationFailureException extends RepositoryException
{
    public function __construct(string $message, InfrastructureExceptionTypesEnum $type, \Throwable|null $previous)
    {
        parent::__construct($message, $type, $previous);
    }
}
