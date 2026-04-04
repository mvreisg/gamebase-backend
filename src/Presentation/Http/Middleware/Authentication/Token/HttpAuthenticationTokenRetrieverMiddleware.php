<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Middleware\Authentication\Token;

use Mvreisg\GamebaseBackend\Application\Authentication\Exception\InvalidTokenException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class HttpAuthenticationTokenRetrieverMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            $bearer = $request->getHeaderLine("Authorization");
            if (empty($bearer)) {
                throw new InvalidTokenException();
            }

            $explodedBearer = explode(" ", $bearer);
            if (count($explodedBearer) !== 2) {
                throw new InvalidTokenException();
            }

            $token = $explodedBearer[1];

            $request = $request->withAttribute("token", $token);

            return $handler->handle($request);
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
