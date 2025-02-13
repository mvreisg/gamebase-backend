<?php

namespace Mvreisg\GamebaseBackend\Domain\Repositories;

use Mvreisg\GamebaseBackend\Domain\Entities\GamePlatform;

/**
 * Game Platform repository interface.
 */
interface GamePlatformRepositoryInterface
{
    /**
     * Inserts a new Game Platform and returns a copy of the object.
     * @param GamePlatform $gamePlatform The object to be inserted.
     * @return GamePlatform The copy of the inserted object.
     */
    public function insert(GamePlatform $gamePlatform): GamePlatform;

    /**
     * Updates an existing Game Platform, returning the success flag.
     * @param GamePlatform $gamePlatform The object to be updated.
     * @return bool The success flag.
     */
    public function update(GamePlatform $gamePlatform): bool;

    /**
     * Deletes an existing Game Platform, returning the success flag.
     * @param GamePlatform $gamePlatform The object containing the data necessary for the deletion.
     * @return bool The success flag.
     */
    public function delete(GamePlatform $gamePlatform): bool;

    /**
     * Deletes all Game Platforms with the Game id.
     * @param GamePlatform $gamePlatform The object containing the Game id.
     * @return bool The success flag.
     */
    public function deleteAllByGameId(GamePlatform $gamePlatform): bool;

    /**
     * Find an Game Platform by its id.
     * @param int $id The game Platform id.
     * @return GamePlatform The found Game Platform, else null.
     */
    public function findById(int $id): GamePlatform|null;

    /**
     * Find all Game Platforms.
     * @return array A list containing the Game Platforms.
     */
    public function findAll(): array;

    /**
     * Returns all Game Platforms with the Game id.
     * @param int $gameId The Game id.
     * @return array A list containing the Game Platforms.
     */
    public function findAllGamePlatformsByGameId(int $gameId): array;

    /**
     * Returns all Game Platforms and Game data intersected by Game id.
     * @return array A list containing the Game Platforms.
     */
    public function innerJoinBetweenGameAndGamePlatformByGameId(): array;
}
