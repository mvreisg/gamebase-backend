<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Exceptions\Repositories\MariaDB;

use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\Enums\InfrastructureExceptionTypesEnum;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\Repositories\RepositoryTransactionCreationFailureException;

class MariaDBTransactionCreationFailureException extends RepositoryTransactionCreationFailureException
{
    public function __construct(\Throwable|null $cause = null)
    {
        parent::__construct(
            'Transaction creation failure!',
            InfrastructureExceptionTypesEnum::MariaDBRepository,
            $cause
        );
    }
}
