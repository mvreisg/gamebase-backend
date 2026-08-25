<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\UserSectorPermission\Service\Dto;

use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;

final readonly class UserSectorPermissionServiceUpdateDto
{
    public function __construct(
        public Id $id,
        public Id $userId,
        public Id $sectorId,
        public Id $permissionId
    ) {
    }
}
