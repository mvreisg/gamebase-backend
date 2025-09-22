<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Exceptions\Entities;

use Mvreisg\GamebaseBackend\Domain\Exceptions\DomainException;
use Mvreisg\GamebaseBackend\Domain\Exceptions\Enums\DomainExceptionTypesEnum;

class EntityInvalidValueException extends DomainException
{
    public function __construct(string $message, \Throwable|null $cause = null)
    {
        parent::__construct($message, DomainExceptionTypesEnum::InvalidValue, $cause);
    }
}
