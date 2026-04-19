<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Controller;

use Mvreisg\GamebaseBackend\Application\UserSectorPermission\Service\UserSectorPermissionService;
use Mvreisg\GamebaseBackend\Domain\Permission\Entity\Permission;
use Mvreisg\GamebaseBackend\Domain\Sector\Entity\Sector;
use Mvreisg\GamebaseBackend\Domain\Shared\ValueObject\Id\Id;
use Mvreisg\GamebaseBackend\Domain\User\Entity\User;
use Mvreisg\GamebaseBackend\Domain\UserSectorPermission\Entity\UserSectorPermission;
use Mvreisg\GamebaseBackend\Infrastructure\Arrays\ArrayKeysExistanceChecker;
use Mvreisg\GamebaseBackend\Presentation\Http\Util\Response\HttpMissingKeysInformerResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class HttpUserSectorPermissionController
{
    private UserSectorPermissionService $userSectorPermissionService;

    public function __construct(UserSectorPermissionService $userSectorPermissionService)
    {
        $this->userSectorPermissionService = $userSectorPermissionService;
    }

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
                new UserSectorPermission(
                    null,
                    User::create(
                        Id::create(
                            $userId
                        )
                    ),
                    Sector::create(
                        Id::create(
                            $sectorId
                        )
                    ),
                    Permission::create(
                        Id::create($permissionId)
                    )
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
                                "password" => $userSectorPermission->getUser()->getPassword()->getValue(),
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

            $userSectorPermission = new UserSectorPermission(
                Id::create(
                    $id
                ),
                User::create(
                    Id::create(
                        $userId
                    )
                ),
                Sector::create(
                    Id::create(
                        $sectorId
                    )
                ),
                Permission::create(
                    Id::create($permissionId)
                )
            );
            $userSectorPermission->setId(Id::create($id));

            $wasUpdated = $this->userSectorPermissionService->update(
                $userSectorPermission,
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
                                "password" => $userSectorPermission->getUser()->getPassword()->getValue(),
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

            $userSectorPermissions = $this->userSectorPermissionService->findAll(
                $token
            );

            if ($userSectorPermissions === null) {
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
            foreach ($userSectorPermissions->fetchAll() as $userSectorPermission) {
                $data[] = [
                    "id" => $userSectorPermission->getId()->getValue(),
                    "user" => [
                        "id" => $userSectorPermission->getUser()->getId()->getValue(),
                        "username" => $userSectorPermission->getUser()->getUsername()->getValue(),
                        "password" => $userSectorPermission->getUser()->getPassword()->getValue(),
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
                        "number_found" => $userSectorPermissions->count(),
                        "data" => $data
                    ])
                );
            return $response->withStatus(200);
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
