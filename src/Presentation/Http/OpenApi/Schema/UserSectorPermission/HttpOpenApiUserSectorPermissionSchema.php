<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\OpenApi\Schema\UserSectorPermission;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "UserSectorPermission",
    type: "object",
    properties: [
        new OA\Property(
            property: "id",
            type: "integer",
            example: 1
        ),
        new OA\Property(
            property: "user",
            ref: "#/components/schemas/User"
        ),
        new OA\Property(
            property: "sector",
            ref: "#/components/schemas/Sector"
        ),
        new OA\Property(
            property: "permission",
            ref: "#/components/schemas/Permission"
        )
    ]
)]
class HttpOpenApiUserSectorPermissionSchema
{
}
