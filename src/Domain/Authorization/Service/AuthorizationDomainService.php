<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Authorization\Service;

use Mvreisg\GamebaseBackend\Domain\Authorization\Exception\UnauthorizedException;
use Mvreisg\GamebaseBackend\Domain\Authorization\Permission\PermissionType;
use Mvreisg\GamebaseBackend\Domain\Authorization\Sector\SectorType;
use Mvreisg\GamebaseBackend\Domain\Permission\ValueObject\PermissionValue\PermissionValue;
use Mvreisg\GamebaseBackend\Domain\Sector\ValueObject\SectorValue\SectorValue;
use Mvreisg\GamebaseBackend\Domain\UserSectorPermission\Entity\Collection\UserSectorPermissionCollection;

class AuthorizationDomainService
{
    public function checkBetweenAll(
        UserSectorPermissionCollection $userSectorPermissions,
        SectorValue $sectorValue,
        PermissionValue $permissionValue
    ): bool {
        try {
            $sectorArray = array_filter(
                $userSectorPermissions->fetchAll(),
                fn ($usp) => $usp->getSector()->equals($sectorValue)
            );
            if (count($sectorArray) === 0) {
                throw new UnauthorizedException();
            }

            $permissionArray = array_filter(
                $userSectorPermissions->fetchAll(),
                fn ($usp) => $usp->getPermission()->equals($permissionValue)
            );
            if (count($permissionArray) === 0) {
                throw new UnauthorizedException();
            }

            $sectorValue = array_shift($sectorArray)->getSector()->getSectorValue();
            $permissionValue = array_shift($permissionArray)->getPermission()->getPermissionValue();

            return $this->check($sectorValue, $permissionValue);
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function check(
        SectorValue $sectorValue,
        PermissionValue $permissionValue
    ): bool {
        try {
            switch (SectorType::from($sectorValue->getValue())) {
                case SectorType::GameGenre:
                case SectorType::GamePlatform:
                case SectorType::UserSectorPermission:
                    return PermissionType::from($permissionValue->getValue()) !== PermissionType::Activate;
                case SectorType::Game:
                case SectorType::Genre:
                case SectorType::Platform:
                case SectorType::User:
                case SectorType::Sector:
                case SectorType::Permission:
                    return PermissionType::from($permissionValue->getValue()) !== PermissionType::Delete;
                default:
                    throw new UnauthorizedException();
            }
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
