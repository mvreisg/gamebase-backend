<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\OpenApi\Schema\GamePlatform;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "GamePlatform",
    type: "object",
    properties: [
        new OA\Property(
            property: "id",
            type: "integer",
            example: 1
        ),
        new OA\Property(
            property: "game",
            ref: "#/components/schemas/Game"
        ),
        new OA\Property(
            property: "platform",
            ref: "#/components/schemas/Platform"
        ),
    ]
)]
class HttpOpenApiGamePlatformSchema
{
}
