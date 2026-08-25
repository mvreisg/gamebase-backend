<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\UserSectorPermission\Repository\Dto;

use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;

final readonly class UserSectorPermissionRepositoryInterfaceInsertDto
{
    public function __construct(
        public Id $userId,
        public Id $sectorId,
        public Id $permissionId
    ) {
    }
}
