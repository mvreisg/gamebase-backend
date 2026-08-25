<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\UserSectorPermission\Service\Dto;

use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;

final readonly class UserSectorPermissionServiceInsertDto
{
    public function __construct(
        public Id $userId,
        public Id $sectorId,
        public Id $permissionId
    ) {
    }
}
