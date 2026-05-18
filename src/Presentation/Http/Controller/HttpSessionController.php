<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Controller;

use Mvreisg\GamebaseBackend\Application\Session\Login\Parameters\SessionLoginParameters;
use Mvreisg\GamebaseBackend\Application\Session\Service\SessionService;
use Mvreisg\GamebaseBackend\Domain\User\ValueObject\Password\Decoded\DecodedPassword;
use Mvreisg\GamebaseBackend\Domain\User\ValueObject\Username\Username;
use Mvreisg\GamebaseBackend\Infrastructure\Arrays\ArrayKeysExistanceChecker;
use Mvreisg\GamebaseBackend\Infrastructure\Time\Duration;
use Mvreisg\GamebaseBackend\Infrastructure\Time\Unit\TimeUnit;
use Mvreisg\GamebaseBackend\Presentation\Http\Util\Response\HttpMissingKeysInformerResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: "Session",
    description: "Endpoints related to session management"
)]
class HttpSessionController
{
    private SessionService $sessionService;

    public function __construct(SessionService $sessionService)
    {
        $this->sessionService = $sessionService;
    }

    #[OA\Post(
        path: "/session/login",
        summary: "Login",
        description:
            "Receives the user credentials and if valid, creates a session and returns the authentication token",
        tags: ["Session"]
    )]
    #[OA\RequestBody(
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: "username",
                    type: "string",
                    example: "mvreisg"
                ),
                new OA\Property(
                    property: "password",
                    type: "string",
                    example: "mg4ing854g48n"
                ),
                new OA\Property(
                    property: "one_week_login",
                    type: "boolean",
                    example: true
                ),
            ]
        )
    )]
    #[OA\Response(
        response: 201,
        description: "Response if credentials is valid",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: "data",
                    type: "object",
                    properties: [
                        new OA\Property(
                            property: "expires",
                            type: "object",
                            properties: [
                                new OA\Property(
                                    property: "unit",
                                    type: "string",
                                    example: "seconds"
                                ),
                                new OA\Property(
                                    property: "time",
                                    type: "integer",
                                    example: 604800
                                )
                            ]
                        ),
                        new OA\Property(
                            property: "token",
                            type: "string",
                            example: "Bearer gmeroibmerong98345nh04h45"
                        ),
                        new OA\Property(
                            property: "user",
                            type: "object",
                            properties: [
                                new OA\Property(
                                    property: "id",
                                    type: "integer",
                                    example: 1
                                ),
                                new OA\Property(
                                    property: "username",
                                    type: "string",
                                    example: "mvreisg"
                                ),
                                new OA\Property(
                                    property: "permissions",
                                    type: "array",
                                    items: new OA\Items(
                                        ref: "#/components/schemas/UserSectorPermissionEntity"
                                    )
                                )
                            ]
                        )
                    ]
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
        description: "Response if a body value is missing, if the user does not exist",
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
                            example: ["username, password"],
                            items: new OA\Items(
                                type: "string"
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
    public function login(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $response = $response->withHeader("Content-Type", "application/json");

            $body = $request->getParsedBody();

            $missingKeys = ArrayKeysExistanceChecker::checkAndReturnMissingKeys(
                $body,
                ["username", "password", "one_week_login"]
            );
            if (count($missingKeys) > 0) {
                return HttpMissingKeysInformerResponse::getStatusAsArrayOfBodyKeys($response, $missingKeys);
            }

            $username = $body["username"];
            $password = $body["password"];
            $oneWeekLogin = $body["one_week_login"];

            $result = $this->sessionService->login(
                new SessionLoginParameters(
                    Username::create($username),
                    DecodedPassword::create($password),
                    $oneWeekLogin
                )
            );
            $token = $result->getToken();
            $data = [
                "data" => [
                    "expires" => [
                        "unit" => TimeUnit::getName(TimeUnit::Second),
                        "time" => $oneWeekLogin === true ?
                            Duration::ONE_DAY_IN_SECONDS * 7 :
                            Duration::ONE_DAY_IN_SECONDS
                    ],
                    "token" => $token,
                    "user" => [
                        "id" => $result->getData()->getUserId()->getValue(),
                        "username" => $result->getData()->getUsername()->getValue(),
                        "permissions" => array_map(function ($item) {
                            return [
                                "id" => $item->getId()->getValue(),
                                "user" => [
                                    "id" => $item->getUser()->getId()->getValue(),
                                    "username" => $item->getUser()->getUsername()->getValue(),
                                    "password" => $item->getUser()->getPassword()->getValue(),
                                    "is_active" => $item->getUser()->getIsActive(),
                                ],
                                "sector" => [
                                    "id" => $item->getSector()->getId()->getValue(),
                                    "name" => $item->getSector()->getName()->getValue(),
                                    "value" => $item->getSector()->getSectorValue()->getValue(),
                                    "is_active" => $item->getSector()->getIsActive(),
                                ],
                                "permission" => [
                                    "id" => $item->getPermission()->getId()->getValue(),
                                    "name" => $item->getPermission()->getName()->getValue(),
                                    "value" => $item->getPermission()->getPermissionValue()->getValue(),
                                    "is_active" => $item->getPermission()->getIsActive(),
                                ]
                            ];
                        }, $result->getData()->getUserSectorPermissionCollection()->fetchAll())
                    ]
                ]
            ];
            $response
                ->getBody()
                ->write(
                    json_encode($data)
                );
            return $response
                ->withStatus(201);
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    #[OA\Delete(
        path: "/session/logoff",
        summary: "Logoff",
        description:
            "Invalidates the user's authentication token and removes it from the cache",
        tags: ["Session"]
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
        description: "Response if logoff process is successful",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: "status",
                    type: "integer",
                    example: 200
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
        response: 403,
        description: "Response if user is forbidden because of invalid token",
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
        response: 500,
        description: "Response if a internal server error occurs",
        content: new OA\JsonContent(
            oneOf: [
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
    public function logoff(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $response = $response->withHeader("Content-Type", "application/json");

            $token = $request->getAttribute("token");

            $wasDeleted = $this->sessionService->logoff(
                $token
            );

            $response
                ->getBody()
                ->write(
                    json_encode([
                        "status" => $wasDeleted ? "deleted" : "same"
                    ])
                );
            return $response->withStatus(200);
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    #[OA\Get(
        path: "/session/me",
        summary: "Get User Information",
        description:
            "Returns the information of the currently logged-in user",
        tags: ["Session"]
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
        description: "Response if credentials is valid",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: "data",
                    type: "object",
                    properties: [
                        new OA\Property(
                            property: "id",
                            type: "integer",
                            example: 1
                        ),
                        new OA\Property(
                            property: "username",
                            type: "string",
                            example: "mvreisg"
                        ),
                        new OA\Property(
                            property: "permissions",
                            type: "array",
                            items: new OA\Items(
                                ref: "#/components/schemas/UserSectorPermissionEntity"
                            )
                        )
                    ]
                )
            ]
        )
    )]
    #[OA\Response(
        response: 404,
        description: "Response if a body value is missing, if the user does not exist",
        content: new OA\JsonContent(
            title: "Unexistant token",
            properties: [
                new OA\Property(
                    property: "message",
                    type: "string",
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
    public function retrieveData(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        try {
            $response = $response->withHeader("Content-Type", "application/json");

            $token = $request->getAttribute("token");

            $sessionData = $this->sessionService->retrieveData(
                $token
            );

            $data = [
                "id" => $sessionData->getUserId()->getValue(),
                "username" => $sessionData->getUsername()->getValue(),
                "permissions" => array_map(function ($item) {
                    return [
                        "id" => $item->getId()->getValue(),
                        "user" => [
                            "id" => $item->getUser()->getId()->getValue(),
                            "username" => $item->getUser()->getUsername()->getValue(),
                            "password" => $item->getUser()->getPassword()->getValue(),
                            "is_active" => $item->getUser()->getIsActive(),
                        ],
                        "sector" => [
                            "id" => $item->getSector()->getId()->getValue(),
                            "name" => $item->getSector()->getName()->getValue(),
                            "value" => $item->getSector()->getSectorValue()->getValue(),
                            "is_active" => $item->getSector()->getIsActive(),
                        ],
                        "permission" => [
                            "id" => $item->getPermission()->getId()->getValue(),
                            "name" => $item->getPermission()->getName()->getValue(),
                            "value" => $item->getPermission()->getPermissionValue()->getValue(),
                            "is_active" => $item->getPermission()->getIsActive(),
                        ]
                    ];
                }, $sessionData->getUserSectorPermissionCollection()->fetchAll())
            ];

            $response
                ->getBody()
                ->write(
                    json_encode([
                        "data" => $data
                    ])
                );
            return $response->withStatus(200);
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
