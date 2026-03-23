<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Controllers;

use Mvreisg\GamebaseBackend\Application\Services\Authorization\AuthorizationService;
use Mvreisg\GamebaseBackend\Application\Services\Permission\PermissionService;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\Action\Decoder\AuthenticationTokenDecoder;
use Mvreisg\GamebaseBackend\Domain\Authentication\Token\State\Encoded\EncodedAuthenticationToken;
use Mvreisg\GamebaseBackend\Domain\Authorization\Types\PermissionTypes;
use Mvreisg\GamebaseBackend\Domain\Authorization\Types\SectorTypes;
use Mvreisg\GamebaseBackend\Domain\Entities\Id;
use Mvreisg\GamebaseBackend\Domain\Entities\Name;
use Mvreisg\GamebaseBackend\Domain\Entities\Permission;
use Mvreisg\GamebaseBackend\Domain\Entities\PermissionValue;
use Mvreisg\GamebaseBackend\Domain\Utils\Arrays\ArrayKeysExistanceChecker;
use Mvreisg\GamebaseBackend\Presentation\Http\Utils\Response\HttpMissingKeysInformerResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class HttpPermissionController
{
    private PermissionService $permissionService;
    private AuthorizationService $authorizationService;
    private AuthenticationTokenDecoder $authenticationTokenDecoder;

    public function __construct(
        PermissionService $permissionService,
        AuthorizationService $authorizationService,
        AuthenticationTokenDecoder $authenticationTokenDecoder
    ) {
        $this->permissionService = $permissionService;
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
                SectorTypes::Permission,
                PermissionTypes::Create
            );

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
                new Permission(
                    Name::make($name),
                    PermissionValue::make($value),
                    $isActive
                )
            );

            $data = [
                "id" => $permission->getIdValue(),
                "name" => $permission->getNameValue(),
                "is_active" => $permission->getIsActive(),
                "value" => $permission->getPermissionValue()
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

            $decodedToken = $this->authenticationTokenDecoder->decode(
                new EncodedAuthenticationToken(
                    $token
                )
            );

            $this->authorizationService->check(
                $decodedToken->getUserSectorPermissionCollection(),
                SectorTypes::Permission,
                PermissionTypes::Update
            );

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

            $permission = new Permission(
                Name::make($name),
                PermissionValue::make($value),
                $isActive
            );
            $permission->setId(Id::make($id));

            $wasUpdated = $this->permissionService->update(
                $permission
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

    public function setIsActive(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
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
                SectorTypes::Permission,
                PermissionTypes::Activate
            );

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
                Id::make($id),
                $isActive
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
                SectorTypes::Permission,
                PermissionTypes::List
            );

            $missingUriParams = ArrayKeysExistanceChecker::checkAndReturnMissingKeys($args, ["id"]);
            if (count($missingUriParams) > 0) {
                return HttpMissingKeysInformerResponse::getStatusAsArrayOfUriParams($response, $missingUriParams);
            }

            $id = (int)$args["id"];

            $permission = $this->permissionService->findById(
                Id::make($id)
            );

            $response
                ->getBody()
                ->write(
                    json_encode([
                        "data" => [
                            "id" => $permission->getIdValue(),
                            "name" => $permission->getNameValue(),
                            "value" => $permission->getPermissionValue(),
                            "is_active" => $permission->getIsActive()
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
                SectorTypes::Permission,
                PermissionTypes::List
            );

            $permissions = $this->permissionService->findAll();

            if ($permissions->count() === 0) {
                $response
                    ->getBody()
                    ->write(
                        json_encode([
                            "message" => "Nothing found!"
                        ])
                    );
                return $response->withStatus(404);
            }

            foreach ($permissions->fetchAll() as $permission) {
                $data[] = [
                    "id" => $permission->getIdValue(),
                    "name" => $permission->getNameValue(),
                    "value" => $permission->getPermissionValue(),
                    "is_active" => $permission->getIsActive()
                ];
            }

            $response
                ->getBody()
                ->write(
                    json_encode([
                        "number_found" => $permissions->count(),
                        "data" => $data
                    ])
                );
            return $response->withStatus(200);
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
