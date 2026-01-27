<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock\Exceptions;

use Mvreisg\GamebaseBackend\Domain\Repositories\Exceptions\RepositoryDuplicatedRegisterException;

class MockDuplicatedRegisterException extends RepositoryDuplicatedRegisterException
{
    public function __construct(mixed $value)
    {
        parent::__construct(
            $value
        );
    }
}
