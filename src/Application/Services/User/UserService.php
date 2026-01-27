<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Services\User;

use Mvreisg\GamebaseBackend\Domain\Data\Id;
use Mvreisg\GamebaseBackend\Domain\Data\User;
use Mvreisg\GamebaseBackend\Domain\Data\UserCollection;
use Mvreisg\GamebaseBackend\Domain\Data\Username;
use Mvreisg\GamebaseBackend\Domain\Encryption\Interface\EncryptionInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\Interface\UserRepositoryInterface;

class UserService
{
    private UserRepositoryInterface $repository;
    private EncryptionInterface $encrypter;

    public function __construct(
        UserRepositoryInterface $repository,
        EncryptionInterface $encrypter
    ) {
        $this->repository = $repository;
        $this->encrypter = $encrypter;
    }

    public function insert(User $newUser): User
    {
        try {
            $this->repository->checkDuplicatedUsernames(
                Username::make($newUser->getUsernameValue())
            );

            $encodedPassword = $this->encrypter->encrypt(
                $newUser->getPasswordValue()
            );
            $newUser->alterPasswordValue($encodedPassword);
            $insertedUser = $this->repository->insert($newUser);

            return $insertedUser;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function update(User $existantUser): bool
    {
        try {
            $this->repository->checkIfExists(
                Id::make($existantUser->getIdValue())
            );

            $this->repository->checkDuplicatedUsernames(
                Username::make($existantUser->getUsernameValue())
            );

            $validatedPassword = $existantUser->getPasswordValue();
            $encodedPassword = $this->encrypter->encrypt($validatedPassword);
            $existantUser->alterPasswordValue($encodedPassword);
            $wasUpdated = $this->repository->update($existantUser);

            return $wasUpdated;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function setIsActive(Id $id, bool $isActive): bool
    {
        try {
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

    public function findById(Id $id): User
    {
        try {
            $fetchedUser = $this->repository->findById($id);

            return $fetchedUser;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findByUsername(Username $username): ?User
    {
        try {
            $fetchedUser = $this->repository->findByUsername($username);

            return $fetchedUser;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findAll(): UserCollection
    {
        try {
            return $this->repository->findAll();
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
