<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Exceptions\Repositories\MariaDB;

use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\Enums\InfrastructureExceptionTypesEnum;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\Repositories\RepositoryUnexistantRegisterException;

class MariaDBUnexistantRegisterException extends RepositoryUnexistantRegisterException
{
    public function __construct(mixed $register, \Throwable|null $cause = null)
    {
        parent::__construct(
            'Unexistant register: ' . $register,
            InfrastructureExceptionTypesEnum::MariaDBRepository,
            $cause
        );
    }
}
