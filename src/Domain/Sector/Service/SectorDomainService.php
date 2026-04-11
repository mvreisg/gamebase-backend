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

    public function ensureValueIsUnique(?Id $id = null, SectorValue $value): void
    {
        $hasDuplicatedValues = $this->repository->checkDuplicatedValues(
            $id,
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
