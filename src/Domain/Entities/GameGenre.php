<?php

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

    public function getId()
    {
        return $this->id;
    }

    public function setId(int $id)
    {
        $this->id = $id;
    }

    public function getGenreId()
    {
        return $this->genreId;
    }

    public function setGenreId(int $genreId)
    {
        $this->genreId = $genreId;
    }

    public function getGameId()
    {
        return $this->gameId;
    }

    public function setGameId(int $gameId)
    {
        $this->gameId = $gameId;
    }

    public function validateId(mixed $id)
    {
        if ($id === null) {
            throw new EntityInvalidValueException('O id é null!');
        }

        if (is_numeric($id) === false) {
            throw new EntityInvalidValueException('O id não é um número!');
        }

        if (is_string($id)) {
            throw new EntityInvalidValueException('O id é uma string!');
        }

        if (is_bool($id)) {
            throw new EntityInvalidValueException('O id é um valor booleano!');
        }

        if ($id < 1) {
            throw new EntityInvalidValueException('O id ' . $id . ' deve ser maior que 0.');
        }
    }

    public function validateGenreId(mixed $genreId)
    {
        if ($genreId === null) {
            throw new EntityInvalidValueException('O genreId é null!');
        }

        if (is_numeric($genreId) === false) {
            throw new EntityInvalidValueException('O genreId não é um número!');
        }

        if (is_string($genreId)) {
            throw new EntityInvalidValueException('O genreId é uma string!');
        }

        if (is_bool($genreId)) {
            throw new EntityInvalidValueException('O genreId é um valor booleano!');
        }

        if ($genreId < 1) {
            throw new EntityInvalidValueException('O genreId ' . $genreId . ' deve ser maior que 0.');
        }
    }

    public function validateGameId(mixed $gameId)
    {
        if ($gameId === null) {
            throw new EntityInvalidValueException('O gameId é null!');
        }

        if (is_numeric($gameId) === false) {
            throw new EntityInvalidValueException('O gameId não é um número!');
        }

        if (is_string($gameId)) {
            throw new EntityInvalidValueException('O gameId é uma string!');
        }

        if (is_bool($gameId)) {
            throw new EntityInvalidValueException('O gameId é um valor booleano!');
        }

        if ($gameId < 1) {
            throw new EntityInvalidValueException('O gameId ' . $gameId . ' deve ser maior que 0.');
        }
    }
}
