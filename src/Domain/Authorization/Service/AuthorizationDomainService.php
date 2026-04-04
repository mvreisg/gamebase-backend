<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\Authorization\Service;

use Mvreisg\GamebaseBackend\Domain\Authorization\Exception\UnauthorizedException;
use Mvreisg\GamebaseBackend\Domain\Authorization\Permission\PermissionType;
use Mvreisg\GamebaseBackend\Domain\Authorization\Sector\SectorType;
use Mvreisg\GamebaseBackend\Domain\UserSectorPermission\Entity\Collection\UserSectorPermissionCollection;

class AuthorizationDomainService
{
    public function ensureHasPermission(
        UserSectorPermissionCollection $userSectorPermissions,
        SectorType $sectorType,
        PermissionType $permissionType
    ): void {
        try {
            foreach ($userSectorPermissions->fetchAll() as $userSectorPermission) {
                $sector = $userSectorPermission->getSector();
                $permission = $userSectorPermission->getPermission();
                if ($sector->equals($sectorType) && $permission->equals($permissionType)) {
                    return;
                }
            }

            throw new UnauthorizedException();
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
