<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\OpenApi\Schema\Sector\Entity;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "SectorEntity",
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
            example: "game"
        ),
        new OA\Property(
            property: "value",
            type: "string",
            example: "game"
        ),
        new OA\Property(
            property: "is_active",
            type: "boolean",
            example: true
        )
    ]
)]
class HttpOpenApiSectorEntitySchema
{
}
