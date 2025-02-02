<?php

namespace Mvreisg\GamebaseBackend\Application\Services;

use Exception;
use Mvreisg\GamebaseBackend\Domain\Entities\Genre;
use Mvreisg\GamebaseBackend\Domain\Exceptions\EntityInvalidValueException;
use Mvreisg\GamebaseBackend\Domain\Repositories\GenreRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseDuplicatedEntryException;
use PDOException;

/**
 * Genre service class.
 */
class GenreService
{
    /**
     * @var GenreRepositoryInterface $repository The repository to be used by this service.
     */
    private GenreRepositoryInterface $repository;

    /**
     * Genre service class repository.
     * @param GenreRepositoryInterface $repository The repository to be used by this service.
     * @return void
     */
    public function __construct(GenreRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Inserts a nem Genre into the repository.
     * @param string $name The name of the Genre.
     * @return Genre A copy of the created Genre object.
     * @throws DatabaseDuplicatedEntryException Throwed in case of database error.
     * @throws EntityInvalidValueException Throwed in case of entity error.
     * @throws PDOException Throwed in case of database connection error.
     * @throws Exception Throwed in case of error.
     */
    public function insert(string $name): Genre
    {
        $genre = new Genre();
        $genre->setName($name);

        try {
            $genre->validateName();
            $validatedName = $genre->getName();
            $hasDuplicatedNames = $this->repository->hasDuplicatedNames($validatedName);
            if ($hasDuplicatedNames) {
                throw new DatabaseDuplicatedEntryException('O nome do gênero a ser inserido já existe no banco de dados!');
            }
            $genre = $this->repository->insert($genre);
            return $genre;
        } catch (DatabaseDuplicatedEntryException | EntityInvalidValueException | PDOException | Exception $e) {
            throw $e;
        }
    }

    /**
     * Updates a Genre already registered in the repository.
     * @param int $id The id of the Genre.
     * @param string $name The name of the Genre.
     * @return Genre A copy of the created Genre object.
     * @throws DatabaseDuplicatedEntryException Throwed in case of database error.
     * @throws EntityInvalidValueException Throwed in case of entity error.
     * @throws PDOException Throwed in case of database connection error.
     * @throws Exception Throwed in case of error.
     */
    public function update(int $id, string $name): bool
    {
        $genre = new Genre();
        $genre->setId($id);
        $genre->setName($name);

        try {
            $genre->validateId();
            $genre->validateName();
            $validatedName = $genre->getName();
            $hasDuplicatedNames = $this->repository->hasDuplicatedNames($validatedName);
            if ($hasDuplicatedNames) {
                throw new DatabaseDuplicatedEntryException('O nome do gênero a ser editado já existe no banco de dados!');
            }
            $wasItSuccessful = $this->repository->update($genre);
            return $wasItSuccessful;
        } catch (DatabaseDuplicatedEntryException | EntityInvalidValueException | PDOException | Exception $e) {
            throw $e;
        }
    }

    /**
     * Finds a Genre already registered in the repository by its id.
     * @param int $id The id of the Genre.
     * @return Genre|null The Genre object if founded, else returns null.
     * @throws DatabaseDuplicatedEntryException Throwed in case of database error.
     * @throws PDOException Throwed in case of database connection error.
     * @throws Exception Throwed in case of error.
     */
    public function findById(int $id): Genre|null
    {
        try {
            $genre = $this->repository->findById($id);
            return $genre;
        } catch (DatabaseDuplicatedEntryException | PDOException | Exception $e) {
            throw $e;
        }
    }

    /**
     * Finda all the Genres in the repository.
     * @return array The list of Genres.
     * @throws PDOException Throwed in case of database error.
     * @throws Exception Throwed in case of error.
     */
    public function findAll(): array
    {
        try {
            $games = $this->repository->findAll();
            return $games;
        } catch (PDOException | Exception $e) {
            throw $e;
        }
    }
}
