<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Controllers;

use Mvreisg\GamebaseBackend\Application\Services\Authentication\AuthenticationService;
use Mvreisg\GamebaseBackend\Application\Services\Game\GameService;
use Mvreisg\GamebaseBackend\Domain\Data\Game;
use Mvreisg\GamebaseBackend\Domain\Data\Id;
use Mvreisg\GamebaseBackend\Domain\Data\Name;
use Mvreisg\GamebaseBackend\Presentation\Http\Entities\HttpRequest;
use Mvreisg\GamebaseBackend\Presentation\Http\Entities\HttpResponse;
use Mvreisg\GamebaseBackend\Presentation\Http\Enums\HttpRequestBodyPartTypes;
use Mvreisg\GamebaseBackend\Presentation\Http\Enums\HttpRouteParameterTypes;
use Mvreisg\GamebaseBackend\Presentation\Http\Middlewares\Authentication\Token\Jwt\HttpJwtAuthenticationTokenValidator;

class HttpGameController
{
    private GameService $gameService;
    private AuthenticationService $authenticationService;

    public function __construct(
        GameService $gameService,
        AuthenticationService $authenticationService
    ) {
        $this->gameService = $gameService;
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

            $name = $request->getBodyOrDieTrying("name", HttpRequestBodyPartTypes::String);
            $isActive = $request->getBodyOrDieTrying("is_active", HttpRequestBodyPartTypes::Bool);

            $game = $this->gameService->insert(
                new Game(
                    Name::make($name),
                    $isActive
                )
            );

            $data = [
                "id" => $game->getIdValue(),
                "name" => $game->getNameValue(),
                "is_active" => $game->getIsActive()
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

            $id = $request->getParamOrDieTrying("id", HttpRouteParameterTypes::Integer);
            $name = $request->getBodyOrDieTrying("name", HttpRequestBodyPartTypes::String);
            $isActive = $request->getBodyOrDieTrying("is_active", HttpRequestBodyPartTypes::Bool);

            $game = new Game(
                Name::make($name),
                $isActive
            );
            $game->setId(Id::make($id));

            $wasUpdated = $this->gameService->update(
                $game
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

            $id = $request->getParamOrDieTrying("id", HttpRouteParameterTypes::Integer);
            $isActive = $request->getBodyOrDieTrying("is_active", HttpRequestBodyPartTypes::Bool);

            $wasUpdated = $this->gameService->setIsActive(
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

            $id = $request->getParamOrDieTrying("id", HttpRouteParameterTypes::Integer);

            $game = $this->gameService->findById(
                Id::make($id)
            );

            $response
                ->setBody([
                    "data" => [
                        "id" => $game->getIdValue(),
                        "name" => $game->getNameValue(),
                        "is_active" => $game->getIsActive()
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

            $games = $this->gameService->findAll();

            if ($games->count() === 0) {
                $response
                    ->setBody([
                        "message" => "Nothing found!"
                    ])
                    ->setStatusNoContent()
                    ->setContentTypeAsJson();
                return $response;
            }

            foreach ($games->fetchAll() as $game) {
                $data[] = [
                    "id" => $game->getIdValue(),
                    "name" => $game->getNameValue(),
                    "is_active" => $game->getIsActive()
                ];
            }

            $response
                ->setBody([
                    "number_found" => $games->count(),
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
