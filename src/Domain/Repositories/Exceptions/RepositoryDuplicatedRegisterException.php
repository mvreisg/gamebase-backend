<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Repositories\Exceptions;

class RepositoryDuplicatedRegisterException extends \DomainException
{
    public function __construct(mixed $value)
    {
        parent::__construct(
            "Duplicated register: $value."
        );
    }
}
