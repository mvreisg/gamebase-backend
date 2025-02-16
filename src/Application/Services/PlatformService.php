<?php

namespace Mvreisg\GamebaseBackend\Application\Services;

use Exception;
use Mvreisg\GamebaseBackend\Domain\Entities\Platform;
use Mvreisg\GamebaseBackend\Domain\Exceptions\EntityInvalidValueException;
use Mvreisg\GamebaseBackend\Domain\Repositories\PlatformRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseDuplicatedEntryException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseFetchFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseStatementCreationFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseStatementExecutionFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseTransactionCreationFailureException;
use PDOException;

/**
 * Platform service class.
 */
class PlatformService
{
    /**
     * @var PlatformRepositoryInterface $repository The repository to be used by this service.
     */
    private PlatformRepositoryInterface $repository;

    /**
     * Platform service class constructor.
     * @param PlatformRepositoryInterface $repository The repository to be used by this service.
     */
    public function __construct(PlatformRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Inserts a new Platform
     * @param string $name The name of the Platform.
     * @return Platform A copy of the inserted Platform.
     * @throws DatabaseDuplicatedEntryException Throwed in case of database error.
     * @throws EntityInvalidValueException Throwed in case of entity error.
     * @throws PDOException Throwed in case of PDO error.
     * @throws Exception Throwed in case of error.
     */
    public function insert(mixed $name): Platform
    {
        $platform = new Platform();
        $platform->setName($name);

        try {
            $platform->validateName();
            $validatedName = $platform->getName();
            $hasDuplicatedNames = $this->repository->hasDuplicatedNames($validatedName);
            if ($hasDuplicatedNames) {
                throw new DatabaseDuplicatedEntryException('O nome da plataforma a ser inserida já existe no banco de dados!');
            }
            $platform = $this->repository->insert($platform);
            return $platform;
        } catch (DatabaseDuplicatedEntryException | EntityInvalidValueException | DatabaseTransactionCreationFailureException | DatabaseStatementCreationFailureException | DatabaseStatementExecutionFailureException | DatabaseFetchFailureException | PDOException $e) {
            throw $e;
        }
    }

    /**
     * Updates a Platform
     * @param int $id The id of the Platform.
     * @param string $name The name of the Platform.
     * @return Platform A copy of the inserted Platform.
     * @throws DatabaseDuplicatedEntryException Throwed in case of database error.
     * @throws EntityInvalidValueException Throwed in case of entity error.
     * @throws PDOException Throwed in case of PDO error.
     * @throws Exception Throwed in case of error.
     */
    public function update(int $id, string $name): bool
    {
        $platform = new Platform();
        $platform->setId($id);
        $platform->setName($name);

        try {
            $platform->validateId();
            $platform->validateName();
            $validatedName = $platform->getName();
            $hasDuplicatedNames = $this->repository->hasDuplicatedNames($validatedName);
            if ($hasDuplicatedNames) {
                throw new DatabaseDuplicatedEntryException('O nome da plataforma a ser editada já existe no banco de dados!');
            }
            $wasItSuccessful = $this->repository->update($platform);
            return $wasItSuccessful;
        } catch (DatabaseDuplicatedEntryException | EntityInvalidValueException | PDOException | Exception $e) {
            throw $e;
        }
    }

    /**
     * Finds a Platform by its id.
     * @param int $id The id of the Platform.
     * @return Platform|null Returns the Platform if found, else return null.
     * @throws PDOException Throwed in case of PDO error.
     * @throws Exception Throwed in case of error.
     */
    public function findById(int $id): Platform|null
    {
        try {
            $platform = $this->repository->findById($id);
            return $platform;
        } catch (PDOException | Exception $e) {
            throw $e;
        }
    }

    /**
     * Finds all Platforms.
     * @return array Returns the Platform list.
     * @throws PDOException Throwed in case of PDO error.
     * @throws Exception Throwed in case of error.
     */
    public function findAll(): array
    {
        try {
            $platforms = $this->repository->findAll();
            return $platforms;
        } catch (PDOException | Exception $e) {
            throw $e;
        }
    }
}
