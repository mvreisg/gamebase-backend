<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Controller;

use Mvreisg\GamebaseBackend\Application\Authentication\Service\AuthenticationService;
use Mvreisg\GamebaseBackend\Infrastructure\Serialization\Authentication\Data\AuthenticationDataSerializer;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: "Authentication",
    description: "Endpoints related to authentication"
)]
class HttpAuthenticationController
{
    private AuthenticationService $authenticationService;

    public function __construct(
        AuthenticationService $authenticationService
    ) {
        $this->authenticationService = $authenticationService;
    }

    #[OA\Get(
        path: "/authentication/validate",
        summary: "Validate",
        description:
            "Validates the provided authentication token and returns the associated authentication data if valid.",
        tags: ["Authentication"],
        security: [["bearerAuth" => []]],
    )]
    #[OA\Response(
        response: 200,
        description: "Valid token",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: "status",
                    type: "string",
                    example: "valid"
                ),
                new OA\Property(
                    property: "data",
                    type: "object",
                    ref: "#/components/schemas/AuthenticationData"
                )
            ]
        )
    )]
    public function validate(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        try {
            $response = $response->withHeader("Content-Type", "application/json");

            $token = $request->getAttribute("token");

            $decodedToken = $this->authenticationService->validate(
                $token
            );

            $response
                ->getBody()
                ->write(
                    json_encode([
                        "status" => "valid",
                        "data" => AuthenticationDataSerializer::toArray(
                            $decodedToken->getAuthenticationData()
                        )
                    ])
                );
            return $response->withStatus(200);
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
