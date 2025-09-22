<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Exceptions\Repositories\MariaDB;

use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\Enums\InfrastructureExceptionTypesEnum;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\Repositories\RepositoryStatementCreationFailureException;

class MariaDBStatementCreationFailureException extends RepositoryStatementCreationFailureException
{
    public function __construct(\Throwable|null $cause = null)
    {
        parent::__construct(
            'Statement creation failure!',
            InfrastructureExceptionTypesEnum::MariaDBRepository,
            $cause
        );
    }
}
