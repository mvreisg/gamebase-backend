<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Controller;

use Mvreisg\GamebaseBackend\Application\Permission\Service\Dto\PermissionServiceInsertDto;
use Mvreisg\GamebaseBackend\Application\Permission\Service\Dto\PermissionServiceUpdateDto;
use Mvreisg\GamebaseBackend\Application\Permission\Service\PermissionService;
use Mvreisg\GamebaseBackend\Domain\Permission\ValueObject\PermissionValue\PermissionValue;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Name\Name;
use Mvreisg\GamebaseBackend\Infrastructure\Arrays\ArrayKeysExistanceChecker;
use Mvreisg\GamebaseBackend\Presentation\Http\Util\Response\HttpMissingKeysInformerResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: "Permission",
    description: "Endpoints related to Permission management"
)]
class HttpPermissionController
{
    private PermissionService $permissionService;

    public function __construct(
        PermissionService $permissionService
    ) {
        $this->permissionService = $permissionService;
    }

    #[OA\Post(
        path: "/permission",
        summary: "Inserts a new Permission",
        description: "If authenticated, inserts a Permission and returns a copy",
        tags: ["Permission"]
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
                    example: "Create"
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
        description: "Response if authenticated and the Permission exists on the repository",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: "data",
                    type: "object",
                    ref: "#/components/schemas/Permission"
                )
            ]
        )
    )]
    #[OA\Response(
        response: 401,
        description: "Response if not authenticated",
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
        description: "Response if a value is missing or if the Permission does not exist",
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
                            example: ["name", "is_active", "value"],
                            items: new OA\Items(
                                type: "string",
                            )
                        )
                    ]
                ),
                new OA\Schema(
                    title: "Permission not found",
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
                ["name", "is_active", "value"]
            );
            if (count($missingBodyKeys) > 0) {
                return HttpMissingKeysInformerResponse::getStatusAsArrayOfBodyKeys($response, $missingBodyKeys);
            }

            $name = $body["name"];
            $isActive = $body["is_active"];
            $value = $body["value"];

            $permission = $this->permissionService->insert(
                new PermissionServiceInsertDto(
                    Name::create($name),
                    PermissionValue::create($value),
                    $isActive
                ),
                $token
            );

            $data = [
                "id" => $permission->getId()->getValue(),
                "name" => $permission->getName()->getValue(),
                "is_active" => $permission->getIsActive(),
                "value" => $permission->getPermissionValue()->getValue()
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
        path: "/permission/{id}",
        summary: "Update a Permission",
        description: "If authenticated, update a Permission and returns the status",
        tags: ["Permission"],
        parameters: [
            new OA\PathParameter(
                name: "id",
                description: "The id of the Permission to be updated",
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
                    example: "Create"
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
        description: "Response if authenticated and the Permission is updated on the repository",
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
        description: "Response if not authenticated",
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
        description: "Response if a value is missing or if the Permission does not exist",
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
                            example: ["name", "is_active", "value"],
                            items: new OA\Items(
                                type: "string",
                            )
                        )
                    ]
                ),
                new OA\Schema(
                    title: "Permission not found",
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
                ["name", "is_active", "value"]
            );
            if (count($missingBodyKeys) > 0) {
                return HttpMissingKeysInformerResponse::getStatusAsArrayOfBodyKeys($response, $missingBodyKeys);
            }

            $id = (int)$args["id"];
            $name = $body["name"];
            $isActive = $body["is_active"];
            $value = $body["value"];

            $wasUpdated = $this->permissionService->update(
                new PermissionServiceUpdateDto(
                    Id::create($id),
                    Name::create($name),
                    PermissionValue::create($value),
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
        path: "/permission/{id}",
        summary: "Set activation status of a Permission by its ID",
        description: "If authenticated, sets the activation status and returns the status",
        tags: ["Permission"],
        parameters: [
            new OA\PathParameter(
                name: "id",
                description: "The id of the Permission to be updated",
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
        description: "Response if authenticated and the Permission is updated on the repository",
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
        description: "Response if not authenticated",
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
        description: "Response if a value is missing or if the Permission does not exist",
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
                    title: "Permission not found",
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

            $wasUpdated = $this->permissionService->setIsActive(
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
        path: "/permission/{id}",
        summary: "Returns a Permission by its ID",
        description: "If authenticated, searches for the Permission with the ID, and if exists, returns it",
        tags: ["Permission"],
        parameters: [
            new OA\PathParameter(
                name: "id",
                description: "The id of the Permission to be searched",
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
        description: "Response if authenticated and the Permission with the informed ID exists",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: "data",
                    type: "object",
                    ref: "#/components/schemas/Permission"
                )
            ]
        )
    )]
    #[OA\Response(
        response: 401,
        description: "Response if not authenticated",
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
        description: "Response if the Permission does not exist",
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

            $permission = $this->permissionService->findById(
                Id::create($id),
                $token
            );

            if ($permission === null) {
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
                            "id" => $permission->getId()->getValue(),
                            "name" => $permission->getName()->getValue(),
                            "value" => $permission->getPermissionValue()->getValue(),
                            "is_active" => $permission->getIsActive()
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
        path: "/permission",
        summary: "Returns all the Permissions on the repository",
        description: "If authenticated, returns all the existant Permissions",
        tags: ["Permission"],
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
        description: "Response if authenticated",
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
                        ref: "#/components/schemas/Permission"
                    )
                ),
            ]
        )
    )]
    #[OA\Response(
        response: 401,
        description: "Response if not authenticated",
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
        description: "Response if no Permissions were found",
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

            $permissions = $this->permissionService->findAll(
                $token
            );

            if ($permissions === null) {
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
            foreach ($permissions->fetchAll() as $permission) {
                $data[] = [
                    "id" => $permission->getId()->getValue(),
                    "name" => $permission->getName()->getValue(),
                    "value" => $permission->getPermissionValue()->getValue(),
                    "is_active" => $permission->getIsActive()
                ];
            }

            $response
                ->getBody()
                ->write(
                    json_encode([
                        "amount" => $permissions->count(),
                        "data" => $data
                    ])
                );
            return $response->withStatus(200);
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
