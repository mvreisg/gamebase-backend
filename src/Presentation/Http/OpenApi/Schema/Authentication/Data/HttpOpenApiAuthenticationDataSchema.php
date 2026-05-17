<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\OpenApi\Schema\Authentication\Data;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "AuthenticationData",
    type: "object",
    properties: [
        new OA\Property(
            property: "userId",
            type: "integer",
            example: 1
        ),
        new OA\Property(
            property: "username",
            type: "string",
            example: "john_doe"
        )
    ]
)]
class HttpOpenApiAuthenticationDataSchema
{
}
