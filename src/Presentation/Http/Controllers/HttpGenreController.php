<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Controllers;

use Mvreisg\GamebaseBackend\Application\Services\Authorization\AuthorizationService;
use Mvreisg\GamebaseBackend\Application\Services\Genre\GenreService;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\Action\Decoder\AuthenticationTokenDecoder;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\Data\Encoded\EncodedAuthenticationToken;
use Mvreisg\GamebaseBackend\Domain\Authorization\Types\Permission\PermissionTypes;
use Mvreisg\GamebaseBackend\Domain\Authorization\Types\Sector\SectorTypes;
use Mvreisg\GamebaseBackend\Domain\Entities\Genre;
use Mvreisg\GamebaseBackend\Domain\Entities\Id;
use Mvreisg\GamebaseBackend\Domain\Entities\Name;
use Mvreisg\GamebaseBackend\Domain\Utils\Arrays\ArrayKeysExistanceChecker;
use Mvreisg\GamebaseBackend\Presentation\Http\Utils\Response\HttpMissingKeysInformerResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class HttpGenreController
{
    private GenreService $genreService;
    private AuthorizationService $authorizationService;
    private AuthenticationTokenDecoder $authenticationTokenDecoder;

    public function __construct(
        GenreService $genreService,
        AuthorizationService $authorizationService,
        AuthenticationTokenDecoder $authenticationTokenDecoder
    ) {
        $this->genreService = $genreService;
        $this->authorizationService = $authorizationService;
        $this->authenticationTokenDecoder = $authenticationTokenDecoder;
    }

    public function insert(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $response = $response->withHeader("Content-Type", "application/json");

            $token = $request->getAttribute("token");

            $decodedToken = $this->authenticationTokenDecoder->decode(
                new EncodedAuthenticationToken(
                    $token
                )
            );

            $this->authorizationService->check(
                $decodedToken->getUserId(),
                SectorTypes::Genre,
                PermissionTypes::Create
            );

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
                    Name::make($name),
                    $isActive
                )
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

            $decodedToken = $this->authenticationTokenDecoder->decode(
                new EncodedAuthenticationToken(
                    $token
                )
            );

            $this->authorizationService->check(
                $decodedToken->getUserId(),
                SectorTypes::Genre,
                PermissionTypes::Update
            );

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
                Name::make($name),
                $isActive
            );
            $genre->setId(Id::make($id));

            $wasUpdated = $this->genreService->update(
                $genre
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

    public function setIsActive(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $response = $response->withHeader("Content-Type", "application/json");

            $token = $request->getAttribute("token");

            $decodedToken = $this->authenticationTokenDecoder->decode(
                new EncodedAuthenticationToken(
                    $token
                )
            );

            $this->authorizationService->check(
                $decodedToken->getUserId(),
                SectorTypes::Genre,
                PermissionTypes::Activate
            );

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
                Id::make($id),
                $isActive
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

    public function findById(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $response = $response->withHeader("Content-Type", "application/json");

            $token = $request->getAttribute("token");

            $decodedToken = $this->authenticationTokenDecoder->decode(
                new EncodedAuthenticationToken(
                    $token
                )
            );

            $this->authorizationService->check(
                $decodedToken->getUserId(),
                SectorTypes::Genre,
                PermissionTypes::List
            );

            $missingUriParams = ArrayKeysExistanceChecker::checkAndReturnMissingKeys($args, ["id"]);
            if (count($missingUriParams) > 0) {
                return HttpMissingKeysInformerResponse::getStatusAsArrayOfUriParams($response, $missingUriParams);
            }

            $id = (int)$args["id"];

            $genre = $this->genreService->findById(
                Id::make($id)
            );

            $response
                ->getBody()
                ->write(
                    json_encode([
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

    public function findAll(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $response = $response->withHeader("Content-Type", "application/json");

            $token = $request->getAttribute("token");

            $decodedToken = $this->authenticationTokenDecoder->decode(
                new EncodedAuthenticationToken(
                    $token
                )
            );

            $this->authorizationService->check(
                $decodedToken->getUserId(),
                SectorTypes::Genre,
                PermissionTypes::List
            );

            $genres = $this->genreService->findAll();

            if ($genres->count() === 0) {
                $response
                    ->getBody()
                    ->write(
                        json_encode([
                            "message" => "Nothing found!"
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
