<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\OpenApi\Schema\Permission\Entity;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "PermissionEntity",
    type: "object",
    properties: [
        new OA\Property(
            property: "id",
            type: "integer",
            example: 1
        ),
        new OA\Property(
            property: "name",
            type: "string",
            example: "create"
        ),
        new OA\Property(
            property: "value",
            type: "string",
            example: "create"
        ),
        new OA\Property(
            property: "is_active",
            type: "boolean",
            example: true
        )
    ]
)]
class HttpOpenApiPermissionEntitySchema
{
}
