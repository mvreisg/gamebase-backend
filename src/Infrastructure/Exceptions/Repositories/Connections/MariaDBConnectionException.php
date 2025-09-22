<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Exceptions\Repositories\Connections;

use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\Enums\InfrastructureExceptionTypesEnum;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\Repositories\RepositoryException;

class MariaDBConnectionException extends RepositoryException
{
    public function __construct(string $message, \Throwable|null $previous = null)
    {
        parent::__construct(
            $message,
            InfrastructureExceptionTypesEnum::MariaDBRepository,
            $previous
        );
    }
}
