<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Entities;

use Mvreisg\GamebaseBackend\Domain\Exceptions\Entities\EntityInvalidValueException;

class SectorPermissionEntity 
{
    private int $id;    
    private int $sectorId;
    private int $permissionId;

    public function __construct(int $id = 0, int $sectorId = 0, int $permissionId = 0)
    {
        $this->id = $id;        
        $this->sectorId = $sectorId;
        $this->permissionId = $permissionId;
    }

    public function getId(): int 
    {
        return $this->id;
    }

    public function setId(int $id): void 
    {
        $this->id = $id;
    }

    public function getSectorId(): int 
    {
        return $this->sectorId;
    }

    public function setSectorId(int $sectorId): void 
    {
        $this->sectorId = $sectorId;
    }

    public function getPermissionId(): int 
    {
        return $this->permissionId;
    }

    public function setPermissionId(int $permissionId): void 
    {
        $this->permissionId = $permissionId;
    }

    public function validateId(): void 
    {
        if ($this->id <= 0){
            throw new EntityInvalidValueException(
                'id must be greater than zero!'
            );
        }
    }

    public function validateSectorId(): void 
    {
        if ($this->sectorId <= 0){
            throw new EntityInvalidValueException(
                'sectorId must be greater than zero!'
            );
        }
    }

    public function validatePermissionId(): void 
    {
        if ($this->permissionId <= 0){
            throw new EntityInvalidValueException(
                'permissionId must be greater than zero!'
            );
        }
    }    
}