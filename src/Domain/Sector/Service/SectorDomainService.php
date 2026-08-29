<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Sector\Service;

use Mvreisg\GamebaseBackend\Domain\Sector\Exception\DuplicatedSectorValueException;
use Mvreisg\GamebaseBackend\Domain\Sector\Exception\SectorNotFoundException;
use Mvreisg\GamebaseBackend\Domain\Sector\Repository\SectorRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Sector\ValueObject\SectorValue\SectorValue;
use Mvreisg\GamebaseBackend\Domain\Shared\Exception\DuplicatedNameException;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Name\Name;

class SectorDomainService
{
    private SectorRepositoryInterface $repository;

    public function __construct(
        SectorRepositoryInterface $repository
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

    public function ensureValueIsUnique(SectorValue $value): void
    {
        $hasDuplicatedValues = $this->repository->checkIfValueExists(
            $value
        );

        if ($hasDuplicatedValues) {
            throw new DuplicatedSectorValueException(
                $value
            );
        }
    }

    public function ensureSectorExists(Id $id): void
    {
        $doesExist = $this->repository->checkIfExists($id);

        if ($doesExist === false) {
            throw new SectorNotFoundException(
                $id
            );
        }
    }
}
