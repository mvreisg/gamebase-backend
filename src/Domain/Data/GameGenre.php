<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Data;

use Mvreisg\GamebaseBackend\Domain\Data\Exceptions\DataException;

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

    public function getIdValue(): int
    {
        if ($this->id === null) {
            throw new DataException(
                "The id is null."
            );
        }
        return $this->id->getValue();
    }

    public function getGameIdValue(): int
    {
        if ($this->gameId === null) {
            throw new DataException(
                "The gameId is null."
            );
        }
        return $this->gameId->getValue();
    }

    public function getGenreIdValue(): int
    {
        if ($this->genreId === null) {
            throw new DataException(
                "The genreId is null."
            );
        }
        return $this->genreId->getValue();
    }
}
