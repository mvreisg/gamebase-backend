<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Game\Exception;

use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;

class GameNotFoundException extends \Exception
{
    public function __construct(Id $id)
    {
        parent::__construct(
            "The game with id '{$id->getValue()}' was not found."
        );
    }
}
