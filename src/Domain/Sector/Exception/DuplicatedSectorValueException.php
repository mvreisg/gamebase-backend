<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Sector\Exception;

use Mvreisg\GamebaseBackend\Domain\Sector\ValueObject\SectorValue\SectorValue;

class DuplicatedSectorValueException extends \Exception
{
    public function __construct(
        SectorValue $value
    ) {
        parent::__construct(
            "The sector value '{$value->getValue()->value}' is duplicated."
        );
    }
}
