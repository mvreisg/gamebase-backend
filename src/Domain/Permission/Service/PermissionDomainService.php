<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Permission\Service;

use Mvreisg\GamebaseBackend\Domain\Permission\Exception\DuplicatedPermissionValueException;
use Mvreisg\GamebaseBackend\Domain\Permission\Exception\PermissionNotFoundException;
use Mvreisg\GamebaseBackend\Domain\Permission\Repository\PermissionRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Permission\ValueObject\PermissionValue\PermissionValue;
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

    public function ensureNameIsUnique(?Id $id = null, Name $name): void
    {
        $hasDuplicatedNames = $this->repository->checkDuplicatedNames(
            $id,
            $name
        );

        if ($hasDuplicatedNames) {
            throw new DuplicatedNameException(
                $name
            );
        }
    }

    public function ensureValueIsUnique(?Id $id = null, PermissionValue $value): void
    {
        $hasDuplicatedValues = $this->repository->checkDuplicatedValues(
            $id,
            $value
        );

        if ($hasDuplicatedValues) {
            throw new DuplicatedPermissionValueException(
                $value
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
