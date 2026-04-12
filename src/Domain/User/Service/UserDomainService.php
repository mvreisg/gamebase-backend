<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\User\Service;

use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;
use Mvreisg\GamebaseBackend\Domain\User\Exception\DuplicatedUsernameException;
use Mvreisg\GamebaseBackend\Domain\User\Exception\UserNotFoundException;
use Mvreisg\GamebaseBackend\Domain\User\Repository\UserRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\User\ValueObject\Username\Username;

class UserDomainService
{
    private UserRepositoryInterface $repository;

    public function __construct(
        UserRepositoryInterface $repository
    ) {
        $this->repository = $repository;
    }

    public function ensureUsernameIsUnique(?Id $id, Username $username): void
    {
        $hasDuplicatedUsernames = $this->repository->checkDuplicatedUsernames(
            $id,
            $username
        );

        if ($hasDuplicatedUsernames) {
            throw new DuplicatedUsernameException(
                $username
            );
        }
    }

    public function ensureUserExists(Id $id): void
    {
        $doesExist = $this->repository->checkIfExists($id);

        if ($doesExist === false) {
            throw new UserNotFoundException(
                $id
            );
        }
    }
}
