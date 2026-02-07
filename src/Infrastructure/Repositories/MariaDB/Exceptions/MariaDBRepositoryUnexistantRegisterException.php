<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\Exceptions;

use Mvreisg\GamebaseBackend\Domain\Repositories\Exceptions\RepositoryUnexistantRegisterException;

class MariaDBRepositoryUnexistantRegisterException extends RepositoryUnexistantRegisterException
{
    public function __construct(mixed $value)
    {
        parent::__construct(
            $value
        );
    }
}
