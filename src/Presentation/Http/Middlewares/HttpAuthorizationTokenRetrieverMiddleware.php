<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Middlewares;

use Mvreisg\GamebaseBackend\Domain\Authorization\Exceptions\UnauthorizedException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;

class HttpAuthorizationTokenRetrieverMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            $bearer = $request->getHeaderLine("Authorization");
            if (empty($bearer)) {
                throw new UnauthorizedException();
            }

            $explodedBearer = explode(" ", $bearer);
            if (count($explodedBearer) !== 2) {
                throw new UnauthorizedException();
            }

            $token = $explodedBearer[1];

            $request = $request->withAttribute("token", $token);

            return $handler->handle($request);
        } catch (\Throwable $e) {
            $response = new Response()
                ->withHeader("Content-Type", "application/json");
            $response
                ->getBody()
                ->write(
                    json_encode([
                        "message" => $e->getMessage()
                    ])
                );
            if ($e instanceof UnauthorizedException) {
                return $response->withStatus(401);
            } else {
                return $response->withStatus(500);
            }
        }

    }
}
