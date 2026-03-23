<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Entities\Exceptions;

class EntityException extends \DomainException
{
    public function __construct(string $message)
    {
        parent::__construct("Entity data exception: $message");
    }
}
