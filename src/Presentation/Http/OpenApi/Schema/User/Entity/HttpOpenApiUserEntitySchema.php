<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\OpenApi\Schema\User\Entity;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "UserEntity",
    type: "object",
    properties: [
        new OA\Property(
            property: "id",
            type: "integer",
            example: 1
        ),
        new OA\Property(
            property: "username",
            type: "string",
            example: "mvreisg"
        ),
        new OA\Property(
            property: "password",
            type: "string",
            example: "f03jg9j043g"
        ),
        new OA\Property(
            property: "is_active",
            type: "boolean",
            example: true
        )
    ]
)]
class HttpOpenApiUserEntitySchema
{
}
