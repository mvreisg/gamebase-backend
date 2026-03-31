<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Name\Exception;

class EmptyNameValueException extends \DomainException
{
    public function __construct()
    {
        parent::__construct("Empty name value.");
    }
}
