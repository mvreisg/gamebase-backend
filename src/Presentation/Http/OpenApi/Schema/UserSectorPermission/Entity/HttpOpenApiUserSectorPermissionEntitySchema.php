<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\OpenApi\Schema\UserSectorPermission\Entity;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "UserSectorPermissionEntity",
    type: "object",
    properties: [
        new OA\Property(
            property: "id",
            type: "integer",
            example: 1
        ),
        new OA\Property(
            property: "user",
            ref: "#/components/schemas/UserEntity"
        ),
        new OA\Property(
            property: "sector",
            ref: "#/components/schemas/SectorEntity"
        ),
        new OA\Property(
            property: "permission",
            ref: "#/components/schemas/PermissionEntity"
        )
    ]
)]
class HttpOpenApiUserSectorPermissionEntitySchema
{
}
