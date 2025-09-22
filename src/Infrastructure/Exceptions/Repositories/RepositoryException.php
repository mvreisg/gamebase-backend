<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Exceptions\Repositories;

use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\InfrastructureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\Enums\InfrastructureExceptionTypesEnum;

class RepositoryException extends InfrastructureException
{
    public function __construct(string $message, InfrastructureExceptionTypesEnum $type, \Throwable|null $cause = null)
    {
        parent::__construct($message, $type, $cause);
    }
}
