<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Services\Genre;

use Mvreisg\GamebaseBackend\Application\Services\Genre\Exceptions\GenreServiceDuplicatedNameException;
use Mvreisg\GamebaseBackend\Application\Services\Genre\Exceptions\GenreServiceInvalidIdException;
use Mvreisg\GamebaseBackend\Application\Services\Genre\Exceptions\GenreServiceInvalidNameException;
use Mvreisg\GamebaseBackend\Application\Services\Genre\Exceptions\GenreServiceUnexistantGenreException;
use Mvreisg\GamebaseBackend\Domain\Entities\Genre\Exceptions\GenreInvalidIdException;
use Mvreisg\GamebaseBackend\Domain\Entities\Genre\Exceptions\GenreInvalidNameException;
use Mvreisg\GamebaseBackend\Domain\Entities\Genre\Genre;
use Mvreisg\GamebaseBackend\Domain\Repositories\Exceptions\RepositoryDuplicatedNameException;
use Mvreisg\GamebaseBackend\Domain\Repositories\Exceptions\RepositoryStatementCreationFailureException;
use Mvreisg\GamebaseBackend\Domain\Repositories\Exceptions\RepositoryStatementExecutionFailureException;
use Mvreisg\GamebaseBackend\Domain\Repositories\Exceptions\RepositoryUnexistantRegisterException;
use Mvreisg\GamebaseBackend\Domain\Repositories\GenreRepositoryInterface;

class GenreService
{
    private GenreRepositoryInterface $repository;

    public function __construct(GenreRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function insert(string $name, bool $isActive): Genre
    {
        try {
            $genre = new Genre(
                null,
                $name,
                $isActive
            );

            $genre->validateName();

            $validatedName = $genre->getName();

            $this->repository->checkDuplicatedNames($validatedName);

            $insertedGenre = $this->repository->insert($genre);

            return $insertedGenre;
        } catch (GenreInvalidNameException $e) {
            throw new GenreServiceInvalidNameException(
                "Genre service error: {$e->getMessage()}",
                $e
            );
        } catch (RepositoryDuplicatedNameException $e) {
            throw new GenreServiceDuplicatedNameException(
                "Genre service error: {$e->getMessage()}",
                $e
            );
        } catch (
            RepositoryStatementCreationFailureException |
            RepositoryStatementExecutionFailureException |
            \Throwable
            $e
        ) {
            throw $e;
        }
    }

    public function update(int $id, string $name, bool $isActive): bool
    {
        try {
            $genre = new Genre(
                $id,
                $name,
                $isActive
            );

            $genre->validateId();

            $validatedId = $genre->getId();

            $this->repository->checkIfExists($validatedId);

            $genre->validateName();

            $validatedName = $genre->getName();

            $this->repository->checkDuplicatedNames($validatedName);

            $wasUpdated = $this->repository->update($genre);

            return $wasUpdated;
        } catch (GenreInvalidIdException $e) {
            throw new GenreServiceInvalidIdException(
                "Genre service error: {$e->getMessage()}",
                $e
            );
        } catch (GenreInvalidNameException $e) {
            throw new GenreServiceInvalidNameException(
                "Genre service error: {$e->getMessage()}",
                $e
            );
        } catch (RepositoryDuplicatedNameException $e) {
            throw new GenreServiceDuplicatedNameException(
                "Genre service error: {$e->getMessage()}",
                $e
            );
        } catch (RepositoryUnexistantRegisterException $e) {
            throw new GenreServiceUnexistantGenreException(
                "Genre service error: {$e->getMessage()}",
                $e
            );
        } catch (
            RepositoryStatementCreationFailureException |
            RepositoryStatementExecutionFailureException |
            \Throwable
            $e
        ) {
            throw $e;
        }
    }

    public function setIsActive(int $id, bool $isActive): bool
    {
        try {
            $genre = new Genre(
                $id,
                null,
                $isActive
            );

            $genre->validateId();

            $validatedId = $genre->getId();

            $this->repository->checkIfExists($validatedId);

            $validatedIsActive = $genre->getIsActive();

            $wasUpdated = $this->repository->setIsActive(
                $validatedId,
                $validatedIsActive
            );

            return $wasUpdated;
        } catch (GenreInvalidIdException $e) {
            throw new GenreServiceInvalidIdException(
                "Genre service error: {$e->getMessage()}",
                $e
            );
        } catch (RepositoryUnexistantRegisterException $e) {
            throw new GenreServiceUnexistantGenreException(
                "Genre service error: {$e->getMessage()}",
                $e
            );
        } catch (
            RepositoryStatementCreationFailureException |
            RepositoryStatementExecutionFailureException |
            \Throwable
            $e
        ) {
            throw $e;
        }
    }

    public function findById(int $id): Genre
    {
        try {
            $genre = new Genre(
                $id
            );

            $genre->validateId();

            $validatedId = $genre->getId();

            $fetchedGenreEntity = $this->repository->findById(
                $validatedId
            );

            return $fetchedGenreEntity;
        } catch (GenreInvalidIdException $e) {
            throw new GenreServiceInvalidIdException(
                "Genre service error: {$e->getMessage()}",
                $e
            );
        } catch (RepositoryUnexistantRegisterException $e) {
            throw new GenreServiceUnexistantGenreException(
                "Genre service error: {$e->getMessage()}",
                $e
            );
        } catch (
            RepositoryStatementCreationFailureException |
            RepositoryStatementExecutionFailureException |
            \Throwable
            $e
        ) {
            throw $e;
        }
    }

    public function findAll(): array
    {
        try {
            return $this->repository->findAll();
        } catch (
            RepositoryStatementCreationFailureException |
            RepositoryStatementExecutionFailureException |
            \Throwable
            $e
        ) {
            throw $e;
        }
    }
}
