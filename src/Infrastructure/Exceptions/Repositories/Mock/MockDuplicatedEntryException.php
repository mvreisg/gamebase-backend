<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Exceptions\Repositories\Mock;

use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\Enums\InfrastructureExceptionTypesEnum;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\Repositories\RepositoryDuplicatedEntryException;

class MockDuplicatedEntryException extends RepositoryDuplicatedEntryException
{
    public function __construct(mixed $entry, \Throwable|null $cause = null)
    {
        parent::__construct(
            'Duplicated entry: ' . $entry,
            InfrastructureExceptionTypesEnum::MockRepository,
            $cause
        );
    }
}
