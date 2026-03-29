<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Services\User;

use Mvreisg\GamebaseBackend\Application\Services\Authentication\AuthenticationService;
use Mvreisg\GamebaseBackend\Application\Services\Authorization\AuthorizationService;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\Data\Encoded\EncodedAuthenticationToken;
use Mvreisg\GamebaseBackend\Domain\Authorization\Types\Permission\PermissionTypes;
use Mvreisg\GamebaseBackend\Domain\Authorization\Types\Sector\SectorTypes;
use Mvreisg\GamebaseBackend\Domain\Encryption\Interface\EncryptionInterface;
use Mvreisg\GamebaseBackend\Domain\Entities\EncodedPassword;
use Mvreisg\GamebaseBackend\Domain\Entities\Id;
use Mvreisg\GamebaseBackend\Domain\Entities\User;
use Mvreisg\GamebaseBackend\Domain\Entities\UserCollection;
use Mvreisg\GamebaseBackend\Domain\Entities\Username;
use Mvreisg\GamebaseBackend\Domain\Repositories\Interface\UserRepositoryInterface;

class UserService
{
    private UserRepositoryInterface $repository;
    private AuthenticationService $authenticationService;
    private AuthorizationService $authorizationService;
    private EncryptionInterface $encrypter;

    public function __construct(
        UserRepositoryInterface $repository,
        AuthenticationService $authenticationService,
        AuthorizationService $authorizationService,
        EncryptionInterface $encrypter
    ) {
        $this->repository = $repository;
        $this->authenticationService = $authenticationService;
        $this->authorizationService = $authorizationService;
        $this->encrypter = $encrypter;
    }

    public function insert(User $new, EncodedAuthenticationToken $token): User
    {
        try {
            $decodedToken = $this->authenticationService->validate(
                $token
            );

            $this->authorizationService->check(
                $decodedToken->getUserId(),
                SectorTypes::User,
                PermissionTypes::Create
            );

            $this->repository->checkDuplicatedUsernames(
                $new->getUsername()
            );

            $encodedPassword = $this->encrypter->encrypt(
                $new->getPassword()->getValue()
            );

            $insertedUser = $this->repository->insert(
                new User(
                    $new->getUsername(),
                    EncodedPassword::make($encodedPassword),
                    $new->getIsActive()
                )
            );

            return $insertedUser;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function update(User $existant, EncodedAuthenticationToken $token): bool
    {
        try {
            $decodedToken = $this->authenticationService->validate(
                $token
            );

            $this->authorizationService->check(
                $decodedToken->getUserId(),
                SectorTypes::User,
                PermissionTypes::Update
            );

            $this->repository->checkIfExists(
                $existant->getId()
            );

            $this->repository->checkDuplicatedUsernames(
                $existant->getUsername()
            );

            $encodedPassword = $this->encrypter->encrypt(
                $existant->getPassword()->getValue()
            );

            $user = new User(
                $existant->getUsername(),
                EncodedPassword::make($encodedPassword),
                $existant->getIsActive()
            );
            $user->setId($existant->getId());
            $wasUpdated = $this->repository->update(
                $user
            );

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
                SectorTypes::User,
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

    public function findById(Id $id, EncodedAuthenticationToken $token): User
    {
        try {
            $decodedToken = $this->authenticationService->validate(
                $token
            );

            $this->authorizationService->check(
                $decodedToken->getUserId(),
                SectorTypes::User,
                PermissionTypes::List
            );

            $fetchedUser = $this->repository->findById($id);

            return $fetchedUser;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findByUsername(Username $username, EncodedAuthenticationToken $token): ?User
    {
        try {
            $decodedToken = $this->authenticationService->validate(
                $token
            );

            $this->authorizationService->check(
                $decodedToken->getUserId(),
                SectorTypes::User,
                PermissionTypes::List
            );

            $fetchedUser = $this->repository->findByUsername($username);

            return $fetchedUser;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findAll(EncodedAuthenticationToken $token): UserCollection
    {
        try {
            $decodedToken = $this->authenticationService->validate(
                $token
            );

            $this->authorizationService->check(
                $decodedToken->getUserId(),
                SectorTypes::User,
                PermissionTypes::List
            );

            return $this->repository->findAll();
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
