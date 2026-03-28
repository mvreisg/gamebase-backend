<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Controllers;

use Mvreisg\GamebaseBackend\Application\Services\Authorization\AuthorizationService;
use Mvreisg\GamebaseBackend\Application\Services\Game\GameService;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\Action\Decoder\AuthenticationTokenDecoder;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\Data\Encoded\EncodedAuthenticationToken;
use Mvreisg\GamebaseBackend\Domain\Authorization\Types\Permission\PermissionTypes;
use Mvreisg\GamebaseBackend\Domain\Authorization\Types\Sector\SectorTypes;
use Mvreisg\GamebaseBackend\Domain\Entities\Game;
use Mvreisg\GamebaseBackend\Domain\Entities\Id;
use Mvreisg\GamebaseBackend\Domain\Entities\Name;
use Mvreisg\GamebaseBackend\Domain\Utils\Arrays\ArrayKeysExistanceChecker;
use Mvreisg\GamebaseBackend\Presentation\Http\Utils\Response\HttpMissingKeysInformerResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class HttpGameController
{
    private GameService $gameService;
    private AuthorizationService $authorizationService;
    private AuthenticationTokenDecoder $authenticationTokenDecoder;

    public function __construct(
        GameService $gameService,
        AuthorizationService $authorizationService,
        AuthenticationTokenDecoder $authenticationTokenDecoder
    ) {
        $this->gameService = $gameService;
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
                SectorTypes::Game,
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

            $game = $this->gameService->insert(
                new Game(
                    Name::make($name),
                    $isActive
                )
            );

            $data = [
                "id" => $game->getId()->getValue(),
                "name" => $game->getName()->getValue(),
                "is_active" => $game->getIsActive()
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
                SectorTypes::Game,
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

            $game = new Game(
                Name::make($name),
                $isActive
            );
            $game->setId(Id::make($id));

            $wasUpdated = $this->gameService->update(
                $game
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
                SectorTypes::Game,
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

            $wasUpdated = $this->gameService->setIsActive(
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
                SectorTypes::Game,
                PermissionTypes::List
            );

            $missingUriParams = ArrayKeysExistanceChecker::checkAndReturnMissingKeys($args, ["id"]);
            if (count($missingUriParams) > 0) {
                return HttpMissingKeysInformerResponse::getStatusAsArrayOfUriParams($response, $missingUriParams);
            }

            $id = (int)$args["id"];

            $game = $this->gameService->findById(
                Id::make($id)
            );

            $response
                ->getBody()
                ->write(
                    json_encode([
                        "data" => [
                            "id" => $game->getId()->getValue(),
                            "name" => $game->getName()->getValue(),
                            "is_active" => $game->getIsActive()
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
                SectorTypes::Game,
                PermissionTypes::List
            );

            $games = $this->gameService->findAll();

            if ($games->count() === 0) {
                $response
                    ->getBody()
                    ->write(
                        json_encode([
                            "message" => "Nothing found!"
                        ])
                    );
                return $response->withStatus(404);
            }

            foreach ($games->fetchAll() as $game) {
                $data[] = [
                    "id" => $game->getId()->getValue(),
                    "name" => $game->getName()->getValue(),
                    "is_active" => $game->getIsActive()
                ];
            }

            $response
                ->getBody()
                ->write(
                    json_encode([
                        "number_found" => $games->count(),
                        "data" => $data
                    ])
                );
            return $response->withStatus(200);
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
