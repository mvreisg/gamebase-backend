<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\GameGenre\Entity;

use Mvreisg\GamebaseBackend\Domain\Shared\Exception\NullIdException;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;

class GameGenre
{
    private ?Id $id;
    private Id $gameId;
    private Id $genreId;

    public function __construct(Id $gameId, Id $genreId)
    {
        $this->id = null;
        $this->gameId = $gameId;
        $this->genreId = $genreId;
    }

    public function setId(Id $id): void
    {
        $this->id = $id;
    }

    public function getId(): Id
    {
        if ($this->id === null) {
            throw new NullIdException(
                GameGenre::class
            );
        }
        return $this->id;
    }

    public function getGameId(): Id
    {
        return $this->gameId;
    }

    public function getGenreId(): Id
    {
        return $this->genreId;
    }
}
