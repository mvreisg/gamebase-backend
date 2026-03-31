<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Sector\ValueObject\Exception;

class EmptySectorValueValueException extends \DomainException
{
    public function __construct()
    {
        parent::__construct("Empty SectorValue value.");
    }
}
