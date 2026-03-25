<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Entities;

use Mvreisg\GamebaseBackend\Domain\Entities\Exceptions\EntityException;

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
            throw new EntityException(
                "The id is null."
            );
        }
        return $this->id;
    }

    public function getGameId(): Id
    {
        if ($this->gameId === null) {
            throw new EntityException(
                "The gameId is null."
            );
        }
        return $this->gameId;
    }

    public function getGenreId(): Id
    {
        if ($this->genreId === null) {
            throw new EntityException(
                "The genreId is null."
            );
        }
        return $this->genreId;
    }
}
