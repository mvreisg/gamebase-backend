<?php
namespace Mvreisg\GamebaseBackend\Domain\Entities;

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
}
