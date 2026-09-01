<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Controller;

use Mvreisg\GamebaseBackend\Application\UserSectorPermission\Service\Dto\UserSectorPermissionServiceInsertDto;
use Mvreisg\GamebaseBackend\Application\UserSectorPermission\Service\Dto\UserSectorPermissionServiceUpdateDto;
use Mvreisg\GamebaseBackend\Application\UserSectorPermission\Service\UserSectorPermissionService;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;
use Mvreisg\GamebaseBackend\Infrastructure\Arrays\ArrayKeysExistanceChecker;
use Mvreisg\GamebaseBackend\Presentation\Http\Util\Response\HttpMissingKeysInformerResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: "UserSectorPermission",
    description: "Endpoints related to game-genre relation management"
)]
class HttpUserSectorPermissionController
{
    private UserSectorPermissionService $userSectorPermissionService;

    public function __construct(UserSectorPermissionService $userSectorPermissionService)
    {
        $this->userSectorPermissionService = $userSectorPermissionService;
    }

    #[OA\Post(
        path: "/user_sector_permission",
        summary: "Inserts a new UserSectorPermission",
        description: "Receives the user credentials and if valid, inserts a UserSectorPermission and returns a copy of the inserted UserSectorPermission.",
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
                    property: "user_id",
                    type: "integer",
                    example: 1
                ),
                new OA\Property(
                    property: "sector_id",
                    type: "integer",
                    example: 1
                ),
                new OA\Property(
                    property: "permission_id",
                    type: "integer",
                    example: 1
                ),
            ]
        )
    )]
    #[OA\Response(
        response: 201,
        description: "Response if credentials is valid and the UserSectorPermission is inserted on the repository",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: "data",
                    type: "object",
                    ref: "#/components/schemas/UserSectorPermission"
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
                            example: ["user_id", "sector_id", "permission_id"],
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
                ["user_id", "sector_id", "permission_id"]
            );
            if (count($missingBodyKeys) > 0) {
                return HttpMissingKeysInformerResponse::getStatusAsArrayOfBodyKeys($response, $missingBodyKeys);
            }

            $userId = $body["user_id"];
            $sectorId = $body["sector_id"];
            $permissionId = $body["permission_id"];

            $userSectorPermission = $this->userSectorPermissionService->insert(
                new UserSectorPermissionServiceInsertDto(
                    Id::create($userId),
                    Id::create($sectorId),
                    Id::create($permissionId)
                ),
                $token
            );

            $response
                ->getBody()
                ->write(
                    json_encode([
                        "data" => [
                            "id" => $userSectorPermission->getId()->getValue(),
                            "user" => [
                                "id" => $userSectorPermission->getUser()->getId()->getValue(),
                                "username" => $userSectorPermission->getUser()->getUsername()->getValue(),
                                "is_active" => $userSectorPermission->getUser()->getIsActive()
                            ],
                            "sector" => [
                                "id" => $userSectorPermission->getSector()->getId()->getValue(),
                                "name" => $userSectorPermission->getSector()->getName()->getValue(),
                                "value" => $userSectorPermission->getSector()->getSectorValue()->getValue(),
                                "is_active" => $userSectorPermission->getSector()->getIsActive(),
                            ],
                            "permission" => [
                                "id" => $userSectorPermission->getPermission()->getId()->getValue(),
                                "name" => $userSectorPermission->getPermission()->getName()->getValue(),
                                "value" => $userSectorPermission->getPermission()->getPermissionValue()->getValue(),
                                "is_active" => $userSectorPermission->getPermission()->getIsActive(),
                            ],
                        ]
                    ])
                );
            return $response->withStatus(201);
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    #[OA\Put(
        path: "/user_sector_permission/{id}",
        summary: "Update a UserSectorPermission",
        description: "Receives the user credentials and if valid, tries to update a UserSectorPermission and returns the update status.",
        tags: ["Update"],
        parameters: [
            new OA\PathParameter(
                name: "id",
                description: "The id of the UserSectorPermission to be updated.",
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
                    property: "user_id",
                    type: "integer",
                    example: 1
                ),
                new OA\Property(
                    property: "sector_id",
                    type: "integer",
                    example: 1
                ),
                new OA\Property(
                    property: "permission_id",
                    type: "integer",
                    example: 1
                ),
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: "Response if credentials is valid and the UserSectorPermission is inserted on the repository",
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
                            example: ["user_id", "sector_id", "permission_id"],
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
                ["user_id", "sector_id", "permission_id"]
            );
            if (count($missingBodyKeys) > 0) {
                return HttpMissingKeysInformerResponse::getStatusAsArrayOfBodyKeys($response, $missingBodyKeys);
            }

            $id = (int) $args["id"];
            $userId = $body["user_id"];
            $sectorId = $body["sector_id"];
            $permissionId = $body["permission_id"];

            $wasUpdated = $this->userSectorPermissionService->update(
                new UserSectorPermissionServiceUpdateDto(
                    Id::create($id),
                    Id::create($userId),
                    Id::create($sectorId),
                    Id::create($permissionId)
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
        path: "/user_sector_permission/{id}",
        summary: "Deletes a UserSectorPermission by its ID",
        description: "Receives the user credentials and if valid, tries to deletes a UserSectorPermission and returns the deletion status.",
        tags: ["Delete"],
        parameters: [
            new OA\PathParameter(
                name: "id",
                description: "The id of the UserSectorPermission to be deleted.",
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
        description: "Response if credentials is valid and the UserSectorPermission is updated on the repository",
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

            $wasDeleted = $this->userSectorPermissionService->delete(
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
        path: "/user_sector_permission/{id}",
        summary: "Returns a UserSectorPermission by its ID",
        description: "Receives the user credentials and if valid, searches for the UserSectorPermission with the ID, and if the UserSectorPermission exists, returns it.",
        tags: ["Get"],
        parameters: [
            new OA\PathParameter(
                name: "id",
                description: "The id of the UserSectorPermission to be searched.",
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
        description: "Response if credentials is valid and the UserSectorPermission with the informed ID exists",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: "data",
                    type: "object",
                    ref: "#/components/schemas/UserSectorPermission"
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

            $userSectorPermission = $this->userSectorPermissionService->findById(
                Id::create($id),
                $token
            );

            if ($userSectorPermission === null) {
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
                            "id" => $userSectorPermission->getId()->getValue(),
                            "user" => [
                                "id" => $userSectorPermission->getUser()->getId()->getValue(),
                                "username" => $userSectorPermission->getUser()->getUsername()->getValue(),
                                "is_active" => $userSectorPermission->getUser()->getIsActive()
                            ],
                            "sector" => [
                                "id" => $userSectorPermission->getSector()->getId()->getValue(),
                                "name" => $userSectorPermission->getSector()->getName()->getValue(),
                                "value" => $userSectorPermission->getSector()->getSectorValue()->getValue(),
                                "is_active" => $userSectorPermission->getSector()->getIsActive(),
                            ],
                            "permission" => [
                                "id" => $userSectorPermission->getPermission()->getId()->getValue(),
                                "name" => $userSectorPermission->getPermission()->getName()->getValue(),
                                "value" => $userSectorPermission->getPermission()->getPermissionValue()->getValue(),
                                "is_active" => $userSectorPermission->getPermission()->getIsActive(),
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
        path: "/user_sector_permission",
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
        description: "Response if credentials is valid and the UserSectorPermission with the informed ID exists",
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
                        ref: "#/components/schemas/UserSectorPermission"
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

            $userSectorPermissions = $this->userSectorPermissionService->findAll(
                $token
            );

            if ($userSectorPermissions === null) {
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
            foreach ($userSectorPermissions->fetchAll() as $userSectorPermission) {
                $data[] = [
                    "id" => $userSectorPermission->getId()->getValue(),
                    "user" => [
                        "id" => $userSectorPermission->getUser()->getId()->getValue(),
                        "username" => $userSectorPermission->getUser()->getUsername()->getValue(),
                        "is_active" => $userSectorPermission->getUser()->getIsActive()
                    ],
                    "sector" => [
                        "id" => $userSectorPermission->getSector()->getId()->getValue(),
                        "name" => $userSectorPermission->getSector()->getName()->getValue(),
                        "value" => $userSectorPermission->getSector()->getSectorValue()->getValue(),
                        "is_active" => $userSectorPermission->getSector()->getIsActive(),
                    ],
                    "permission" => [
                        "id" => $userSectorPermission->getPermission()->getId()->getValue(),
                        "name" => $userSectorPermission->getPermission()->getName()->getValue(),
                        "value" => $userSectorPermission->getPermission()->getPermissionValue()->getValue(),
                        "is_active" => $userSectorPermission->getPermission()->getIsActive(),
                    ],
                ];
            }

            $response
                ->getBody()
                ->write(
                    json_encode([
                        "amount" => $userSectorPermissions->count(),
                        "data" => $data
                    ])
                );
            return $response->withStatus(200);
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
