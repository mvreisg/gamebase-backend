<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\GameGenre\Entity;

use Mvreisg\GamebaseBackend\Domain\Game\Entity\Game;
use Mvreisg\GamebaseBackend\Domain\Genre\Entity\Genre;
use Mvreisg\GamebaseBackend\Domain\Shared\Exception\NullIdException;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;

class GameGenre
{
    private ?Id $id;
    private Game $game;
    private Genre $genre;

    public function __construct(
        Game $game,
        Genre $genre
    ) {
        $this->id = null;
        $this->game = $game;
        $this->genre = $genre;
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

    public function getGame(): Game
    {
        return $this->game;
    }

    public function getGenre(): Genre
    {
        return $this->genre;
    }
}
