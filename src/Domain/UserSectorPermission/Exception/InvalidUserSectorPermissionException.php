<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\UserSectorPermission\Exception;

use Mvreisg\GamebaseBackend\Domain\Permission\Entity\Permission;
use Mvreisg\GamebaseBackend\Domain\Sector\Entity\Sector;
use Mvreisg\GamebaseBackend\Domain\User\Entity\User;

class InvalidUserSectorPermissionException extends \Exception
{
    public function __construct(
        User $user,
        Sector $sector,
        Permission $permission
    ) {
        $username = $user->getUsername()->getValue();
        $sectorName = $sector->getName()->getValue();
        $permissionName = $permission->getName()->getValue();
        parent::__construct(
            "The user {$username} with the sector {$sectorName} and permission {$permissionName} is invalid!"
        );
    }
}
