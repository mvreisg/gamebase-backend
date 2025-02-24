<?php

namespace Mvreisg\GamebaseBackend\Domain\Repositories;

use Mvreisg\GamebaseBackend\Domain\Entities\Platform;

/**
 * Platform repository interface.
 */
interface PlatformRepositoryInterface
{
    /**
     * Inserts a new Platform into the repository.
     * @param Platform $platform The Platform to be inserted.
     * @return Platform A copy of the inserted Platform.
     */
    public function insert(Platform $platform): Platform;

    /**
     * Updates an existing Platform in the repository.
     * @param Platform $platform The Platform data to be updated.
     * @return bool The success flag.
     */
    public function update(Platform $platform): bool;

    /**
     * Deletes an existing Platform in the repository.
     * @param int $id The id of the register to be deleted.
     * @return bool The success flag.
     */
    public function setIsActive(int $id, bool $isActive): bool;

    /**
     * Finds a Platform in the repository by its id.
     * @param int $id The id to search for.
     * @return Platform|null Returns the Platform if found, else returns null.
     */
    public function findById(int $id): Platform|null;

    /**
     * Finds all Platforms in the repository.
     * @return array A list containing all the founded repositories.
     */
    public function findAll(): array;

    /**
     * Check if the name already exists in the repository.
     * @param string $name The name to be searched.
     * @return bool True if it already exists, else returns false.
     */
    public function hasDuplicatedNames(string $name): bool;
}
