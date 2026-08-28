<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Controller;

use Mvreisg\GamebaseBackend\Application\Genre\Service\Dto\GenreServiceInsertDto;
use Mvreisg\GamebaseBackend\Application\Genre\Service\Dto\GenreServiceUpdateDto;
use Mvreisg\GamebaseBackend\Application\Genre\Service\GenreService;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Name\Name;
use Mvreisg\GamebaseBackend\Infrastructure\Arrays\ArrayKeysExistanceChecker;
use Mvreisg\GamebaseBackend\Presentation\Http\Util\Response\HttpMissingKeysInformerResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: "Genre",
    description: "Endpoints related to genre management"
)]
class HttpGenreController
{
    private GenreService $genreService;

    public function __construct(GenreService $genreService)
    {
        $this->genreService = $genreService;
    }

    #[OA\Post(
        path: "/genre",
        summary: "Inserts a new Genre",
        description: "Receives the user credentials and if valid, inserts a Genre and returns a copy of the inserted Genre.",
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
                    property: "name",
                    type: "string",
                    example: "RPG (Role-playing game)"
                ),
                new OA\Property(
                    property: "is_active",
                    type: "boolean",
                    example: true
                ),
            ]
        )
    )]
    #[OA\Response(
        response: 201,
        description: "Response if credentials is valid and the Genre is inserted on the repository",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: "data",
                    type: "object",
                    ref: "#/components/schemas/Genre"
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
                            example: ["name", "is_active"],
                            items: new OA\Items(
                                type: "string",
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
                ["name", "is_active"]
            );
            if (count($missingBodyKeys) > 0) {
                return HttpMissingKeysInformerResponse::getStatusAsArrayOfBodyKeys($response, $missingBodyKeys);
            }

            $name = $body["name"];
            $isActive = $body["is_active"];

            $genre = $this->genreService->insert(
                new GenreServiceInsertDto(
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

    #[OA\Put(
        path: "/genre/{id}",
        summary: "Update a Genre",
        description: "Receives the user credentials and if valid, tries to update a Genre and returns the update status.",
        tags: ["Update"],
        parameters: [
            new OA\PathParameter(
                name: "id",
                description: "The id of the Genre to be updated.",
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
                    property: "name",
                    type: "string",
                    example: "RPG (Role-playing game)"
                ),
                new OA\Property(
                    property: "is_active",
                    type: "boolean",
                    example: true
                ),
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: "Response if credentials is valid and the Genre is inserted on the repository",
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
                            example: ["name", "is_active"],
                            items: new OA\Items(
                                type: "string",
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
                ["name", "is_active"]
            );
            if (count($missingBodyKeys) > 0) {
                return HttpMissingKeysInformerResponse::getStatusAsArrayOfBodyKeys($response, $missingBodyKeys);
            }

            $id = (int)$args["id"];
            $name = $body["name"];
            $isActive = $body["is_active"];

            $wasUpdated = $this->genreService->update(
                new GenreServiceUpdateDto(
                    Id::create($id),
                    Name::create($name),
                    $isActive
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
            return $response
                ->withStatus(200);
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    #[OA\Patch(
        path: "/genre/{id}",
        summary: "Activates/Deactivates a Genre by its ID",
        description: "Receives the user credentials and if valid, tries to activate/deactivate a Genre and returns the activation status.",
        tags: ["Activate", "Deactivate"],
        parameters: [
            new OA\PathParameter(
                name: "id",
                description: "The id of the Genre to be activated/deactivated.",
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
                    property: "is_active",
                    type: "boolean",
                    example: true
                ),
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: "Response if credentials is valid and the Genre is updated on the repository",
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
                            example: ["is_active"],
                            items: new OA\Items(
                                type: "string",
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

    #[OA\Get(
        path: "/genre/{id}",
        summary: "Returns a Genre by its ID",
        description: "Receives the user credentials and if valid, searches for the Genre with the ID, and if the Genre exists, returns it.",
        tags: ["Get"],
        parameters: [
            new OA\PathParameter(
                name: "id",
                description: "The id of the Genre to be searched.",
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
        description: "Response if credentials is valid and the Genre with the informed ID exists",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: "data",
                    type: "object",
                    ref: "#/components/schemas/Genre"
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
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    #[OA\Get(
        path: "/genre",
        summary: "Returns all the Genres on the repository",
        description: "Receives the user credentials and if valid, returns all the existant Genres.",
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
        description: "Response if credentials is valid and the Genre with the informed ID exists",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: "number_found",
                    type: "integer",
                ),
                new OA\Property(
                    property: "data",
                    type: "array",
                    items: new OA\Items(
                        ref: "#/components/schemas/Genre"
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
                    property: "number_found",
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

            $data = [];
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
