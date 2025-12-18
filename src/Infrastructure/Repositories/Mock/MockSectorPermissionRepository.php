<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock;

use Mvreisg\GamebaseBackend\Domain\Entities\SectorPermission\SectorPermission;
use Mvreisg\GamebaseBackend\Domain\Repositories\PermissionRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\SectorRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\SectorPermissionRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock\Exceptions\MockUnexistantRegisterException;

class MockSectorPermissionRepository implements SectorPermissionRepositoryInterface
{
    private array $data;
    private int $idIndex;

    public function __construct()
    {
        $this->data = [];
        $this->idIndex = 0;
    }

    public function insert(SectorPermission $sectorPermission): SectorPermission
    {
        try {
            $this->idIndex++;
            $sectorPermission->setId($this->idIndex);
            $this->data[] = $sectorPermission;
            return new SectorPermission(
                $sectorPermission->getId(),
                $sectorPermission->getSectorId(),
                $sectorPermission->getPermissionId()
            );
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function update(SectorPermission $sectorPermission): bool
    {
        try {
            $index = -1;
            foreach ($this->data as $key => $value) {
                if ($value->getId() === $sectorPermission->getId()) {
                    $index = $key;
                    break;
                }
            }

            if ($index < 0) {
                return false;
            }

            $foundSectorPermission = $this->data[$index];

            $hasDifferentSectorId =
                $foundSectorPermission->getSectorId() !== $sectorPermission->getSectorId();

            $hasDifferentPermissionId =
                $foundSectorPermission->getPermissionId() !== $sectorPermission->getPermissionId();

            $isDifferent = $hasDifferentSectorId || $hasDifferentPermissionId;

            if ($isDifferent === false) {
                return false;
            }

            $this->data[$index] = new SectorPermission(
                $sectorPermission->getId(),
                $sectorPermission->getSectorId(),
                $sectorPermission->getPermissionId()
            );

            return true;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function delete(SectorPermission $sectorPermission): bool
    {
        $index = -1;
        foreach ($this->data as $key => $value) {
            if ($value->getId() === $sectorPermission->getId()) {
                $index = $key;
                break;
            }
        }

        if ($index < 0) {
            return false;
        }

        unset($this->data[$index]);
        return true;
    }

    public function findById(int $id): SectorPermission
    {
        foreach ($this->data as $key => $value) {
            if ($value->getId() === $id) {
                return $value;
            }
        }
        throw new MockUnexistantRegisterException(
            "Unexistant user permission with id $id"
        );
    }

    public function findAllByPermissionId(int $permissionId): array
    {
        $data = [];
        foreach ($this->data as $key => $value) {
            if ($value->getPermissionId() === $permissionId) {
                $data[] = $value;
            }
        }
        return $data;
    }

    public function findAll(): array
    {
        return $this->data;
    }

    public function checkIfExists(int $id): void
    {
        foreach ($this->data as $key => $value) {
            if ($value->getId() === $id) {
                return;
            }
        }
        throw new MockUnexistantRegisterException(
            "Unexistant user permission with id $id"
        );
    }
}
