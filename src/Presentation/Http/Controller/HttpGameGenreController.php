<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Controller;

use Mvreisg\GamebaseBackend\Application\GameGenre\Service\Dto\GameGenreServiceInsertDto;
use Mvreisg\GamebaseBackend\Application\GameGenre\Service\Dto\GameGenreServiceUpdateDto;
use Mvreisg\GamebaseBackend\Application\GameGenre\Service\GameGenreService;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;
use Mvreisg\GamebaseBackend\Infrastructure\Arrays\ArrayKeysExistanceChecker;
use Mvreisg\GamebaseBackend\Presentation\Http\Util\Response\HttpMissingKeysInformerResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: "GameGenre",
    description: "Endpoints related to game-genre relation management"
)]
class HttpGameGenreController
{
    private GameGenreService $gameGenreService;

    public function __construct(GameGenreService $gameGenreService)
    {
        $this->gameGenreService = $gameGenreService;
    }

    #[OA\Post(
        path: "/game_genre",
        summary: "Inserts a new GameGenre",
        description: "Receives the user credentials and if valid, inserts a GameGenre and returns a copy of the inserted GameGenre.",
        tags: ["Insert"]
    )]
    #[OA\Parameter(
        name: "Authorization",
        in: "header",
        required: true,
        description: "Bearer token",
        schema: new OA\Schema(
            type: "string"
        )
    )]
    #[OA\RequestBody(
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: "game_id",
                    type: "integer",
                    example: 1
                ),
                new OA\Property(
                    property: "genre_id",
                    type: "integer",
                    example: 1
                ),
            ]
        )
    )]
    #[OA\Response(
        response: 201,
        description: "Response if credentials is valid and the GameGenre is inserted on the repository",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: "data",
                    type: "object",
                    ref: "#/components/schemas/GameGenre"
                )
            ]
        )
    )]
    #[OA\Response(
        response: 401,
        description: "Response if user does not have credentials",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: "message",
                    type: "string",
                ),
            ]
        )
    )]
    #[OA\Response(
        response: 404,
        description: "Response if a body value is missing or if the user does not exist",
        content: new OA\JsonContent(
            oneOf: [
                new OA\Schema(
                    title: "Missing keys",
                    properties: [
                        new OA\Property(
                            property: "message",
                            type: "string",
                            example: "Missing body keys: "
                        ),
                        new OA\Property(
                            property: "body",
                            type: "array",
                            example: ["game_id", "genre_id"],
                            items: new OA\Items(
                                type: "integer",
                            )
                        )
                    ]
                ),
                new OA\Schema(
                    title: "User not found",
                    properties: [
                        new OA\Property(
                            property: "message",
                            type: "string",
                        )
                    ]
                )
            ]
        )
    )]
    #[OA\Response(
        response: 500,
        description: "Response if a internal server error occurs",
        content: new OA\JsonContent(
            oneOf: [
                new OA\Schema(
                    title: "Encryption error",
                    properties: [
                        new OA\Property(
                            property: "message",
                            type: "string",
                        ),
                    ]
                ),
                new OA\Schema(
                    title: "Authentication token cache exception",
                    properties: [
                        new OA\Property(
                            property: "message",
                            type: "string",
                        )
                    ]
                ),
                new OA\Schema(
                    title: "Authentication token provider exception",
                    properties: [
                        new OA\Property(
                            property: "message",
                            type: "string",
                        )
                    ]
                )
            ]
        )
    )]
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
                new GameGenreServiceInsertDto(
                    Id::create($gameId),
                    Id::create($genreId),
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

    #[OA\Put(
        path: "/game_genre/{id}",
        summary: "Update a GameGenre",
        description: "Receives the user credentials and if valid, tries to update a GameGenre and returns the update status.",
        tags: ["Update"],
        parameters: [
            new OA\PathParameter(
                name: "id",
                description: "The id of the GameGenre to be updated.",
                required: true,
                schema: new OA\Schema(
                    type: "integer"
                )
            )
        ]
    )]
    #[OA\Parameter(
        name: "Authorization",
        in: "header",
        required: true,
        description: "Bearer token",
        schema: new OA\Schema(
            type: "string"
        )
    )]
    #[OA\RequestBody(
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: "game_id",
                    type: "integer",
                    example: 1
                ),
                new OA\Property(
                    property: "genre_id",
                    type: "integer",
                    example: 1
                ),
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: "Response if credentials is valid and the GameGenre is inserted on the repository",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: "status",
                    type: "string",
                ),
            ]
        )
    )]
    #[OA\Response(
        response: 401,
        description: "Response if user does not have credentials",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: "message",
                    type: "string",
                ),
            ]
        )
    )]
    #[OA\Response(
        response: 404,
        description: "Response if a body value is missing or if the user does not exist",
        content: new OA\JsonContent(
            oneOf: [
                new OA\Schema(
                    title: "Missing keys",
                    properties: [
                        new OA\Property(
                            property: "message",
                            type: "string",
                            example: "Missing body keys: "
                        ),
                        new OA\Property(
                            property: "body",
                            type: "array",
                            example: ["game_id", "genre_id"],
                            items: new OA\Items(
                                type: "integer",
                            )
                        )
                    ]
                ),
                new OA\Schema(
                    title: "User not found",
                    properties: [
                        new OA\Property(
                            property: "message",
                            type: "string",
                        )
                    ]
                )
            ]
        )
    )]
    #[OA\Response(
        response: 500,
        description: "Response if a internal server error occurs",
        content: new OA\JsonContent(
            oneOf: [
                new OA\Schema(
                    title: "Encryption error",
                    properties: [
                        new OA\Property(
                            property: "message",
                            type: "string",
                        ),
                    ]
                ),
                new OA\Schema(
                    title: "Authentication token cache exception",
                    properties: [
                        new OA\Property(
                            property: "message",
                            type: "string",
                        )
                    ]
                ),
                new OA\Schema(
                    title: "Authentication token provider exception",
                    properties: [
                        new OA\Property(
                            property: "message",
                            type: "string",
                        )
                    ]
                )
            ]
        )
    )]
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

            $wasUpdated = $this->gameGenreService->update(
                new GameGenreServiceUpdateDto(
                    Id::create($id),
                    Id::create($gameId),
                    Id::create($genreId),
                ),
                $token
            );

            $response
                ->getBody()
                ->write(
                    json_encode([
                        "status" => $wasUpdated ? "updated" : "same"
                    ])
                );
            return $response->withStatus(200);
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    #[OA\Delete(
        path: "/game_genre/{id}",
        summary: "Deletes a GameGenre by its ID",
        description: "Receives the user credentials and if valid, tries to deletes a GameGenre and returns the deletion status.",
        tags: ["Delete"],
        parameters: [
            new OA\PathParameter(
                name: "id",
                description: "The id of the GameGenre to be deleted.",
                required: true,
                schema: new OA\Schema(
                    type: "integer"
                )
            )
        ]
    )]
    #[OA\Parameter(
        name: "Authorization",
        in: "header",
        required: true,
        description: "Bearer token",
        schema: new OA\Schema(
            type: "string"
        )
    )]
    #[OA\Response(
        response: 200,
        description: "Response if credentials is valid and the GameGenre is updated on the repository",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: "status",
                    type: "string",
                ),
            ]
        )
    )]
    #[OA\Response(
        response: 401,
        description: "Response if user does not have credentials",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: "message",
                    type: "string",
                ),
            ]
        )
    )]
    #[OA\Response(
        response: 404,
        description: "Response if a body value is missing or if the user does not exist",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: "message",
                    type: "string",
                )
            ]
        )
    )]
    #[OA\Response(
        response: 500,
        description: "Response if a internal server error occurs",
        content: new OA\JsonContent(
            oneOf: [
                new OA\Schema(
                    title: "Encryption error",
                    properties: [
                        new OA\Property(
                            property: "message",
                            type: "string",
                        ),
                    ]
                ),
                new OA\Schema(
                    title: "Authentication token cache exception",
                    properties: [
                        new OA\Property(
                            property: "message",
                            type: "string",
                        )
                    ]
                ),
                new OA\Schema(
                    title: "Authentication token provider exception",
                    properties: [
                        new OA\Property(
                            property: "message",
                            type: "string",
                        )
                    ]
                )
            ]
        )
    )]
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
            return $response->withStatus(200);
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    #[OA\Get(
        path: "/game_genre/{id}",
        summary: "Returns a GameGenre by its ID",
        description: "Receives the user credentials and if valid, searches for the GameGenre with the ID, and if the GameGenre exists, returns it.",
        tags: ["Get"],
        parameters: [
            new OA\PathParameter(
                name: "id",
                description: "The id of the GameGenre to be searched.",
                required: true,
                schema: new OA\Schema(
                    type: "integer"
                )
            )
        ]
    )]
    #[OA\Parameter(
        name: "Authorization",
        in: "header",
        required: true,
        description: "Bearer token",
        schema: new OA\Schema(
            type: "string"
        )
    )]
    #[OA\Response(
        response: 200,
        description: "Response if credentials is valid and the GameGenre with the informed ID exists",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: "data",
                    type: "object",
                    ref: "#/components/schemas/GameGenre"
                )
            ]
        )
    )]
    #[OA\Response(
        response: 401,
        description: "Response if user does not have credentials",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: "message",
                    type: "string",
                ),
            ]
        )
    )]
    #[OA\Response(
        response: 404,
        description: "Response if a body value is missing or if the user does not exist",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: "found",
                    type: "boolean",
                    example: false
                ),
            ]
        )
    )]
    #[OA\Response(
        response: 500,
        description: "Response if a internal server error occurs",
        content: new OA\JsonContent(
            oneOf: [
                new OA\Schema(
                    title: "Encryption error",
                    properties: [
                        new OA\Property(
                            property: "message",
                            type: "string",
                        ),
                    ]
                ),
                new OA\Schema(
                    title: "Authentication token cache exception",
                    properties: [
                        new OA\Property(
                            property: "message",
                            type: "string",
                        )
                    ]
                ),
                new OA\Schema(
                    title: "Authentication token provider exception",
                    properties: [
                        new OA\Property(
                            property: "message",
                            type: "string",
                        )
                    ]
                )
            ]
        )
    )]
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
            return $response->withStatus(200);
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    #[OA\Get(
        path: "/game_genre",
        summary: "Returns all the GameGenres on the repository",
        description: "Receives the user credentials and if valid, returns all the existant GameGenres.",
        tags: ["Get", "All"],
    )]
    #[OA\Parameter(
        name: "Authorization",
        in: "header",
        required: true,
        description: "Bearer token",
        schema: new OA\Schema(
            type: "string"
        )
    )]
    #[OA\Response(
        response: 200,
        description: "Response if credentials is valid and the GameGenre with the informed ID exists",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: "amount",
                    type: "integer",
                ),
                new OA\Property(
                    property: "data",
                    type: "array",
                    items: new OA\Items(
                        ref: "#/components/schemas/GameGenre"
                    )
                ),
            ]
        )
    )]
    #[OA\Response(
        response: 401,
        description: "Response if user does not have credentials",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: "message",
                    type: "string",
                ),
            ]
        )
    )]
    #[OA\Response(
        response: 404,
        description: "Response if a body value is missing or if the user does not exist",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: "amount",
                    type: "integer",
                    example: 0
                ),
            ]
        )
    )]
    #[OA\Response(
        response: 500,
        description: "Response if a internal server error occurs",
        content: new OA\JsonContent(
            oneOf: [
                new OA\Schema(
                    title: "Encryption error",
                    properties: [
                        new OA\Property(
                            property: "message",
                            type: "string",
                        ),
                    ]
                ),
                new OA\Schema(
                    title: "Authentication token cache exception",
                    properties: [
                        new OA\Property(
                            property: "message",
                            type: "string",
                        )
                    ]
                ),
                new OA\Schema(
                    title: "Authentication token provider exception",
                    properties: [
                        new OA\Property(
                            property: "message",
                            type: "string",
                        )
                    ]
                )
            ]
        )
    )]
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
                            "amount" => 0
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
                        "amount" => $gameGenres->count(),
                        "data" => $data
                    ])
                );
            return $response->withStatus(200);
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
