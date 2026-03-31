<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Sector\ValueObject\Exception;

class InvalidSectorValueValueException extends \DomainException
{
    public function __construct(string $value)
    {
        parent::__construct("Invalid SectorValue value: " . $value);
    }
}
