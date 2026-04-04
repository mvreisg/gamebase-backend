<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Controller;

use Mvreisg\GamebaseBackend\Application\GamePlatform\Service\GamePlatformService;
use Mvreisg\GamebaseBackend\Domain\Game\Entity\Game;
use Mvreisg\GamebaseBackend\Domain\GamePlatform\Entity\GamePlatform;
use Mvreisg\GamebaseBackend\Domain\Platform\Entity\Platform;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;
use Mvreisg\GamebaseBackend\Infrastructure\Arrays\ArrayKeysExistanceChecker;
use Mvreisg\GamebaseBackend\Presentation\Http\Util\Response\HttpMissingKeysInformerResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class HttpGamePlatformController
{
    private GamePlatformService $gamePlatformService;

    public function __construct(GamePlatformService $gamePlatformService)
    {
        $this->gamePlatformService = $gamePlatformService;
    }

    public function insert(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $response = $response->withHeader("Content-Type", "application/json");

            $token = $request->getAttribute("token");

            $body = $request->getParsedBody();

            $missingBodyKeys = ArrayKeysExistanceChecker::checkAndReturnMissingKeys(
                $body,
                ["game_id", "platform_id"]
            );
            if (count($missingBodyKeys) > 0) {
                return HttpMissingKeysInformerResponse::getStatusAsArrayOfBodyKeys($response, $missingBodyKeys);
            }

            $gameId = $body["game_id"];
            $platformId = $body["platform_id"];

            $gamePlatform = $this->gamePlatformService->insert(
                new GamePlatform(
                    new Game(
                        Id::create(
                            $gameId
                        )
                    ),
                    new Platform(
                        Id::create(
                            $platformId
                        )
                    )
                ),
                $token
            );

            $data = [
                "id" => $gamePlatform->getId()->getValue(),
                "game" => [
                    "id" => $gamePlatform->getGame()->getId()->getValue(),
                    "name" => $gamePlatform->getGame()->getName()->getValue(),
                    "is_active" => $gamePlatform->getGame()->getIsActive(),
                ],
                "platform" => [
                    "id" => $gamePlatform->getPlatform()->getId()->getValue(),
                    "name" => $gamePlatform->getPlatform()->getName()->getValue(),
                    "is_active" => $gamePlatform->getPlatform()->getIsActive(),
                ],
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
                ["game_id", "platform_id"]
            );
            if (count($missingBodyKeys) > 0) {
                return HttpMissingKeysInformerResponse::getStatusAsArrayOfBodyKeys($response, $missingBodyKeys);
            }

            $id = (int)$args["id"];
            $gameId = $body["game_id"];
            $platformId = $body["platform_id"];

            $gamePlatform = new GamePlatform(
                new Game(
                    Id::create(
                        $gameId
                    )
                ),
                new Platform(
                    Id::create(
                        $platformId
                    )
                )
            );
            $gamePlatform->setId(
                Id::create(
                    $id
                )
            );

            $wasUpdated = $this->gamePlatformService->update(
                $gamePlatform,
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

    public function delete(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $response = $response->withHeader("Content-Type", "application/json");

            $token = $request->getAttribute("token");

            $missingUriParams = ArrayKeysExistanceChecker::checkAndReturnMissingKeys($args, ["id"]);
            if (count($missingUriParams) > 0) {
                return HttpMissingKeysInformerResponse::getStatusAsArrayOfUriParams($response, $missingUriParams);
            }

            $id = (int)$args["id"];

            $wasDeleted = $this->gamePlatformService->delete(
                Id::create($id),
                $token
            );

            $response
                ->getBody()
                ->write(
                    json_encode([
                        "status" => $wasDeleted ? "deleted" : "not_deleted"
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

            $missingUriParams = ArrayKeysExistanceChecker::checkAndReturnMissingKeys($args, ["id"]);
            if (count($missingUriParams) > 0) {
                return HttpMissingKeysInformerResponse::getStatusAsArrayOfUriParams($response, $missingUriParams);
            }

            $id = (int)$args["id"];

            $gamePlatform = $this->gamePlatformService->findById(
                Id::create($id),
                $token
            );

            if ($gamePlatform === null) {
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
                        "data" => [
                            "id" => $gamePlatform->getId()->getValue(),
                            "game" => [
                                "id" => $gamePlatform->getGame()->getId()->getValue(),
                                "name" => $gamePlatform->getGame()->getName()->getValue(),
                                "is_active" => $gamePlatform->getGame()->getIsActive(),
                            ],
                            "platform" => [
                                "id" => $gamePlatform->getPlatform()->getId()->getValue(),
                                "name" => $gamePlatform->getPlatform()->getName()->getValue(),
                                "is_active" => $gamePlatform->getPlatform()->getIsActive(),
                            ],
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

            $gamePlatforms = $this->gamePlatformService->findAll(
                $token
            );

            if ($gamePlatforms === null) {
                $response
                    ->getBody()
                    ->write(
                        json_encode([
                            "number_found" => 0
                        ])
                    );
                return $response->withStatus(404);
            }

            $data = [];
            foreach ($gamePlatforms->fetchAll() as $gamePlatform) {
                $data[] = [
                    "id" => $gamePlatform->getId()->getValue(),
                    "game" => [
                        "id" => $gamePlatform->getGame()->getId()->getValue(),
                        "name" => $gamePlatform->getGame()->getName()->getValue(),
                        "is_active" => $gamePlatform->getGame()->getIsActive(),
                    ],
                    "platform" => [
                        "id" => $gamePlatform->getPlatform()->getId()->getValue(),
                        "name" => $gamePlatform->getPlatform()->getName()->getValue(),
                        "is_active" => $gamePlatform->getPlatform()->getIsActive(),
                    ],
                ];
            }

            $response
                ->getBody()
                ->write(
                    json_encode([
                        "number_found" => $gamePlatforms->count(),
                        "data" => $data
                    ])
                );
            return $response->withStatus(200);
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
