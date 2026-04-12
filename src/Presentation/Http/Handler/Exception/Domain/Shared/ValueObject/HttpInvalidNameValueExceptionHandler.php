<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Handler\Exception\Domain\Shared\ValueObject;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Interfaces\ErrorHandlerInterface;
use Slim\Psr7\Response;

class HttpInvalidNameValueExceptionHandler implements ErrorHandlerInterface
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
                    "message" => $exception->getMessage()
                ])
            );
        return $response
            ->withHeader("Content-Type", "application/json")
            ->withStatus(422);
    }
}
