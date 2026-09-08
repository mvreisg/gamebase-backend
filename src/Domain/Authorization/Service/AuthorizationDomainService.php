<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Authorization\Service;

use Mvreisg\GamebaseBackend\Domain\Authorization\Exception\UnauthorizedException;
use Mvreisg\GamebaseBackend\Domain\Permission\ValueObject\PermissionValue\PermissionValue;
use Mvreisg\GamebaseBackend\Domain\Sector\ValueObject\SectorValue\SectorValue;
use Mvreisg\GamebaseBackend\Domain\UserSectorPermission\Entity\Collection\UserSectorPermissionCollection;

class AuthorizationDomainService
{
    public function ensureHasPermission(
        UserSectorPermissionCollection $userSectorPermissions,
        SectorValue $sectorValue,
        PermissionValue $permissionValue
    ): bool {
        try {
            foreach ($userSectorPermissions->fetchAll() as $userSectorPermission) {
                $sector = $userSectorPermission->getSector();
                $permission = $userSectorPermission->getPermission();
                if ($sector->equals($sectorValue) && $permission->equals($permissionValue)) {
                    return true;
                }
            }

            throw new UnauthorizedException();
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
