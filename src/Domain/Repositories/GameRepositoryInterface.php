<?php

namespace Mvreisg\GamebaseBackend\Domain\Repositories;

use Mvreisg\GamebaseBackend\Domain\Entities\Game;

/**
 * The Game repository interface.
 */
interface GameRepositoryInterface
{
    /**
     * Inserts a Game into the repository.
     * @param Game $game The Game object containing the data to be inserted into the repository.
     * @return Game The inserted Game object clone.
     */
    public function insert(Game $game): Game;

    /**
     * Updates a Game register in the Game repository.
     * @param Game $game The Game object containing the data to be updated into the repository.
     * @return bool Returns the success flag.
     */
    public function update(Game $game): bool;

    /**
     * Deletes a Game registed in the Game repository by the id.
     * @param int $id The respective id of the Game register that is wanted to be deleted.
     * @return bool Returns the success flag.
     */
    public function setIsActive(int $id, bool $isActive): bool;

    /**
     * Finds a Game register in the Game repository by its respective id and returns their Game object if it was found.
     * @param int $id The id of the Game register that is wanted to be found.
     * @return Game|null Returns the Game object if id is founded, else returns null.
     */
    public function findById(int $id): Game|null;

    /**
     * Finds all the Game registers in the repository.
     * @return array Returns all Games registers found in the Game repository in a list.
     */
    public function findAll(): array;

    /**
     * Checks if a register with the name already exists in the repository.
     * @param string $name The Game name.
     * @return bool Returns true if the register already exists, else false.
     */
    public function hasDuplicatedNames(string $name): bool;
}
