<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Authorization\Exception;

class UnauthorizedException extends \Exception
{
    public function __construct()
    {
        parent::__construct("Unauthorized.");
    }
}
