<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Controllers;

use Mvreisg\GamebaseBackend\Application\Services\Authentication\AuthenticationService;
use Mvreisg\GamebaseBackend\Application\Services\Authorization\AuthorizationService;
use Mvreisg\GamebaseBackend\Application\Services\Platform\PlatformService;
use Mvreisg\GamebaseBackend\Domain\Authorization\Enums\PermissionTypes;
use Mvreisg\GamebaseBackend\Domain\Authorization\Enums\SectorTypes;
use Mvreisg\GamebaseBackend\Domain\Data\Id;
use Mvreisg\GamebaseBackend\Domain\Data\Name;
use Mvreisg\GamebaseBackend\Domain\Data\Platform;
use Mvreisg\GamebaseBackend\Presentation\Http\Entities\HttpRequest;
use Mvreisg\GamebaseBackend\Presentation\Http\Entities\HttpResponse;
use Mvreisg\GamebaseBackend\Presentation\Http\Enums\HttpRequestBodyPartTypes;
use Mvreisg\GamebaseBackend\Presentation\Http\Enums\HttpRouteParameterTypes;
use Mvreisg\GamebaseBackend\Presentation\Http\Middlewares\Authentication\Token\Jwt\HttpJwtAuthenticationTokenValidator;

class HttpPlatformController
{
    private PlatformService $platformService;
    private AuthenticationService $authenticationService;
    private AuthorizationService $authorizationService;

    public function __construct(
        PlatformService $platformService,
        AuthenticationService $authenticationService,
        AuthorizationService $authorizationService
    ) {
        $this->platformService = $platformService;
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
                SectorTypes::Platform,
                PermissionTypes::Create
            );

            $name = $request->getBodyOrDieTrying("name", HttpRequestBodyPartTypes::String);
            $isActive = $request->getBodyOrDieTrying("is_active", HttpRequestBodyPartTypes::Bool);

            $platform = $this->platformService->insert(
                new Platform(
                    Name::make($name),
                    $isActive
                )
            );

            $response
                ->setBody([
                    "data" => [
                        "id" => $platform->getIdValue(),
                        "name" => $platform->getNameValue(),
                        "is_active" => $platform->getIsActive()
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
                SectorTypes::Platform,
                PermissionTypes::Update
            );

            $id = $request->getParamOrDieTrying("id", HttpRouteParameterTypes::Integer);
            $name = $request->getBodyOrDieTrying("name", HttpRequestBodyPartTypes::String);
            $isActive = $request->getBodyOrDieTrying("is_active", HttpRequestBodyPartTypes::Bool);

            $platform = new Platform(
                Name::make($name),
                $isActive
            );
            $platform->setId(Id::make($id));

            $wasUpdated = $this->platformService->update(
                $platform
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
                SectorTypes::Platform,
                PermissionTypes::Activate
            );

            $id = $request->getParamOrDieTrying("id", HttpRouteParameterTypes::Integer);
            $isActive = $request->getBodyOrDieTrying("is_active", HttpRequestBodyPartTypes::Bool);

            $wasUpdated = $this->platformService->setIsActive(
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
                SectorTypes::Platform,
                PermissionTypes::List
            );

            $id = $request->getParamOrDieTrying("id", HttpRouteParameterTypes::Integer);

            $platform = $this->platformService->findById(
                Id::make($id)
            );

            $response
                ->setBody([
                    "data" => [
                        "id" => $platform->getIdValue(),
                        "name" => $platform->getNameValue(),
                        "is_active" => $platform->getIsActive()
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
                SectorTypes::Platform,
                PermissionTypes::List
            );

            $platforms = $this->platformService->findAll();

            if ($platforms->count() === 0) {
                $response
                    ->setBody([
                        "message" => "Nothing found!"
                    ])
                    ->setStatusNoContent()
                    ->setContentTypeAsJson();
                return $response;
            }

            $data = [];
            foreach ($platforms->fetchAll() as $platform) {
                $data[] = [
                    "id" => $platform->getIdValue(),
                    "name" => $platform->getNameValue(),
                    "is_active" => $platform->getIsActive()
                ];
            }

            $response
                ->setBody([
                    "number_found" => $platforms->count(),
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
