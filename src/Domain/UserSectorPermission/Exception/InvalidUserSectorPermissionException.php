<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\UserSectorPermission\Exception;

use Mvreisg\GamebaseBackend\Domain\UserSectorPermission\Entity\UserSectorPermission;

class InvalidUserSectorPermissionException extends \Exception
{
    public function __construct(
        UserSectorPermission $userSectorPermission
    ) {
        $username = $userSectorPermission->getUser()->getUsername()->getValue();
        $sectorName = $userSectorPermission->getSector()->getName()->getValue();
        $permissionName = $userSectorPermission->getPermission()->getName()->getValue();
        parent::__construct(
            "The user {$username} with the sector {$sectorName} and permission {$permissionName} is invalid!"
        );
    }
}
