<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Controller;

use Mvreisg\GamebaseBackend\Application\Genre\Service\GenreService;
use Mvreisg\GamebaseBackend\Domain\Genre\Entity\Genre;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Name\Name;
use Mvreisg\GamebaseBackend\Infrastructure\Arrays\ArrayKeysExistanceChecker;
use Mvreisg\GamebaseBackend\Presentation\Http\Util\Response\HttpMissingKeysInformerResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class HttpGenreController
{
    private GenreService $genreService;

    public function __construct(GenreService $genreService)
    {
        $this->genreService = $genreService;
    }

    public function insert(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $response = $response->withHeader("Content-Type", "application/json");

            $token = $request->getAttribute("token");

            $body = $request->getParsedBody();

            $missingBodyKeys = ArrayKeysExistanceChecker::checkAndReturnMissingKeys(
                $body,
                ["name", "is_active"]
            );
            if (count($missingBodyKeys) > 0) {
                return HttpMissingKeysInformerResponse::getStatusAsArrayOfBodyKeys($response, $missingBodyKeys);
            }

            $name = $body["name"];
            $isActive = $body["is_active"];

            $genre = $this->genreService->insert(
                new Genre(
                    null,
                    Name::create($name),
                    $isActive
                ),
                $token
            );

            $data = [
                "id" => $genre->getId()->getValue(),
                "name" => $genre->getName()->getValue(),
                "is_active" => $genre->getIsActive()
            ];

            $response
                ->getBody()
                ->write(
                    json_encode([
                        "data" => $data
                    ])
                );
            return $response->withStatus(201);
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function update(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $response = $response->withHeader("Content-Type", "application/json");

            $token = $request->getAttribute("token");

            $missingUriParams = ArrayKeysExistanceChecker::checkAndReturnMissingKeys($args, ["id"]);
            if (count($missingUriParams) > 0) {
                return HttpMissingKeysInformerResponse::getStatusAsArrayOfUriParams($response, $missingUriParams);
            }

            $body = $request->getParsedBody();

            $missingBodyKeys = ArrayKeysExistanceChecker::checkAndReturnMissingKeys(
                $body,
                ["name", "is_active"]
            );
            if (count($missingBodyKeys) > 0) {
                return HttpMissingKeysInformerResponse::getStatusAsArrayOfBodyKeys($response, $missingBodyKeys);
            }

            $id = (int)$args["id"];
            $name = $body["name"];
            $isActive = $body["is_active"];

            $genre = new Genre(
                Id::create($id),
                Name::create($name),
                $isActive
            );

            $wasUpdated = $this->genreService->update(
                $genre,
                $token
            );

            $response
                ->getBody()
                ->write(
                    json_encode([
                        "status" => $wasUpdated ? "updated" : "same"
                    ])
                );
            return $response
                ->withStatus(200);
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function setIsActive(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        try {
            $response = $response->withHeader("Content-Type", "application/json");

            $token = $request->getAttribute("token");

            $missingUriParams = ArrayKeysExistanceChecker::checkAndReturnMissingKeys($args, ["id"]);
            if (count($missingUriParams) > 0) {
                return HttpMissingKeysInformerResponse::getStatusAsArrayOfUriParams($response, $missingUriParams);
            }

            $body = $request->getParsedBody();

            $missingBodyKeys = ArrayKeysExistanceChecker::checkAndReturnMissingKeys(
                $body,
                ["is_active"]
            );
            if (count($missingBodyKeys) > 0) {
                return HttpMissingKeysInformerResponse::getStatusAsArrayOfBodyKeys($response, $missingBodyKeys);
            }

            $id = (int)$args["id"];
            $isActive = $body["is_active"];

            $wasUpdated = $this->genreService->setIsActive(
                Id::create($id),
                $isActive,
                $token
            );

            $response
                ->getBody()
                ->write(
                    json_encode([
                        "status" => $wasUpdated ? "updated" : "same"
                    ])
                );
            return $response
                ->withStatus(200);
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findById(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        try {
            $response = $response->withHeader("Content-Type", "application/json");

            $token = $request->getAttribute("token");

            $missingUriParams = ArrayKeysExistanceChecker::checkAndReturnMissingKeys($args, ["id"]);
            if (count($missingUriParams) > 0) {
                return HttpMissingKeysInformerResponse::getStatusAsArrayOfUriParams($response, $missingUriParams);
            }

            $id = (int)$args["id"];

            $genre = $this->genreService->findById(
                Id::create($id),
                $token
            );

            if ($genre === null) {
                $response
                    ->getBody()
                    ->write(
                        json_encode([
                            "found" => false
                        ])
                    );
                return $response->withStatus(404);
            }

            $response
                ->getBody()
                ->write(
                    json_encode([
                        "found" => true,
                        "data" => [
                            "id" => $genre->getId()->getValue(),
                            "name" => $genre->getName()->getValue(),
                            "is_active" => $genre->getIsActive()
                        ]
                    ])
                );
            return $response
                ->withStatus(200);
            return $response;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findAll(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        try {
            $response = $response->withHeader("Content-Type", "application/json");

            $token = $request->getAttribute("token");

            $genres = $this->genreService->findAll(
                $token
            );

            if ($genres === null) {
                $response
                    ->getBody()
                    ->write(
                        json_encode([
                            "number_found" => 0
                        ])
                    );
                return $response->withStatus(404);
            }

            foreach ($genres->fetchAll() as $genre) {
                $data[] = [
                    "id" => $genre->getId()->getValue(),
                    "name" => $genre->getName()->getValue(),
                    "is_active" => $genre->getIsActive()
                ];
            }

            $response
                ->getBody()
                ->write(
                    json_encode([
                        "number_found" => $genres->count(),
                        "data" => $data
                    ])
                );
            return $response->withStatus(200);
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
