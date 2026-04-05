<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Permission\Service;

use Mvreisg\GamebaseBackend\Domain\Permission\Exception\PermissionNotFoundException;
use Mvreisg\GamebaseBackend\Domain\Permission\Repository\PermissionRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Shared\Exception\DuplicatedNameException;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Name\Name;

class PermissionDomainService
{
    private PermissionRepositoryInterface $repository;

    public function __construct(
        PermissionRepositoryInterface $repository
    ) {
        $this->repository = $repository;
    }

    public function ensureNameIsUnique(Name $name): void
    {
        $hasDuplicatedNames = $this->repository->checkDuplicatedNames(
            $name
        );

        if ($hasDuplicatedNames) {
            throw new DuplicatedNameException(
                $name
            );
        }
    }

    public function ensurePermissionExists(Id $id): void
    {
        $doesExist = $this->repository->checkIfExists($id);

        if ($doesExist === false) {
            throw new PermissionNotFoundException(
                $id
            );
        }
    }
}
