<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Controllers;

use Mvreisg\GamebaseBackend\Application\Services\Authentication\AuthenticationService;
use Mvreisg\GamebaseBackend\Application\Services\UserPermission\UserPermissionService;
use Mvreisg\GamebaseBackend\Domain\Data\Id;
use Mvreisg\GamebaseBackend\Domain\Data\UserPermission;
use Mvreisg\GamebaseBackend\Presentation\Http\Entities\HttpRequest;
use Mvreisg\GamebaseBackend\Presentation\Http\Entities\HttpResponse;
use Mvreisg\GamebaseBackend\Presentation\Http\Enums\HttpRequestBodyPartTypes;
use Mvreisg\GamebaseBackend\Presentation\Http\Enums\HttpRouteParameterTypes;
use Mvreisg\GamebaseBackend\Presentation\Http\Middlewares\Authentication\Token\Jwt\HttpJwtAuthenticationTokenValidator;

class HttpUserPermissionController
{
    private UserPermissionService $userPermissionService;
    private AuthenticationService $authenticationService;

    public function __construct(
        UserPermissionService $userPermissionService,
        AuthenticationService $authenticationService
    ) {
        $this->userPermissionService = $userPermissionService;
        $this->authenticationService = $authenticationService;
    }

    public function insert(HttpRequest $request): HttpResponse
    {
        try {
            $response = HttpResponse::make();

            HttpJwtAuthenticationTokenValidator::validate(
                $request->getHeaderOrDieTrying("Authorization"),
                $this->authenticationService
            );

            $userId = $request->getBodyOrDieTrying("user_id", HttpRequestBodyPartTypes::Int);
            $permissionId = $request->getBodyOrDieTrying("permission_id", HttpRequestBodyPartTypes::Int);

            $userPermission = $this->userPermissionService->insert(
                new UserPermission(
                    Id::make($userId),
                    Id::make($permissionId)
                )
            );

            $response
                ->setBody([
                    "data" => [
                        "id" => $userPermission->getIdValue(),
                        "user_id" => $userPermission->getUserIdValue(),
                        "permission_id" => $userPermission->getPermissionIdValue()
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

            HttpJwtAuthenticationTokenValidator::validate(
                $request->getHeaderOrDieTrying("Authorization"),
                $this->authenticationService
            );

            $id = $request->getParamOrDieTrying("id", HttpRouteParameterTypes::Integer);
            $userId = $request->getBodyOrDieTrying("user_id", HttpRequestBodyPartTypes::Int);
            $permissionId = $request->getBodyOrDieTrying("permission_id", HttpRequestBodyPartTypes::Int);

            $userPermission = new UserPermission(
                Id::make($userId),
                Id::make($permissionId)
            );
            $userPermission->setId(Id::make($id));

            $wasUpdated = $this->userPermissionService->update(
                $userPermission
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

            HttpJwtAuthenticationTokenValidator::validate(
                $request->getHeaderOrDieTrying("Authorization"),
                $this->authenticationService
            );

            $id = $request->getParamOrDieTrying("id", HttpRouteParameterTypes::Integer);

            $wasDeleted = $this->userPermissionService->delete(
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

            HttpJwtAuthenticationTokenValidator::validate(
                $request->getHeaderOrDieTrying("Authorization"),
                $this->authenticationService
            );

            $id = $request->getParamOrDieTrying("id", HttpRouteParameterTypes::Integer);

            $userPermission = $this->userPermissionService->findById(
                Id::make($id)
            );

            $response
                ->setBody([
                    "data" => [
                        "id" => $userPermission->getIdValue(),
                        "user_id" => $userPermission->getUserIdValue(),
                        "permission_id" => $userPermission->getPermissionIdValue()
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

            HttpJwtAuthenticationTokenValidator::validate(
                $request->getHeaderOrDieTrying("Authorization"),
                $this->authenticationService
            );

            $userPermissions = $this->userPermissionService->findAll();

            if ($userPermissions->isEmpty()) {
                $response
                    ->setBody([
                        "message" => "Nothing found!"
                    ])
                    ->setStatusOk()
                    ->setContentTypeAsJson();
                return $response;
            }

            $data = [];
            foreach ($userPermissions->fetchAll() as $userPermission) {
                $data[] = [
                    "id" => $userPermission->getIdValue(),
                    "user_id" => $userPermission->getUserIdValue(),
                    "permission_id" => $userPermission->getPermissionIdValue()
                ];
            }

            $response
                ->setBody([
                    "number_found" => $userPermissions->count(),
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
