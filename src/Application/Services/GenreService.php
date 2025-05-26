<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Services;

use Mvreisg\GamebaseBackend\Domain\Entities\Genre;
use Mvreisg\GamebaseBackend\Domain\Exceptions\EntityInvalidValueException;
use Mvreisg\GamebaseBackend\Domain\Repositories\GenreRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseDuplicatedEntryException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseFetchFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseStatementCreationFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseStatementExecutionFailureException;
use PDOException;

class GenreService
{
    private GenreRepositoryInterface $repository;

    public function __construct(GenreRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function insert(mixed $name, mixed $isActive): Genre
    {
        $genre = new Genre();

        try {
            $genre->setName($name);
            $genre->setIsActive($isActive);

            $genre->validateName();
            
            $validatedName = $genre->getName();
            $hasDuplicatedNames = $this->repository->hasDuplicatedNames($validatedName);
            if ($hasDuplicatedNames) {
                throw new DatabaseDuplicatedEntryException('O nome do gênero a ser inserido já existe no repositório!');
            }
            $genre = $this->repository->insert($genre);
            return $genre;
        } catch (
            EntityInvalidValueException |
            DatabaseDuplicatedEntryException |
            DatabaseStatementCreationFailureException |
            DatabaseStatementExecutionFailureException |
            DatabaseFetchFailureException |
            PDOException $e
        ) {
            throw $e;
        }
    }

    public function update(mixed $id, mixed $name, mixed $isActive): bool
    {
        $genre = new Genre();

        try {            
            $genre->setId($id);
            $genre->setName($name);
            $genre->setIsActive($isActive);

            $genre->validateId();
            $genre->validateName();
            /*
            $validatedName = $genre->getName();
            $hasDuplicatedNames = $this->repository->hasDuplicatedNames($validatedName);
            if ($hasDuplicatedNames) {
                throw new DatabaseDuplicatedEntryException(
                    'O nome do gênero a ser atualizado já existe no repositório!'
                );
            }
            */
            $wasItSuccessful = $this->repository->update($genre);
            return $wasItSuccessful;
        } catch (
            EntityInvalidValueException |
            DatabaseDuplicatedEntryException |
            DatabaseStatementCreationFailureException |
            DatabaseStatementExecutionFailureException |
            PDOException $e
        ) {
            throw $e;
        }
    }

    public function setIsActive(mixed $id, mixed $isActive): bool
    {
        $genre = new Genre();
        try {
            $genre->setId($id);
            $genre->setIsActive($isActive);

            $genre->validateId();
            
            $wasSuccessful = $this->repository->setIsActive($id, $isActive);
            return $wasSuccessful;
        } catch (
            EntityInvalidValueException |
            DatabaseStatementCreationFailureException |
            DatabaseStatementExecutionFailureException |
            PDOException $e
        ) {
            throw $e;
        }
    }

    public function findById(mixed $id): Genre|null
    {
        $genre = new Genre();

        try {            
            $genre->setId($id);

            $genre->validateId();

            $genre = $this->repository->findById($id);
            return $genre;
        } catch (
            EntityInvalidValueException |
            DatabaseStatementCreationFailureException |
            DatabaseStatementExecutionFailureException |
            PDOException $e
        ) {
            throw $e;
        }
    }

    public function findAll(): array
    {
        try {
            $allGenres = $this->repository->findAll();
            return $allGenres;
        } catch (
            DatabaseStatementCreationFailureException |
            DatabaseStatementExecutionFailureException |
            PDOException $e
        ) {
            throw $e;
        }
    }
}
