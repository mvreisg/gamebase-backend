<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\OpenApi\Info;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: "1.0.0",
    title: "Gamebase-Backend API",
    description: "API documentation for the Gamebase-Backend project."
)]
#[OA\SecurityScheme(
    securityScheme: "bearerAuth",
    type: "http",
    scheme: "bearer",
    bearerFormat: "JWT"
)]
class HttpOpenApiInfo
{
}
