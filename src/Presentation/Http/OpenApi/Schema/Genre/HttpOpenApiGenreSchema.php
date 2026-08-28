<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\OpenApi\Schema\Genre;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "Genre",
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
            example: "RPG (Role-playing game)"
        ),
        new OA\Property(
            property: "is_active",
            type: "boolean",
            example: true
        )
    ]
)]
class HttpOpenApiGenreSchema
{
}
