<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Shared\Exception;

class NullIsActiveException extends \Exception
{
    public function __construct(string $className)
    {
        parent::__construct("The isActive of the {$className} is null.");
    }
}
