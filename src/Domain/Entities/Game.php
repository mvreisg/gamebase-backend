<?php

namespace Mvreisg\GamebaseBackend\Domain\Entities;

use Mvreisg\GamebaseBackend\Domain\Exceptions\EntityInvalidValueException;

/**
 * Game entity class.
 */
class Game
{
    /**
     * @var int $id The Game id.
     */
    private int $id;

    /**
     * @var string $name The Game name.
     */
    private string $name;

    /**
     * @var list<Genre> $genres The Game Genres objects list.
     */
    private array $genres;

    /**
     * @var list<Platform> $platforms The Game Platforms objects list.
     */
    private array $platforms;

    /**
     * The Game class constructor.
     * @param int $id The Game id.
     * @param string $name The Game name;
     * @param list<Genre> $genres The Game Genres objects list.
     * @param list<Platform> $platforms The Game Genres objects list.
     * @return void
     */
    public function __construct(int $id = 0, string $name = '', array $genres = [], array $platforms = [])
    {
        $this->id = $id;
        $this->name = $name;
        $this->genres = $genres;
        $this->platforms = $platforms;
    }

    /**
     * The Game id getter.
     * @return int The Game id.
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * The Game id setter.
     * @param int $id The Game id.
     * @return void
     */
    public function setId(int $id)
    {
        $this->id = $id;
    }

    /**
     * The Game name getter.
     * @return string The Game name.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * The Game name setter.
     * @param string $name The Game name.
     * @return void
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * The Game Genres objects list getter.
     * @return list<Genre> The Game Genres objects list.
     */
    public function getGenres()
    {
        return $this->genres;
    }

    /**
     * The Game Genres objects list setter.
     * @param list<Genre> $genres The Game Genres objects list.
     * @return void
     */
    public function setGenres(array $genres)
    {
        $this->genres = $genres;
    }

    /**
     * The Game Platforms objects list getter.
     * @return list<Platform> The Game Platforms objects list.
     */
    public function getPlatforms()
    {
        return $this->platforms;
    }

    /**
     * The Game Platforms objects list setter.
     * @param list<Platform> $platforms The Game Platforms objects list.
     */
    public function setPlatforms(array $platforms)
    {
        $this->platforms = $platforms;
    }

    /**
     * Method to validate the name of the Game, throwing an exception if the name is invalid.
     * @throws EntityInvalidValueException Throwed if the name is invalid.
     */
    public function validateName()
    {
        if ($this->name === null) {
            throw new EntityInvalidValueException('O nome é nulo.');
        }

        $this->name = trim($this->name);

        if ($this->name === '') {
            throw new EntityInvalidValueException('O nome está vazio.');
        }
    }

    /**
     * Method to validate the id of the Game, throwing an exception if the id is invalid.
     * @throws EntityInvalidValueException Throwed if the id is invalid.
     */
    public function validateId()
    {
        if ($this->id < 1) {
            throw new EntityInvalidValueException('O id ' . $this->id . ' é menor que um.');
        }
    }
}
