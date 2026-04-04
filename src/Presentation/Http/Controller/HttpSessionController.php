<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Controller;

use Mvreisg\GamebaseBackend\Application\Session\Login\Parameters\SessionLoginParameters;
use Mvreisg\GamebaseBackend\Application\Session\Service\SessionService;
use Mvreisg\GamebaseBackend\Domain\User\ValueObject\Password\Decoded\DecodedPassword;
use Mvreisg\GamebaseBackend\Domain\User\ValueObject\Username\Username;
use Mvreisg\GamebaseBackend\Infrastructure\Arrays\ArrayKeysExistanceChecker;
use Mvreisg\GamebaseBackend\Infrastructure\Time\Duration;
use Mvreisg\GamebaseBackend\Infrastructure\Time\TimeUnit;
use Mvreisg\GamebaseBackend\Presentation\Http\Util\Response\HttpMissingKeysInformerResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class HttpSessionController
{
    private SessionService $sessionService;

    public function __construct(SessionService $sessionService)
    {
        $this->sessionService = $sessionService;
    }

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
                    DecodedPassword::make($password),
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

    public function retrieveData(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
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
