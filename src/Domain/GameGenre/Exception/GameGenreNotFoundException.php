<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\GameGenre\Exception;

use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;

class GameGenreNotFoundException extends \Exception
{
    public function __construct(Id $id)
    {
        parent::__construct(
            "The game genre with id '{$id->getValue()}' was not found."
        );
    }
}
