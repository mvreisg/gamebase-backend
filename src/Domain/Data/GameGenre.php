<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Data;

class GameGenre
{
    private ?Id $id;
    private Id $gameId;
    private Id $genreId;

    public function __construct(?Id $id = null, Id $gameId, Id $genreId)
    {
        $this->id = $id;
        $this->gameId = $gameId;
        $this->genreId = $genreId;
    }

    public function getIdValue(): int
    {
        if ($this->id === null) {
            throw new \InvalidArgumentException(
                "The id is null."
            );
        }
        return $this->id->getValue();
    }

    public function getGameIdValue(): int
    {
        if ($this->gameId === null) {
            throw new \InvalidArgumentException(
                "The gameId is null."
            );
        }
        return $this->gameId->getValue();
    }

    public function getGenreIdValue(): int
    {
        if ($this->genreId === null) {
            throw new \InvalidArgumentException(
                "The genreId is null."
            );
        }
        return $this->genreId->getValue();
    }
}
