<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Controllers;

use Mvreisg\GamebaseBackend\Application\Services\Authentication\AuthenticationService;
use Mvreisg\GamebaseBackend\Application\Services\Authorization\AuthorizationService;
use Mvreisg\GamebaseBackend\Application\Services\GamePlatform\GamePlatformService;
use Mvreisg\GamebaseBackend\Domain\Authorization\Enums\PermissionTypes;
use Mvreisg\GamebaseBackend\Domain\Authorization\Enums\SectorTypes;
use Mvreisg\GamebaseBackend\Domain\Entities\GamePlatform;
use Mvreisg\GamebaseBackend\Domain\Entities\Id;
use Mvreisg\GamebaseBackend\Presentation\Http\Entities\HttpRequest;
use Mvreisg\GamebaseBackend\Presentation\Http\Entities\HttpResponse;
use Mvreisg\GamebaseBackend\Presentation\Http\Enums\HttpRequestBodyPartTypes;
use Mvreisg\GamebaseBackend\Presentation\Http\Enums\HttpRouteParameterTypes;
use Mvreisg\GamebaseBackend\Presentation\Http\Middlewares\Authentication\Token\Jwt\HttpJwtAuthenticationTokenValidator;

class HttpGamePlatformController
{
    private GamePlatformService $gamePlatformService;
    private AuthenticationService $authenticationService;
    private AuthorizationService $authorizationService;

    public function __construct(
        GamePlatformService $gamePlatformService,
        AuthenticationService $authenticationService,
        AuthorizationService $authorizationService
    ) {
        $this->gamePlatformService = $gamePlatformService;
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
                SectorTypes::GamePlatform,
                PermissionTypes::Create
            );

            $gameId = $request->getBodyOrDieTrying("game_id", HttpRequestBodyPartTypes::Int);
            $platformId = $request->getBodyOrDieTrying("platform_id", HttpRequestBodyPartTypes::Int);

            $gamePlatform = $this->gamePlatformService->insert(
                new GamePlatform(
                    Id::make($platformId),
                    Id::make($gameId)
                )
            );

            $data = [
                "id" => $gamePlatform->getIdValue(),
                "game_id" => $gamePlatform->getGameIdValue(),
                "platform_id" => $gamePlatform->getPlatformIdValue()
            ];

            $response
                ->setBody([
                    "data" => $data
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
                SectorTypes::GamePlatform,
                PermissionTypes::Update
            );

            $id = $request->getParamOrDieTrying("id", HttpRouteParameterTypes::Integer);
            $gameId = $request->getBodyOrDieTrying("game_id", HttpRequestBodyPartTypes::Int);
            $platformId = $request->getBodyOrDieTrying("platform_id", HttpRequestBodyPartTypes::Int);

            $gamePlatform = new GamePlatform(
                Id::make($platformId),
                Id::make($gameId)
            );
            $gamePlatform->setId(Id::make($id));

            $wasUpdated = $this->gamePlatformService->update(
                $gamePlatform
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
                SectorTypes::GamePlatform,
                PermissionTypes::Delete
            );

            $id = $request->getParamOrDieTrying("id", HttpRouteParameterTypes::Integer);

            $wasDeleted = $this->gamePlatformService->delete(
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
                SectorTypes::GamePlatform,
                PermissionTypes::List
            );

            $id = $request->getParamOrDieTrying("id", HttpRouteParameterTypes::Integer);

            $gamePlatform = $this->gamePlatformService->findById(
                Id::make($id)
            );

            $response
                ->setBody([
                    "data" => [
                        "id" => $gamePlatform->getIdValue(),
                        "game_id" => $gamePlatform->getGameIdValue(),
                        "platform_id" => $gamePlatform->getPlatformIdValue()
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
                SectorTypes::GamePlatform,
                PermissionTypes::List
            );

            $gamePlatforms = $this->gamePlatformService->findAll();

            if ($gamePlatforms->isEmpty()) {
                $response
                    ->setBody([
                        "message" => "Nothing found!"
                    ])
                    ->setStatusOk()
                    ->setContentTypeAsJson();
                return $response;
            }

            $data = [];
            foreach ($gamePlatforms->fetchAll() as $gamePlatform) {
                $data[] = [
                    "id" => $gamePlatform->getIdValue(),
                    "game_id" => $gamePlatform->getGameIdValue(),
                    "platform_id" => $gamePlatform->getPlatformIdValue()
                ];
            }

            $response
                ->setBody([
                    "number_found" => $gamePlatforms->count(),
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
