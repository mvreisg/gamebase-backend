<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\OpenApi\Schema\GameGenre;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "GameGenre",
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
            property: "genre",
            ref: "#/components/schemas/Genre"
        ),
    ]
)]
class HttpOpenApiGameGenreSchema
{
}
