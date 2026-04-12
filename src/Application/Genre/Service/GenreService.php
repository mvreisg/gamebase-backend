<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Genre\Service;

use Mvreisg\GamebaseBackend\Application\Authorization\UseCase\CheckAuthorizationUseCase;
use Mvreisg\GamebaseBackend\Domain\Authorization\Permission\PermissionType;
use Mvreisg\GamebaseBackend\Domain\Authorization\Sector\SectorType;
use Mvreisg\GamebaseBackend\Domain\Genre\Entity\Collection\GenreCollection;
use Mvreisg\GamebaseBackend\Domain\Genre\Entity\Genre;
use Mvreisg\GamebaseBackend\Domain\Genre\Repository\GenreRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Genre\Service\GenreDomainService;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;

class GenreService
{
    private CheckAuthorizationUseCase $checkAuthorizationUseCase;
    private GenreDomainService $genreDomainService;
    private GenreRepositoryInterface $repository;

    public function __construct(
        CheckAuthorizationUseCase $checkAuthorizationUseCase,
        GenreDomainService $genreDomainService,
        GenreRepositoryInterface $repository
    ) {
        $this->checkAuthorizationUseCase = $checkAuthorizationUseCase;
        $this->genreDomainService = $genreDomainService;
        $this->repository = $repository;
    }

    public function insert(Genre $genre, string $token): Genre
    {
        try {
            $this->checkAuthorizationUseCase->execute(
                $token,
                SectorType::Genre,
                PermissionType::Create
            );

            $this->genreDomainService->ensureNameIsUnique(
                null,
                $genre->getName()
            );

            $insertedGenre = $this->repository->insert($genre);

            return $insertedGenre;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function update(Genre $genre, string $token): bool
    {
        try {
            $this->checkAuthorizationUseCase->execute(
                $token,
                SectorType::Genre,
                PermissionType::Update
            );

            $this->genreDomainService->ensureGenreExists(
                $genre->getId()
            );

            $this->genreDomainService->ensureNameIsUnique(
                $genre->getId(),
                $genre->getName()
            );

            $wasUpdated = $this->repository->update($genre);

            return $wasUpdated;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function setIsActive(Id $id, bool $isActive, string $token): bool
    {
        try {
            $this->checkAuthorizationUseCase->execute(
                $token,
                SectorType::Genre,
                PermissionType::Activate
            );

            $this->genreDomainService->ensureGenreExists(
                $id
            );

            $wasUpdated = $this->repository->setIsActive(
                $id,
                $isActive
            );

            return $wasUpdated;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findById(Id $id, string $token): ?Genre
    {
        try {
            $this->checkAuthorizationUseCase->execute(
                $token,
                SectorType::Genre,
                PermissionType::List
            );

            $fetchedGenreEntity = $this->repository->findById(
                $id
            );

            return $fetchedGenreEntity;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findAll(string $token): ?GenreCollection
    {
        try {
            $this->checkAuthorizationUseCase->execute(
                $token,
                SectorType::Genre,
                PermissionType::List
            );

            return $this->repository->findAll();
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
