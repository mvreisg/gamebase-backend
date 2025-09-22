<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Exceptions\Repositories\MariaDB;

use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\Enums\InfrastructureExceptionTypesEnum;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\Repositories\RepositoryFetchFailureException;

class MariaDBFetchFailureException extends RepositoryFetchFailureException
{
    public function __construct(\Throwable|null $cause = null)
    {
        parent::__construct(
            'Fetch failure!',
            InfrastructureExceptionTypesEnum::MariaDBRepository,
            $cause
        );
    }
}
