<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Entities\GameGenre;

use Mvreisg\GamebaseBackend\Domain\Entities\GameGenre\Exceptions\GameGenreInvalidGameIdException;
use Mvreisg\GamebaseBackend\Domain\Entities\GameGenre\Exceptions\GameGenreInvalidGenreIdException;
use Mvreisg\GamebaseBackend\Domain\Entities\GameGenre\Exceptions\GameGenreInvalidIdException;

class GameGenre
{
    private ?int $id;
    private int $gameId;
    private int $genreId;

    public function __construct(?int $id = null, int $gameId = 0, int $genreId = 0)
    {
        $this->id = $id;
        $this->gameId = $gameId;
        $this->genreId = $genreId;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getGameId(): int
    {
        return $this->gameId;
    }

    public function setGameId(int $gameId): void
    {
        $this->gameId = $gameId;
    }

    public function getGenreId(): int
    {
        return $this->genreId;
    }

    public function setGenreId(int $genreId): void
    {
        $this->genreId = $genreId;
    }

    public function validateId(): void
    {
        if ($this->id <= 0) {
            throw new GameGenreInvalidIdException(
                "The id must be greater than zero!"
            );
        }
    }

    public function validateGameId(): void
    {
        if ($this->gameId <= 0) {
            throw new GameGenreInvalidGameIdException(
                "The game id must be greater than zero!"
            );
        }
    }

    public function validateGenreId(): void
    {
        if ($this->genreId <= 0) {
            throw new GameGenreInvalidGenreIdException(
                "The genre id must be greater than zero!"
            );
        }
    }
}
