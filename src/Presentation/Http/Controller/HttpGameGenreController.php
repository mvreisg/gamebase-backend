<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Controller;

use Mvreisg\GamebaseBackend\Application\GameGenre\Service\GameGenreService;
use Mvreisg\GamebaseBackend\Domain\Game\Entity\Game;
use Mvreisg\GamebaseBackend\Domain\GameGenre\Entity\GameGenre;
use Mvreisg\GamebaseBackend\Domain\Genre\Entity\Genre;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;
use Mvreisg\GamebaseBackend\Infrastructure\Arrays\ArrayKeysExistanceChecker;
use Mvreisg\GamebaseBackend\Presentation\Http\Util\Response\HttpMissingKeysInformerResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class HttpGameGenreController
{
    private GameGenreService $gameGenreService;

    public function __construct(GameGenreService $gameGenreService)
    {
        $this->gameGenreService = $gameGenreService;
    }

    public function insert(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $response = $response->withHeader("Content-Type", "application/json");

            $token = $request->getAttribute("token");

            $body = $request->getParsedBody();

            $missingBodyKeys = ArrayKeysExistanceChecker::checkAndReturnMissingKeys(
                $body,
                ["game_id", "genre_id"]
            );
            if (count($missingBodyKeys) > 0) {
                return HttpMissingKeysInformerResponse::getStatusAsArrayOfBodyKeys($response, $missingBodyKeys);
            }

            $gameId = $body["game_id"];
            $genreId = $body["genre_id"];

            $gameGenre = $this->gameGenreService->insert(
                GameGenre::create(
                    null,
                    Game::createFromIdOnly(
                        Id::create(
                            $gameId
                        )
                    ),
                    Genre::createFromIdOnly(
                        Id::create(
                            $genreId
                        )
                    )
                ),
                $token
            );

            $data = [
                "id" => $gameGenre->getId()->getValue(),
                "game" => [
                    "id" => $gameGenre->getGame()->getId()->getValue(),
                    "name" => $gameGenre->getGame()->getName()->getValue(),
                    "is_active" => $gameGenre->getGame()->getIsActive(),
                ],
                "genre" => [
                    "id" => $gameGenre->getGenre()->getId()->getValue(),
                    "name" => $gameGenre->getGenre()->getName()->getValue(),
                    "is_active" => $gameGenre->getGenre()->getIsActive(),
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
                ["game_id", "genre_id"]
            );
            if (count($missingBodyKeys) > 0) {
                return HttpMissingKeysInformerResponse::getStatusAsArrayOfBodyKeys($response, $missingBodyKeys);
            }

            $id = (int)$args["id"];
            $gameId = $body["game_id"];
            $genreId = $body["genre_id"];

            $gameGenre = GameGenre::create(
                Id::create($id),
                Game::createFromIdOnly(
                    Id::create(
                        $gameId
                    )
                ),
                Genre::createFromIdOnly(
                    Id::create(
                        $genreId
                    )
                )
            );

            $wasUpdated = $this->gameGenreService->update(
                $gameGenre,
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

            $wasDeleted = $this->gameGenreService->delete(
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

            $gameGenre = $this->gameGenreService->findById(
                Id::create($id),
                $token
            );

            if ($gameGenre === null) {
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
                            "id" => $gameGenre->getId()->getValue(),
                            "game" => [
                                "id" => $gameGenre->getGame()->getId()->getValue(),
                                "name" => $gameGenre->getGame()->getName()->getValue(),
                                "is_active" => $gameGenre->getGame()->getIsActive(),
                            ],
                            "genre" => [
                                "id" => $gameGenre->getGenre()->getId()->getValue(),
                                "name" => $gameGenre->getGenre()->getName()->getValue(),
                                "is_active" => $gameGenre->getGenre()->getIsActive(),
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

    public function findAll(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        try {
            $response = $response->withHeader("Content-Type", "application/json");

            $token = $request->getAttribute("token");

            $gameGenres = $this->gameGenreService->findAll(
                $token
            );

            if ($gameGenres === null) {
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
            foreach ($gameGenres->fetchAll() as $gameGenre) {
                $data[] = [
                    "id" => $gameGenre->getId()->getValue(),
                    "game" => [
                        "id" => $gameGenre->getGame()->getId()->getValue(),
                        "name" => $gameGenre->getGame()->getName()->getValue(),
                        "is_active" => $gameGenre->getGame()->getIsActive(),
                    ],
                    "genre" => [
                        "id" => $gameGenre->getGenre()->getId()->getValue(),
                        "name" => $gameGenre->getGenre()->getName()->getValue(),
                        "is_active" => $gameGenre->getGenre()->getIsActive(),
                    ]
                ];
            }

            $response
                ->getBody()
                ->write(
                    json_encode([
                        "number_found" => $gameGenres->count(),
                        "data" => $data
                    ])
                );
            return $response->withStatus(200);
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
