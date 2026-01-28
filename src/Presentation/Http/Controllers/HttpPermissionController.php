<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Controllers;

use Mvreisg\GamebaseBackend\Application\Services\Authentication\AuthenticationService;
use Mvreisg\GamebaseBackend\Application\Services\Permission\PermissionService;
use Mvreisg\GamebaseBackend\Domain\Data\Id;
use Mvreisg\GamebaseBackend\Domain\Data\Name;
use Mvreisg\GamebaseBackend\Domain\Data\Permission;
use Mvreisg\GamebaseBackend\Presentation\Http\Entities\HttpRequest;
use Mvreisg\GamebaseBackend\Presentation\Http\Entities\HttpResponse;
use Mvreisg\GamebaseBackend\Presentation\Http\Middlewares\Authentication\Token\Jwt\HttpJwtAuthenticationTokenValidator;

class HttpPermissionController
{
    private PermissionService $permissionService;
    private AuthenticationService $authenticationService;

    public function __construct(
        PermissionService $permissionService,
        AuthenticationService $authenticationService
    ) {
        $this->permissionService = $permissionService;
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

            $name = $request->getBodyOrDieTrying("name");
            $isActive = $request->getBodyOrDieTrying("is_active");

            $permission = $this->permissionService->insert(
                new Permission(
                    null,
                    Name::make($name),
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

            HttpJwtAuthenticationTokenValidator::validate(
                $request->getHeaderOrDieTrying("Authorization"),
                $this->authenticationService
            );

            $id = $request->getParamOrDieTrying("id");
            $name = $request->getBodyOrDieTrying("name");
            $isActive = $request->getBodyOrDieTrying("is_active");

            $wasUpdated = $this->permissionService->update(
                new Permission(
                    Id::make($id),
                    Name::make($name),
                    $isActive
                )
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

            HttpJwtAuthenticationTokenValidator::validate(
                $request->getHeaderOrDieTrying("Authorization"),
                $this->authenticationService
            );

            $id = $request->getParamOrDieTrying("id");
            $isActive = $request->getBodyOrDieTrying("is_active");

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

            HttpJwtAuthenticationTokenValidator::validate(
                $request->getHeaderOrDieTrying("Authorization"),
                $this->authenticationService
            );

            $id = $request->getParamOrDieTrying("id");

            $permission = $this->permissionService->findById(
                Id::make($id)
            );

            $response
                ->setBody([
                    "data" => [
                        "id" => $permission->getIdValue(),
                        "name" => $permission->getNameValue(),
                        "isActive" => $permission->getIsActive()
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
                    "isActive" => $permission->getIsActive()
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
