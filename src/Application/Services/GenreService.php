<?php

namespace Mvreisg\GamebaseBackend\Application\Services;

use Exception;
use Mvreisg\GamebaseBackend\Domain\Entities\Genre;
use Mvreisg\GamebaseBackend\Domain\Exceptions\EntityInvalidValueException;
use Mvreisg\GamebaseBackend\Domain\Repositories\GenreRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseDuplicatedEntryException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseFetchFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseStatementCreationFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseStatementExecutionFailureException;
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
    public function insert(mixed $name): Genre
    {
        $genre = new Genre();

        try {
            $genre->validateName($name);
            $genre->setName($name);
            $validatedName = $genre->getName();
            $hasDuplicatedNames = $this->repository->hasDuplicatedNames($validatedName);
            if ($hasDuplicatedNames) {
                throw new DatabaseDuplicatedEntryException('O nome do gênero a ser inserido já existe no repositório!');
            }
            $genre = $this->repository->insert($genre);
            return $genre;
        } catch (EntityInvalidValueException | DatabaseDuplicatedEntryException | DatabaseStatementCreationFailureException | DatabaseStatementExecutionFailureException | DatabaseFetchFailureException | PDOException $e) {
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
    public function update(mixed $id, mixed $name): bool
    {
        $genre = new Genre();

        try {
            $genre->validateId($id);
            $genre->validateName($name);
            $genre->setId($id);
            $genre->setName($name);
            $validatedName = $genre->getName();
            $hasDuplicatedNames = $this->repository->hasDuplicatedNames($validatedName);
            if ($hasDuplicatedNames) {
                throw new DatabaseDuplicatedEntryException('O nome do gênero a ser atualizado já existe no repositório!');
            }
            $wasItSuccessful = $this->repository->update($genre);
            return $wasItSuccessful;
        } catch (EntityInvalidValueException | DatabaseDuplicatedEntryException | DatabaseStatementCreationFailureException | DatabaseStatementExecutionFailureException | PDOException $e) {
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
    public function findById(mixed $id): Genre|null
    {
        $genre = new Genre();

        try {
            $genre->validateId($id);
            $genre->setId($id);
            $genre = $this->repository->findById($id);
            return $genre;
        } catch (EntityInvalidValueException | DatabaseStatementCreationFailureException | DatabaseStatementExecutionFailureException | PDOException $e) {
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
            $genres = $this->repository->findAll();
            return $genres;
        } catch (DatabaseStatementCreationFailureException | DatabaseStatementExecutionFailureException | PDOException $e) {
            throw $e;
        }
    }
}
