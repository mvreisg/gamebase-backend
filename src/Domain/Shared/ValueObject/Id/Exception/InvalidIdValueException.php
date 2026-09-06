<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Exception;

class InvalidIdValueException extends \Exception
{
    public function __construct(int $value)
    {
        parent::__construct("Invalid id value: " . $value);
    }
}
