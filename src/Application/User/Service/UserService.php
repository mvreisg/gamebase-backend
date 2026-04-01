<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\User\Service;

use Mvreisg\GamebaseBackend\Application\Authentication\Services\AuthenticationService;
use Mvreisg\GamebaseBackend\Application\Authorization\Exceptions\UnauthorizedException;
use Mvreisg\GamebaseBackend\Application\Authorization\Service\CheckAuthorizationUseCase;
use Mvreisg\GamebaseBackend\Application\User\Service\Exceptions\DuplicatedUserException;
use Mvreisg\GamebaseBackend\Application\User\Service\Exceptions\DuplicatedUsernameException;
use Mvreisg\GamebaseBackend\Domain\Authorization\Permission\PermissionType;
use Mvreisg\GamebaseBackend\Domain\Authorization\Sector\SectorType;
use Mvreisg\GamebaseBackend\Domain\Encryption\Interface\EncryptionInterface;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;
use Mvreisg\GamebaseBackend\Domain\User\Entity\Collection\UserCollection;
use Mvreisg\GamebaseBackend\Domain\User\Entity\User;
use Mvreisg\GamebaseBackend\Domain\User\Repository\UserRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\User\ValueObject\Password\Encoded\EncodedPassword;
use Mvreisg\GamebaseBackend\Domain\User\ValueObject\Username\Username;

class UserService
{
    private UserRepositoryInterface $repository;
    private AuthenticationService $authenticationService;
    private EncryptionInterface $encrypter;
    private CheckAuthorizationUseCase $checkAuthorizationUseCase;

    public function __construct(
        UserRepositoryInterface $repository,
        AuthenticationService $authenticationService,
        EncryptionInterface $encrypter,
        CheckAuthorizationUseCase $checkAuthorizationUseCase
    ) {
        $this->repository = $repository;
        $this->authenticationService = $authenticationService;
        $this->encrypter = $encrypter;
        $this->checkAuthorizationUseCase = $checkAuthorizationUseCase;
    }

    public function insert(User $new, string $token): User
    {
        try {
            $isAuthorized = $this->checkAuthorizationUseCase->execute(
                $token,
                SectorType::User,
                PermissionType::Create
            );

            if ($isAuthorized === false) {
                throw new UnauthorizedException();
            }

            $hasDuplicatedUsernames = $this->repository->checkDuplicatedUsernames(
                $new->getUsername()
            );

            if ($hasDuplicatedUsernames) {
                throw new DuplicatedUsernameException(
                    $new->getUsername()
                );
            }

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

    public function update(User $existant, string $token): bool
    {
        try {
            $isAuthorized = $this->checkAuthorizationUseCase->execute(
                $token,
                SectorType::User,
                PermissionType::Update
            );

            if ($isAuthorized === false) {
                throw new UnauthorizedException();
            }

            $doesUserAlreadyExists = $this->repository->checkIfExists(
                $existant->getId()
            );

            if ($doesUserAlreadyExists) {
                throw new DuplicatedUserException(
                    $existant->getId()
                );
            }

            $hasDuplicatedUsernames = $this->repository->checkDuplicatedUsernames(
                $existant->getUsername()
            );

            if ($hasDuplicatedUsernames) {
                throw new DuplicatedUsernameException(
                    $existant->getUsername()
                );
            }

            $encodedPassword = $this->encrypter->encrypt(
                $existant->getPassword()->getValue()
            );

            $user = new User(
                $existant->getUsername(),
                EncodedPassword::make($encodedPassword),
                $existant->getIsActive()
            );
            $user->setId(
                $existant->getId()
            );

            $wasUpdated = $this->repository->update(
                $user
            );

            return $wasUpdated;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function setIsActive(Id $id, bool $isActive, string $token): bool
    {
        try {
            $decodedToken = $this->authenticationService->validate(
                $token
            );

            $this->authorizationService->check(
                $decodedToken->getAuthenticationData()->getUserId(),
                SectorType::User,
                PermissionType::Activate
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

    public function findById(Id $id, string $token): User
    {
        try {
            $decodedToken = $this->authenticationService->validate(
                $token
            );

            $this->authorizationService->check(
                $decodedToken->getAuthenticationData()->getUserId(),
                SectorType::User,
                PermissionType::List
            );

            $fetchedUser = $this->repository->findById($id);

            return $fetchedUser;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findByUsername(Username $username, string $token): ?User
    {
        try {
            $decodedToken = $this->authenticationService->validate(
                $token
            );

            $this->authorizationService->check(
                $decodedToken->getAuthenticationData()->getUserId(),
                SectorType::User,
                PermissionType::List
            );

            $fetchedUser = $this->repository->findByUsername($username);

            return $fetchedUser;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findAll(string $token): UserCollection
    {
        try {
            $decodedToken = $this->authenticationService->validate(
                $token
            );

            $this->authorizationService->check(
                $decodedToken->getAuthenticationData()->getUserId(),
                SectorType::User,
                PermissionType::List
            );

            return $this->repository->findAll();
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
