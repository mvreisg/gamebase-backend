<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Exception;

class InvalidIdValueException extends \DomainException
{
    public function __construct(int $value)
    {
        parent::__construct("Invalid ID value: " . $value);
    }
}
