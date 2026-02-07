<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock\Exceptions;

use Mvreisg\GamebaseBackend\Domain\Repositories\Exceptions\RepositoryUnexistantRegisterException;

class MockUnexistantRegisterException extends RepositoryUnexistantRegisterException
{
    public function __construct(mixed $value)
    {
        parent::__construct(
            $value
        );
    }
}
