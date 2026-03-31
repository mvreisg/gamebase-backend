<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Permission\ValueObject\Exception;

class InvalidPermissionValueValueException extends \DomainException
{
    public function __construct(string $value)
    {
        parent::__construct("Invalid PermissionValue value: " . $value);
    }
}
