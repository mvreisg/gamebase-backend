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

            $name = $request->getBodyOrDieTrying("name");
            $isActive = $request->getBodyOrDieTrying("is_active");

            $game = $this->gameService->insert(
                new Game(
                    null,
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

            $id = $request->getParamOrDieTrying("id");
            $name = $request->getBodyOrDieTrying("name");
            $isActive = $request->getBodyOrDieTrying("is_active");

            $wasUpdated = $this->gameService->update(
                new Game(
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

            $id = $request->getParamOrDieTrying("id");

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
                    "isActive" => $game->getIsActive()
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
