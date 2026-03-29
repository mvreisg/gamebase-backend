<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Services\Genre;

use Mvreisg\GamebaseBackend\Application\Services\Authentication\AuthenticationService;
use Mvreisg\GamebaseBackend\Application\Services\Authorization\AuthorizationService;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\Data\Encoded\EncodedAuthenticationToken;
use Mvreisg\GamebaseBackend\Domain\Authorization\Types\Permission\PermissionTypes;
use Mvreisg\GamebaseBackend\Domain\Authorization\Types\Sector\SectorTypes;
use Mvreisg\GamebaseBackend\Domain\Entities\Genre;
use Mvreisg\GamebaseBackend\Domain\Entities\GenreCollection;
use Mvreisg\GamebaseBackend\Domain\Entities\Id;
use Mvreisg\GamebaseBackend\Domain\Repositories\Interface\GenreRepositoryInterface;

class GenreService
{
    private GenreRepositoryInterface $repository;
    private AuthenticationService $authenticationService;
    private AuthorizationService $authorizationService;

    public function __construct(
        GenreRepositoryInterface $repository,
        AuthenticationService $authenticationService,
        AuthorizationService $authorizationService
    ) {
        $this->repository = $repository;
        $this->authenticationService = $authenticationService;
        $this->authorizationService = $authorizationService;
    }

    public function insert(Genre $genre, EncodedAuthenticationToken $token): Genre
    {
        try {
            $decodedToken = $this->authenticationService->validate(
                $token
            );

            $this->authorizationService->check(
                $decodedToken->getUserId(),
                SectorTypes::Genre,
                PermissionTypes::Create
            );

            $this->repository->checkDuplicatedNames(
                $genre->getName()
            );

            $insertedGenre = $this->repository->insert($genre);

            return $insertedGenre;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function update(Genre $genre, EncodedAuthenticationToken $token): bool
    {
        try {
            $decodedToken = $this->authenticationService->validate(
                $token
            );

            $this->authorizationService->check(
                $decodedToken->getUserId(),
                SectorTypes::Genre,
                PermissionTypes::Update
            );

            $this->repository->checkIfExists(
                $genre->getId()
            );

            $this->repository->checkDuplicatedNames(
                $genre->getName()
            );

            $wasUpdated = $this->repository->update($genre);

            return $wasUpdated;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function setIsActive(Id $id, bool $isActive, EncodedAuthenticationToken $token): bool
    {
        try {
            $decodedToken = $this->authenticationService->validate(
                $token
            );

            $this->authorizationService->check(
                $decodedToken->getUserId(),
                SectorTypes::Genre,
                PermissionTypes::Activate
            );

            $this->repository->checkIfExists($id);

            $wasUpdated = $this->repository->setIsActive(
                $id,
                $isActive
            );

            return $wasUpdated;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findById(Id $id, EncodedAuthenticationToken $token): Genre
    {
        try {
            $decodedToken = $this->authenticationService->validate(
                $token
            );

            $this->authorizationService->check(
                $decodedToken->getUserId(),
                SectorTypes::Genre,
                PermissionTypes::List
            );

            $fetchedGenreEntity = $this->repository->findById(
                $id
            );

            return $fetchedGenreEntity;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findAll(EncodedAuthenticationToken $token): GenreCollection
    {
        try {
            $decodedToken = $this->authenticationService->validate(
                $token
            );

            $this->authorizationService->check(
                $decodedToken->getUserId(),
                SectorTypes::Genre,
                PermissionTypes::List
            );

            return $this->repository->findAll();
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
