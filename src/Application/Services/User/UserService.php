<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Services\User;

use Mvreisg\GamebaseBackend\Domain\Entities\EncodedPassword;
use Mvreisg\GamebaseBackend\Domain\Entities\Id;
use Mvreisg\GamebaseBackend\Domain\Entities\User;
use Mvreisg\GamebaseBackend\Domain\Entities\UserCollection;
use Mvreisg\GamebaseBackend\Domain\Entities\Username;
use Mvreisg\GamebaseBackend\Domain\Repositories\Interface\UserRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Encryption\EncryptionAdapter;

class UserService
{
    private UserRepositoryInterface $repository;
    private EncryptionAdapter $encrypter;

    public function __construct(
        UserRepositoryInterface $repository,
        EncryptionAdapter $encrypter
    ) {
        $this->repository = $repository;
        $this->encrypter = $encrypter;
    }

    public function insert(User $new): User
    {
        try {
            $this->repository->checkDuplicatedUsernames(
                Username::make($new->getUsernameValue())
            );

            $encodedPassword = $this->encrypter->encrypt(
                $new->getPasswordValue()
            );

            $insertedUser = $this->repository->insert(
                new User(
                    Username::make($new->getUsernameValue()),
                    EncodedPassword::make($encodedPassword),
                    $new->getIsActive()
                )
            );

            return $insertedUser;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function update(User $existant): bool
    {
        try {
            $this->repository->checkIfExists(
                Id::make($existant->getIdValue())
            );

            $this->repository->checkDuplicatedUsernames(
                Username::make($existant->getUsernameValue())
            );

            $validatedPassword = $existant->getPasswordValue();
            $encodedPassword = $this->encrypter->encrypt($validatedPassword);

            $user = new User(
                Username::make($existant->getUsernameValue()),
                EncodedPassword::make($encodedPassword),
                $existant->getIsActive()
            );
            $user->setId(Id::make($existant->getIdValue()));
            $wasUpdated = $this->repository->update(
                $user
            );

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
