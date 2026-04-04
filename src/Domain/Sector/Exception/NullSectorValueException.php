<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Sector\Exception;

class NullSectorValueException extends \Exception
{
    public function __construct()
    {
        parent::__construct(
            "The sector value of the sector is null."
        );
    }
}
