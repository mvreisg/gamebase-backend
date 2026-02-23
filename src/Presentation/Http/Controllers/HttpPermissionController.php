<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Controllers;

use Mvreisg\GamebaseBackend\Application\Services\Authentication\AuthenticationService;
use Mvreisg\GamebaseBackend\Application\Services\Authorization\AuthorizationService;
use Mvreisg\GamebaseBackend\Application\Services\Permission\PermissionService;
use Mvreisg\GamebaseBackend\Domain\Authorization\Enums\PermissionTypes;
use Mvreisg\GamebaseBackend\Domain\Authorization\Enums\SectorTypes;
use Mvreisg\GamebaseBackend\Domain\Data\Id;
use Mvreisg\GamebaseBackend\Domain\Data\Name;
use Mvreisg\GamebaseBackend\Domain\Data\Permission;
use Mvreisg\GamebaseBackend\Domain\Data\PermissionValue;
use Mvreisg\GamebaseBackend\Presentation\Http\Entities\HttpRequest;
use Mvreisg\GamebaseBackend\Presentation\Http\Entities\HttpResponse;
use Mvreisg\GamebaseBackend\Presentation\Http\Enums\HttpRequestBodyPartTypes;
use Mvreisg\GamebaseBackend\Presentation\Http\Enums\HttpRouteParameterTypes;
use Mvreisg\GamebaseBackend\Presentation\Http\Middlewares\Authentication\Token\Jwt\HttpJwtAuthenticationTokenValidator;

class HttpPermissionController
{
    private PermissionService $permissionService;
    private AuthenticationService $authenticationService;
    private AuthorizationService $authorizationService;

    public function __construct(
        PermissionService $permissionService,
        AuthenticationService $authenticationService,
        AuthorizationService $authorizationService
    ) {
        $this->permissionService = $permissionService;
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
                SectorTypes::Permission,
                PermissionTypes::Create
            );

            $name = $request->getBodyOrDieTrying("name", HttpRequestBodyPartTypes::String);
            $isActive = $request->getBodyOrDieTrying("is_active", HttpRequestBodyPartTypes::Bool);
            $value = $request->getBodyOrDieTrying("value", HttpRequestBodyPartTypes::String);

            $permission = $this->permissionService->insert(
                new Permission(
                    Name::make($name),
                    PermissionValue::make($value),
                    $isActive
                )
            );

            $response
                ->setBody([
                    "data" => [
                        "id" => $permission->getIdValue(),
                        "name" => $permission->getNameValue(),
                        "isActive" => $permission->getIsActive()
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
                SectorTypes::Permission,
                PermissionTypes::Update
            );

            $id = $request->getParamOrDieTrying("id", HttpRouteParameterTypes::Integer);
            $name = $request->getBodyOrDieTrying("name", HttpRequestBodyPartTypes::String);
            $isActive = $request->getBodyOrDieTrying("is_active", HttpRequestBodyPartTypes::Bool);
            $value = $request->getBodyOrDieTrying("value", HttpRequestBodyPartTypes::String);

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

    public function setIsActive(HttpRequest $request): HttpResponse
    {
        try {
            $response = HttpResponse::make();

            $validationResult = HttpJwtAuthenticationTokenValidator::validate(
                $request->getHeaderOrDieTrying("Authorization"),
                $this->authenticationService
            );

            $this->authorizationService->check(
                $validationResult->getUserSectorPermissionCollection(),
                SectorTypes::Permission,
                PermissionTypes::Activate
            );

            $id = $request->getParamOrDieTrying("id", HttpRouteParameterTypes::Integer);
            $isActive = $request->getBodyOrDieTrying("is_active", HttpRequestBodyPartTypes::Bool);

            $wasUpdated = $this->permissionService->setIsActive(
                Id::make($id),
                $isActive
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
                SectorTypes::Permission,
                PermissionTypes::List
            );

            $id = $request->getParamOrDieTrying("id", HttpRouteParameterTypes::Integer);

            $permission = $this->permissionService->findById(
                Id::make($id)
            );

            $response
                ->setBody([
                    "data" => [
                        "id" => $permission->getIdValue(),
                        "name" => $permission->getNameValue(),
                        "value" => $permission->getPermissionValue(),
                        "is_active" => $permission->getIsActive()
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
                SectorTypes::Permission,
                PermissionTypes::List
            );

            $permissions = $this->permissionService->findAll();

            if ($permissions->count() === 0) {
                $response
                    ->setBody([
                        "message" => "Nothing found!"
                    ])
                    ->setStatusNoContent()
                    ->setContentTypeAsJson();
                return $response;
            }

            $data = [];
            foreach ($permissions->fetchAll() as $permission) {
                $data[] = [
                    "id" => $permission->getIdValue(),
                    "name" => $permission->getNameValue(),
                    "value" => $permission->getPermissionValue(),
                    "is_active" => $permission->getIsActive()
                ];
            }

            $response
                ->setBody([
                    "number_found" => $permissions->count(),
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
