<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Domain\UserSectorPermission\Repository\Dto;

use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;

final readonly class UserSectorPermissionRepositoryInterfaceUpdateDto
{
    public function __construct(
        public Id $id,
        public Id $userId,
        public Id $sectorId,
        public Id $permissionId
    ) {
    }
}
