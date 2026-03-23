<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Controllers;

use Mvreisg\GamebaseBackend\Application\Services\Authorization\AuthorizationService;
use Mvreisg\GamebaseBackend\Application\Services\UserSectorPermission\UserSectorPermissionService;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\Action\Decoder\AuthenticationTokenDecoder;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\State\Encoded\EncodedAuthenticationToken;
use Mvreisg\GamebaseBackend\Domain\Authorization\Types\PermissionTypes;
use Mvreisg\GamebaseBackend\Domain\Authorization\Types\SectorTypes;
use Mvreisg\GamebaseBackend\Domain\Entities\Id;
use Mvreisg\GamebaseBackend\Domain\Entities\UserSectorPermission;
use Mvreisg\GamebaseBackend\Domain\Utils\Arrays\ArrayKeysExistanceChecker;
use Mvreisg\GamebaseBackend\Presentation\Http\Utils\Response\HttpMissingKeysInformerResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class HttpUserSectorPermissionController
{
    private UserSectorPermissionService $userSectorPermissionService;
    private AuthorizationService $authorizationService;
    private AuthenticationTokenDecoder $authenticationTokenDecoder;

    public function __construct(
        UserSectorPermissionService $userSectorPermissionService,
        AuthorizationService $authorizationService,
        AuthenticationTokenDecoder $authenticationTokenDecoder
    ) {
        $this->userSectorPermissionService = $userSectorPermissionService;
        $this->authorizationService = $authorizationService;
        $this->authenticationTokenDecoder = $authenticationTokenDecoder;
    }

    public function insert(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $response = $response->withHeader("Content-Type", "application/json");

            $token = $request->getAttribute("token");

            $decodedToken = $this->authenticationTokenDecoder->decode(
                new EncodedAuthenticationToken(
                    $token
                )
            );

            $this->authorizationService->check(
                $decodedToken->getUserSectorPermissionCollection(),
                SectorTypes::UserSectorPermission,
                PermissionTypes::Create
            );

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
                    Id::make($userId),
                    Id::make($sectorId),
                    Id::make($permissionId)
                )
            );

            $response
                ->getBody()
                ->write(
                    json_encode([
                        "data" => [
                            "id" => $userSectorPermission->getIdValue(),
                            "user_id" => $userSectorPermission->getUserIdValue(),
                            "sector_id" => $userSectorPermission->getSectorIdValue(),
                            "permission_id" => $userSectorPermission->getPermissionIdValue()
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

            $decodedToken = $this->authenticationTokenDecoder->decode(
                new EncodedAuthenticationToken(
                    $token
                )
            );

            $this->authorizationService->check(
                $decodedToken->getUserSectorPermissionCollection(),
                SectorTypes::UserSectorPermission,
                PermissionTypes::Update
            );

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
                Id::make($userId),
                Id::make($sectorId),
                Id::make($permissionId)
            );
            $userSectorPermission->setId(Id::make($id));

            $wasUpdated = $this->userSectorPermissionService->update(
                $userSectorPermission
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

            $decodedToken = $this->authenticationTokenDecoder->decode(
                new EncodedAuthenticationToken(
                    $token
                )
            );

            $this->authorizationService->check(
                $decodedToken->getUserSectorPermissionCollection(),
                SectorTypes::UserSectorPermission,
                PermissionTypes::Delete
            );

            $missingUriParams = ArrayKeysExistanceChecker::checkAndReturnMissingKeys($args, ["id"]);
            if (count($missingUriParams) > 0) {
                return HttpMissingKeysInformerResponse::getStatusAsArrayOfUriParams($response, $missingUriParams);
            }

            $id = (int)$args["id"];

            $wasDeleted = $this->userSectorPermissionService->delete(
                Id::make($id)
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

            $decodedToken = $this->authenticationTokenDecoder->decode(
                new EncodedAuthenticationToken(
                    $token
                )
            );

            $this->authorizationService->check(
                $decodedToken->getUserSectorPermissionCollection(),
                SectorTypes::UserSectorPermission,
                PermissionTypes::List
            );

            $missingUriParams = ArrayKeysExistanceChecker::checkAndReturnMissingKeys($args, ["id"]);
            if (count($missingUriParams) > 0) {
                return HttpMissingKeysInformerResponse::getStatusAsArrayOfUriParams($response, $missingUriParams);
            }

            $id = (int)$args["id"];

            $userSectorPermission = $this->userSectorPermissionService->findById(
                Id::make($id)
            );

            $response
                ->getBody()
                ->write(
                    json_encode([
                        "data" => [
                            "id" => $userSectorPermission->getIdValue(),
                            "user_id" => $userSectorPermission->getUserIdValue(),
                            "sector_id" => $userSectorPermission->getSectorIdValue(),
                            "permission_id" => $userSectorPermission->getPermissionIdValue()
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

            $decodedToken = $this->authenticationTokenDecoder->decode(
                new EncodedAuthenticationToken(
                    $token
                )
            );

            $this->authorizationService->check(
                $decodedToken->getUserSectorPermissionCollection(),
                SectorTypes::UserSectorPermission,
                PermissionTypes::List
            );

            $userSectorPermissions = $this->userSectorPermissionService->findAll();

            if ($userSectorPermissions->isEmpty()) {
                $response
                    ->getBody()
                    ->write(
                        json_encode([
                            "message" => "Nothing found!"
                        ])
                    );
                return $response->withStatus(404);
            }

            $data = [];
            foreach ($userSectorPermissions->fetchAll() as $userSectorPermission) {
                $data[] = [
                    "id" => $userSectorPermission->getIdValue(),
                    "user_id" => $userSectorPermission->getUserIdValue(),
                    "sector_id" => $userSectorPermission->getSectorIdValue(),
                    "permission_id" => $userSectorPermission->getPermissionIdValue()
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
