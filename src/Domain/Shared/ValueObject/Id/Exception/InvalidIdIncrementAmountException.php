<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Exception;

class InvalidIdIncrementAmountException extends \Exception
{
    public function __construct(int $amount)
    {
        parent::__construct("Invalid ID increment amount: " . $amount);
    }
}
