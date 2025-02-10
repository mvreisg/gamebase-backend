<?php

namespace Mvreisg\GamebaseBackend\Domain\Repositories;

use Mvreisg\GamebaseBackend\Domain\Entities\GameGenre;

/**
 * Game Genre repository interface.
 */
interface GameGenreRepositoryInterface
{
    /**
     * Inserts a new Game Genre entity into the repository.
     * @param GameGenre $gameGenre The eneity to be inserted.
     * @return GameGenre a copy of the inserted entity.
     */
    public function insert(GameGenre $gameGenre): GameGenre;

    /**
     * Updates an existing register of a Game Genre entity in the repository.
     * @param GameGenre $gameGenre The data to be updated.
     * @return bool The success flag.
     */
    public function update(GameGenre $gameGenre): bool;

    /**
     * Deletes an existing register of a Game Genre entity in the repository.
     * @param GameGenre $gameGenre The data to be deleted.
     * @return bool The success flag.
     */
    public function delete(GameGenre $gameGenre): bool;

    /**
     * Deletes all registers of Game Genre entity with the respective Game id binded to it.
     * @param GameGenre $gameGenre The data containing the Game id.
     * @return bool The success flag.
     */
    public function deleteAllByGameId(GameGenre $gameGenre): bool;

    /**
     * Finds a Game Genre register by its id.
     * @param int $id The id to find.
     * @return GameGenre the found Game Genre.
     */
    public function findById(int $id): GameGenre|null;

    /**
     * Finds all Game Genre registers
     * @return array A list of all the Game Genres.
     */
    public function findAll(): array;

    /**
     * Finds all the Game Genres entities that contains the respective Game id.
     * @param int $gameId The Game id.
     * @return array A list containing the Game Genre entities.
     */
    public function findAllGameGenresByGameId(int $gameId): array;

    /**
     * Makes a inner join between Game and Game Genre, returuning all registers with the Game id.
     * @return array A list containing the game Genre entities.
     */
    public function innerJoinBetweenGameAndGameGenreByGameId(): array;
}
