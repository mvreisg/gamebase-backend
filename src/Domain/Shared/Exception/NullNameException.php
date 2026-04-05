<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Shared\Exception;

class NullNameException extends \Exception
{
    public function __construct(string $className)
    {
        parent::__construct(
            "The name of the {$className} is null."
        );
    }
}
