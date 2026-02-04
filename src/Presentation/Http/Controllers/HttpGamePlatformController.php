<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Controllers;

use Mvreisg\GamebaseBackend\Application\Services\Authentication\AuthenticationService;
use Mvreisg\GamebaseBackend\Application\Services\GamePlatform\GamePlatformService;
use Mvreisg\GamebaseBackend\Domain\Data\GamePlatform;
use Mvreisg\GamebaseBackend\Domain\Data\Id;
use Mvreisg\GamebaseBackend\Presentation\Http\Entities\HttpRequest;
use Mvreisg\GamebaseBackend\Presentation\Http\Entities\HttpResponse;
use Mvreisg\GamebaseBackend\Presentation\Http\Middlewares\Authentication\Token\Jwt\HttpJwtAuthenticationTokenValidator;

class HttpGamePlatformController
{
    private GamePlatformService $gamePlatformService;
    private AuthenticationService $authenticationService;

    public function __construct(
        GamePlatformService $gamePlatformService,
        AuthenticationService $authenticationService
    ) {
        $this->gamePlatformService = $gamePlatformService;
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

            $gameId = $request->getBodyOrDieTrying("game_id");
            $platformId = $request->getBodyOrDieTrying("platform_id");

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

            HttpJwtAuthenticationTokenValidator::validate(
                $request->getHeaderOrDieTrying("Authorization"),
                $this->authenticationService
            );

            $id = $request->getParamOrDieTrying("id");
            $gameId = $request->getBodyOrDieTrying("game_id");
            $platformId = $request->getBodyOrDieTrying("platform_id");

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

            HttpJwtAuthenticationTokenValidator::validate(
                $request->getHeaderOrDieTrying("Authorization"),
                $this->authenticationService
            );

            $id = $request->getParamOrDieTrying("id");

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

            HttpJwtAuthenticationTokenValidator::validate(
                $request->getHeaderOrDieTrying("Authorization"),
                $this->authenticationService
            );

            $id = $request->getParamOrDieTrying("id");

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

            HttpJwtAuthenticationTokenValidator::validate(
                $request->getHeaderOrDieTrying("Authorization"),
                $this->authenticationService
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
