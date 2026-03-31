<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Name\Exception;

class InvalidNameValueException extends \DomainException
{
    public function __construct(string $value)
    {
        parent::__construct("Invalid name value: " . $value);
    }
}
