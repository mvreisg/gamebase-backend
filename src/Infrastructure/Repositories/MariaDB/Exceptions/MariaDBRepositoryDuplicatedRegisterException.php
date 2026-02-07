<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDB\Exceptions;

use Mvreisg\GamebaseBackend\Domain\Repositories\Exceptions\RepositoryDuplicatedRegisterException;

class MariaDBRepositoryDuplicatedRegisterException extends RepositoryDuplicatedRegisterException
{
    public function __construct(mixed $value)
    {
        parent::__construct(
            $value
        );
    }
}
