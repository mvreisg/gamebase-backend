<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Data\Exceptions;

class DataException extends \DomainException
{
    public function __construct(string $message)
    {
        parent::__construct("Domain data exception: $message");
    }
}
