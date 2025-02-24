<?php

namespace Mvreisg\GamebaseBackend\Domain\Repositories;

use Mvreisg\GamebaseBackend\Domain\Entities\Genre;

/**
 * The Genre repository interface.
 */
interface GenreRepositoryInterface
{
    /**
     * Inserts a new Genre into the repository
     * @param Genre $genre The Genre to be inserted.
     * @return Genre A copy of the Genre inserted.
     */
    public function insert(Genre $genre): Genre;

    /**
     * Updates a Genre already created in the repository.
     * @param Genre $genre The genre data to update.
     * @return bool The success flag.
     */
    public function update(Genre $genre): bool;

    public function setIsActive(int $id, bool $isActive): bool;

    /**
     * Finds a Genre in the repository by its id.
     * @param int $id The Genre id.
     * @return Genre|null Returns the Genre if it finds, else returns null.
     */
    public function findById(int $id): Genre|null;

    /**
     * Find all the Genre registers in the repository.
     * @return array A list of genres.
     */
    public function findAll(): array;

    /**
     * Checks if the name passed already exists in the repository.
     * @param string $name The name to check.
     * @return bool True if already exists, else false.
     */
    public function hasDuplicatedNames(string $name): bool;
}
