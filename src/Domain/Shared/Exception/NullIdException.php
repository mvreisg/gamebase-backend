<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Shared\Exception;

class NullIdException extends \DomainException
{
    public function __construct(string $className)
    {
        parent::__construct("The id of the {$className} is null.");
    }
}
