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

    public function ensureNameIsUniqueOnInsert(Name $name): void
    {
        $id = $this->repository->checkIfNameExists(
            $name
        );

        if ($id) {
            throw new DuplicatedNameException(
                $name
            );
        }
    }

    public function ensureNameIsUniqueOnUpdate(Name $name, Id $id): void
    {
        $existantId = $this->repository->checkIfNameExists(
            $name
        );

        if ($existantId === null) {
            return;
        }

        if ($existantId->getValue() !== $id->getValue()) {
            throw new DuplicatedNameException(
                $name
            );
        }
    }

    public function ensureValueIsUnique(PermissionValue $value): void
    {
        $hasDuplicatedValues = $this->repository->checkIfValueExists(
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
