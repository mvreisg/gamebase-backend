<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Repositories\Exceptions;

class RepositoryUnexistantRegisterException extends \DomainException
{
    public function __construct(mixed $value)
    {
        parent::__construct(
            "Unexistant register with following value: $value."
        );
    }
}
