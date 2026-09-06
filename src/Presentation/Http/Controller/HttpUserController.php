<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Controller;

use Mvreisg\GamebaseBackend\Application\User\Service\Dto\UserServiceInsertDto;
use Mvreisg\GamebaseBackend\Application\User\Service\Dto\UserServiceUpdateDto;
use Mvreisg\GamebaseBackend\Application\User\Service\UserService;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;
use Mvreisg\GamebaseBackend\Domain\User\Entity\SafeUser;
use Mvreisg\GamebaseBackend\Domain\User\ValueObject\Password\Decoded\DecodedPassword;
use Mvreisg\GamebaseBackend\Domain\User\ValueObject\Username\Username;
use Mvreisg\GamebaseBackend\Infrastructure\Arrays\ArrayKeysExistanceChecker;
use Mvreisg\GamebaseBackend\Presentation\Http\Util\Response\HttpMissingKeysInformerResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: "User",
    description: "Endpoints related to User management"
)]
class HttpUserController
{
    private UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    #[OA\Post(
        path: "/user",
        summary: "Inserts a new User",
        description: "If authenticated, inserts a User and returns a copy",
        tags: ["User"]
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
                    example: "Marcus"
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
        description: "Response if authenticated and the User exists on the repository",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: "data",
                    type: "object",
                    ref: "#/components/schemas/User"
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
        description: "Response if a value is missing or if the User does not exist",
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
            properties: [
                new OA\Property(
                    property: "message",
                    type: "string",
                ),
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
                ["username", "password", "is_active"]
            );
            if (count($missingBodyKeys) > 0) {
                return HttpMissingKeysInformerResponse::getStatusAsArrayOfBodyKeys($response, $missingBodyKeys);
            }

            $username = $body["username"];
            $password = $body["password"];
            $isActive = $body["is_active"];

            $user = SafeUser::create(
                $this->userService->insert(
                    new UserServiceInsertDto(
                        Username::create($username),
                        DecodedPassword::create($password),
                        $isActive
                    ),
                    $token
                )
            );

            $data = [
                "id" => $user->getId()->getValue(),
                "username" => $user->getUsername()->getValue(),
                "is_active" => $user->getIsActive()
            ];

            $response
                ->getBody()
                ->write(
                    json_encode($data)
                );
            return $response->withStatus(201);
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    #[OA\Put(
        path: "/user/{id}",
        summary: "Update a User",
        description: "If authenticated, update a User and returns the status",
        tags: ["User"],
        parameters: [
            new OA\PathParameter(
                name: "id",
                description: "The id of the User to be updated",
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
                    example: "Marcus"
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
        description: "Response if authenticated and the User is updated on the repository",
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
        description: "Response if a value is missing or if the User does not exist",
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
            properties: [
                new OA\Property(
                    property: "message",
                    type: "string",
                ),
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
                ["username", "password", "is_active"]
            );
            if (count($missingBodyKeys) > 0) {
                return HttpMissingKeysInformerResponse::getStatusAsArrayOfBodyKeys($response, $missingBodyKeys);
            }

            $id = (int)$args["id"];
            $username = $body["username"];
            $password = $body["password"];
            $isActive = $body["is_active"];


            $wasUpdated = $this->userService->update(
                new UserServiceUpdateDto(
                    Id::create($id),
                    Username::create($username),
                    DecodedPassword::create($password),
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
        path: "/user/{id}",
        summary: "Set activation status of a User by its ID",
        description: "If authenticated, sets the activation status and returns the status",
        tags: ["User"],
        parameters: [
            new OA\PathParameter(
                name: "id",
                description: "The id of the User to be updated",
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
        description: "Response if authenticated and the User is updated on the repository",
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
        description: "Response if a value is missing or if the User does not exist",
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
            properties: [
                new OA\Property(
                    property: "message",
                    type: "string",
                ),
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

            $wasUpdated = $this->userService->setIsActive(
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
        path: "/user/{id}",
        summary: "Returns a User by its ID",
        description: "If authenticated, searches for the User with the ID, and if exists, returns it",
        tags: ["User"],
        parameters: [
            new OA\PathParameter(
                name: "id",
                description: "The id of the User to be searched",
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
        description: "Response if authenticated and the User with the informed ID exists",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: "data",
                    type: "object",
                    ref: "#/components/schemas/User"
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
        description: "Response if the User does not exist",
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
            properties: [
                new OA\Property(
                    property: "message",
                    type: "string",
                ),
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

            $user = SafeUser::create(
                $this->userService->findById(
                    Id::create($id),
                    $token
                )
            );

            if ($user === null) {
                $response
                    ->getBody()
                    ->write(
                        json_encode([
                            "found" => false
                        ])
                    );
                return $response->withStatus(404);
            }

            $data = [
                "id" => $user->getId()->getValue(),
                "username" => $user->getUsername()->getValue(),
                "is_active" => $user->getIsActive()
            ];

            $response
                ->getBody()
                ->write(
                    json_encode($data)
                );
            return $response->withStatus(201);
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    #[OA\Get(
        path: "/user/{username}",
        summary: "Returns a User by its Username",
        description: "If authenticated, searches for the User with the Username, and if exists, returns it",
        tags: ["User"],
        parameters: [
            new OA\PathParameter(
                name: "username",
                description: "The Username of the User to be searched",
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
        description: "Response if authenticated and the User with the informed username exists",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: "data",
                    type: "object",
                    ref: "#/components/schemas/User"
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
        description: "Response if the User does not exist",
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
            properties: [
                new OA\Property(
                    property: "message",
                    type: "string",
                ),
            ]
        )
    )]
    public function findByUsername(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        try {
            $response = $response->withHeader("Content-Type", "application/json");

            $token = $request->getAttribute("token");

            $missingUriParams = ArrayKeysExistanceChecker::checkAndReturnMissingKeys($args, ["username"]);
            if (count($missingUriParams) > 0) {
                return HttpMissingKeysInformerResponse::getStatusAsArrayOfUriParams($response, $missingUriParams);
            }

            $username = $args["username"];

            $user = SafeUser::create(
                $this->userService->findByUsername(
                    Username::create($username),
                    $token
                )
            );

            if ($user === null) {
                $response
                    ->getBody()
                    ->write(
                        json_encode([
                            "found" => false
                        ])
                    );
                return $response->withStatus(404);
            }

            $data = [
                "id" => $user->getId()->getValue(),
                "username" => $user->getUsername()->getValue(),
                "is_active" => $user->getIsActive()
            ];

            $response
                ->getBody()
                ->write(
                    json_encode($data)
                );
            return $response->withStatus(201);
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    #[OA\Get(
        path: "/user",
        summary: "Returns all the Users on the repository",
        description: "If authenticated, returns all the existant Users",
        tags: ["User"],
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
                        ref: "#/components/schemas/User"
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
        description: "Response if no Users were found",
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
            properties: [
                new OA\Property(
                    property: "message",
                    type: "string",
                ),
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

            $users = $this->userService->findAll(
                $token
            );

            if ($users === null) {
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
            foreach ($users->fetchAll() as $user) {
                $safeUser = SafeUser::create($user);
                $value = [
                    "id" => $safeUser->getId()->getValue(),
                    "username" => $safeUser->getUsername()->getValue(),
                    "is_active" => $safeUser->getIsActive()
                ];

                $data[] = $value;
            }

            $response
                ->getBody()
                ->write(
                    json_encode([
                        "amount" => $users->count(),
                        "data" => $data
                    ])
                );
            return $response->withStatus(200);
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
