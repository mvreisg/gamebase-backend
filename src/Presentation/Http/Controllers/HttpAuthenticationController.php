<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Controllers;

use Mvreisg\GamebaseBackend\Application\Services\Authentication\AuthenticationService;
use Mvreisg\GamebaseBackend\Domain\Authentication\Exceptions\InvalidTokenException;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\Action\Validator\Decoded\Exceptions\DecodedAuthenticationTokenValidatorException;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\State\Encoded\EncodedAuthenticationToken;
use Mvreisg\GamebaseBackend\Domain\Authorization\Exceptions\UnauthorizedException;
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

            $this->authenticationService->validate(
                new EncodedAuthenticationToken(
                    $token
                )
            );

            $response
                ->getBody()
                ->write(
                    json_encode([
                        "message" => "Valid."
                    ])
                );
            return $response->withStatus(200);
        } catch (\Throwable $e) {
            $response
                ->getBody()
                ->write(
                    json_encode([
                        "message" => $e->getMessage()
                    ])
                );
            if ($e instanceof UnauthorizedException) {
                return $response->withStatus(401);
            } elseif (
                $e instanceof InvalidTokenException ||
                $e instanceof DecodedAuthenticationTokenValidatorException
            ) {
                return $response->withStatus(400);
            } else {
                return $response->withStatus(500);
            }
        }
    }
}
