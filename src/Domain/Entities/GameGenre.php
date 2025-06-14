<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Entities;

use Mvreisg\GamebaseBackend\Domain\Exceptions\EntityInvalidValueException;

class GameGenre
{
    private int $id;
    private int $genreId;
    private int $gameId;

    public function __construct(int $id = 0, int $genreId = 0, int $gameId = 0)
    {
        $this->id = $id;
        $this->genreId = $genreId;
        $this->gameId = $gameId;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getGenreId(): int
    {
        return $this->genreId;
    }

    public function setGenreId(int $genreId): void
    {
        $this->genreId = $genreId;
    }

    public function getGameId(): int
    {
        return $this->gameId;
    }

    public function setGameId(int $gameId): void
    {
        $this->gameId = $gameId;
    }

    public function validateId(): void
    {
        if ($this->id <= 0) {
            throw new EntityInvalidValueException('O id deve ser maior que zero!');
        }
    }

    public function validateGenreId(): void
    {
        if ($this->genreId <= 0) {
            throw new EntityInvalidValueException('O genreId deve ser maior que zero!');
        }
    }

    public function validateGameId(): void
    {
        if ($this->gameId <= 0) {
            throw new EntityInvalidValueException('O gameId deve ser maior que zero!');
        }
    }
}
