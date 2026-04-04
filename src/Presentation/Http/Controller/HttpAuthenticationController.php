<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Controller;

use Mvreisg\GamebaseBackend\Application\Authentication\Services\AuthenticationService;
use Mvreisg\GamebaseBackend\Infrastructure\Serialization\Authentication\Data\AuthenticationDataSerializer;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class HttpAuthenticationController
{
    private AuthenticationService $authenticationService;

    public function __construct(
        AuthenticationService $authenticationService
    ) {
        $this->authenticationService = $authenticationService;
    }

    public function validate(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
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
