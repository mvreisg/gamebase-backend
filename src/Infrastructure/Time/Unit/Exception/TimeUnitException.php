<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Time\Unit\Exception;

class TimeUnitException extends \Exception
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
