<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Controllers;

use Mvreisg\GamebaseBackend\Application\Services\Authentication\AuthenticationService;
use Mvreisg\GamebaseBackend\Application\Services\Authorization\AuthorizationService;
use Mvreisg\GamebaseBackend\Application\Services\UserSectorPermission\UserSectorPermissionService;
use Mvreisg\GamebaseBackend\Domain\Authorization\Enums\PermissionTypes;
use Mvreisg\GamebaseBackend\Domain\Authorization\Enums\SectorTypes;
use Mvreisg\GamebaseBackend\Domain\Entities\Id;
use Mvreisg\GamebaseBackend\Domain\Entities\UserSectorPermission;
use Mvreisg\GamebaseBackend\Presentation\Http\Entities\HttpRequest;
use Mvreisg\GamebaseBackend\Presentation\Http\Entities\HttpResponse;
use Mvreisg\GamebaseBackend\Presentation\Http\Enums\HttpRequestBodyPartTypes;
use Mvreisg\GamebaseBackend\Presentation\Http\Enums\HttpRouteParameterTypes;
use Mvreisg\GamebaseBackend\Presentation\Http\Middlewares\Authentication\Token\Jwt\HttpJwtAuthenticationTokenValidator;

class HttpUserSectorPermissionController
{
    private UserSectorPermissionService $userSectorPermissionService;
    private AuthenticationService $authenticationService;
    private AuthorizationService $authorizationService;

    public function __construct(
        UserSectorPermissionService $userSectorPermissionService,
        AuthenticationService $authenticationService,
        AuthorizationService $authorizationService
    ) {
        $this->userSectorPermissionService = $userSectorPermissionService;
        $this->authenticationService = $authenticationService;
        $this->authorizationService = $authorizationService;
    }

    public function insert(HttpRequest $request): HttpResponse
    {
        try {
            $response = HttpResponse::make();

            $validationResult = HttpJwtAuthenticationTokenValidator::validate(
                $request->getHeaderOrDieTrying("Authorization"),
                $this->authenticationService
            );

            $this->authorizationService->check(
                $validationResult->getUserSectorPermissionCollection(),
                SectorTypes::UserSectorPermission,
                PermissionTypes::Create
            );

            $userId = $request->getBodyOrDieTrying("user_id", HttpRequestBodyPartTypes::Int);
            $sectorId = $request->getBodyOrDieTrying("sector_id", HttpRequestBodyPartTypes::Int);
            $permissionId = $request->getBodyOrDieTrying("permission_id", HttpRequestBodyPartTypes::Int);

            $userSectorPermission = $this->userSectorPermissionService->insert(
                new UserSectorPermission(
                    Id::make($userId),
                    Id::make($sectorId),
                    Id::make($permissionId)
                )
            );

            $response
                ->setBody([
                    "data" => [
                        "id" => $userSectorPermission->getIdValue(),
                        "user_id" => $userSectorPermission->getUserIdValue(),
                        "sector_id" => $userSectorPermission->getSectorIdValue(),
                        "permission_id" => $userSectorPermission->getPermissionIdValue()
                    ]
                ])
                ->setStatusCreated()
                ->setContentTypeAsJson();
            return $response;
        } catch (\Throwable $e) {
            throw $e;
        }
    }


    public function update(HttpRequest $request): HttpResponse
    {
        try {
            $response = HttpResponse::make();

            $validationResult = HttpJwtAuthenticationTokenValidator::validate(
                $request->getHeaderOrDieTrying("Authorization"),
                $this->authenticationService
            );

            $this->authorizationService->check(
                $validationResult->getUserSectorPermissionCollection(),
                SectorTypes::UserSectorPermission,
                PermissionTypes::Update
            );

            $id = $request->getParamOrDieTrying("id", HttpRouteParameterTypes::Integer);
            $userId = $request->getBodyOrDieTrying("user_id", HttpRequestBodyPartTypes::Int);
            $sectorId = $request->getBodyOrDieTrying("sector_id", HttpRequestBodyPartTypes::Int);
            $permissionId = $request->getBodyOrDieTrying("permission_id", HttpRequestBodyPartTypes::Int);

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
                ->setBody([
                    "was_updated" => $wasUpdated
                ])
                ->setStatusOk()
                ->setContentTypeAsJson();
            return $response;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function delete(HttpRequest $request): HttpResponse
    {
        try {
            $response = HttpResponse::make();

            $validationResult = HttpJwtAuthenticationTokenValidator::validate(
                $request->getHeaderOrDieTrying("Authorization"),
                $this->authenticationService
            );

            $this->authorizationService->check(
                $validationResult->getUserSectorPermissionCollection(),
                SectorTypes::UserSectorPermission,
                PermissionTypes::Delete
            );

            $id = $request->getParamOrDieTrying("id", HttpRouteParameterTypes::Integer);

            $wasDeleted = $this->userSectorPermissionService->delete(
                Id::make($id)
            );

            $response
                ->setBody([
                    "was_deleted" => $wasDeleted
                ])
                ->setStatusOk()
                ->setContentTypeAsJson();
            return $response;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findById(HttpRequest $request): HttpResponse
    {
        try {
            $response = HttpResponse::make();

            $validationResult = HttpJwtAuthenticationTokenValidator::validate(
                $request->getHeaderOrDieTrying("Authorization"),
                $this->authenticationService
            );

            $this->authorizationService->check(
                $validationResult->getUserSectorPermissionCollection(),
                SectorTypes::UserSectorPermission,
                PermissionTypes::List
            );

            $id = $request->getParamOrDieTrying("id", HttpRouteParameterTypes::Integer);

            $userSectorPermission = $this->userSectorPermissionService->findById(
                Id::make($id)
            );

            $response
                ->setBody([
                    "data" => [
                        "id" => $userSectorPermission->getIdValue(),
                        "user_id" => $userSectorPermission->getUserIdValue(),
                        "sector_id" => $userSectorPermission->getSectorIdValue(),
                        "permission_id" => $userSectorPermission->getPermissionIdValue()
                    ]
                ])
                ->setStatusOk()
                ->setContentTypeAsJson();
            return $response;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findAll(HttpRequest $request): HttpResponse
    {
        try {
            $response = HttpResponse::make();

            $validationResult = HttpJwtAuthenticationTokenValidator::validate(
                $request->getHeaderOrDieTrying("Authorization"),
                $this->authenticationService
            );

            $this->authorizationService->check(
                $validationResult->getUserSectorPermissionCollection(),
                SectorTypes::UserSectorPermission,
                PermissionTypes::List
            );

            $userSectorPermissions = $this->userSectorPermissionService->findAll();

            if ($userSectorPermissions->isEmpty()) {
                $response
                    ->setBody([
                        "message" => "Nothing found!"
                    ])
                    ->setStatusOk()
                    ->setContentTypeAsJson();
                return $response;
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
                ->setBody([
                    "number_found" => $userSectorPermissions->count(),
                    "data" => $data
                ])
                ->setStatusOk()
                ->setContentTypeAsJson();
            return $response;
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
