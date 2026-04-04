<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Shared\Exception;

use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Name\Name;

class DuplicatedNameException extends \Exception
{
    public function __construct(Name $name)
    {
        parent::__construct(
            "The name '{$name->getValue()}' is already in use."
        );
    }
}
