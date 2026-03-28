<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Handlers\Exceptions;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Interfaces\ErrorHandlerInterface;
use Slim\Psr7\Response;

class HttpMethodNotAllowedExceptionHandler implements ErrorHandlerInterface
{
    public function __invoke(
        ServerRequestInterface $request,
        \Throwable $exception,
        bool $displayErrorDetails,
        bool $logErrors,
        bool $logErrorDetails
    ): ResponseInterface {
        $response = new Response();
        $response
            ->getBody()
            ->write(
                json_encode([
                    "message" => "Method not allowed: {$request->getMethod()}."
                ])
            );
        return $response
            ->withHeader("Content-Type", "application/json")
            ->withStatus(404);
    }
}
