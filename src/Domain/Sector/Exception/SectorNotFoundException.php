<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Sector\Exception;

use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;

class SectorNotFoundException extends \Exception
{
    public function __construct(Id $id)
    {
        parent::__construct(
            "The sector with id '{$id->getValue()}' was not found."
        );
    }
}
