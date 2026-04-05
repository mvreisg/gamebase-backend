<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\GamePlatform\Exception;

use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;

class GamePlatformNotFoundException extends \Exception
{
    public function __construct(Id $id)
    {
        parent::__construct(
            "The game platform with id '{$id->getValue()}' was not found."
        );
    }
}
