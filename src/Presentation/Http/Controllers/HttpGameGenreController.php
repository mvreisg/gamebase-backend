<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Controllers;

use Mvreisg\GamebaseBackend\Application\Services\Authentication\AuthenticationService;
use Mvreisg\GamebaseBackend\Application\Services\GameGenre\GameGenreService;
use Mvreisg\GamebaseBackend\Domain\Data\GameGenre;
use Mvreisg\GamebaseBackend\Domain\Data\Id;
use Mvreisg\GamebaseBackend\Presentation\Http\Entities\HttpRequest;
use Mvreisg\GamebaseBackend\Presentation\Http\Entities\HttpResponse;
use Mvreisg\GamebaseBackend\Presentation\Http\Middlewares\Authentication\Token\Jwt\HttpJwtAuthenticationTokenValidator;

class HttpGameGenreController
{
    private GameGenreService $gameGenreService;
    private AuthenticationService $authenticationService;

    public function __construct(
        GameGenreService $gameGenreService,
        AuthenticationService $authenticationService
    ) {
        $this->gameGenreService = $gameGenreService;
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
            $genreId = $request->getBodyOrDieTrying("genre_id");

            $gameGenre = $this->gameGenreService->insert(
                new GameGenre(
                    Id::make($gameId),
                    Id::make($genreId)
                )
            );

            $data = [
                "id" => $gameGenre->getIdValue(),
                "game_id" => $gameGenre->getGameIdValue(),
                "genre_id" => $gameGenre->getGenreIdValue()
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
            $genreId = $request->getBodyOrDieTrying("genre_id");

            $gameGenre = new GameGenre(
                Id::make($gameId),
                Id::make($genreId)
            );
            $gameGenre->setId(Id::make($id));

            $wasUpdated = $this->gameGenreService->update(
                $gameGenre
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

            $wasDeleted = $this->gameGenreService->delete(
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

            $gameGenre = $this->gameGenreService->findById(
                Id::make($id)
            );

            $response
                ->setBody([
                    "data" => [
                        "id" => $gameGenre->getIdValue(),
                        "game_id" => $gameGenre->getGameIdValue(),
                        "genre_id" => $gameGenre->getGenreIdValue()
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

            $gameGenres = $this->gameGenreService->findAll();

            if ($gameGenres->isEmpty()) {
                $response
                    ->setBody([
                        "message" => "Nothing found!"
                    ])
                    ->setStatusOk()
                    ->setContentTypeAsJson();
                return $response;
            }

            $data = [];
            foreach ($gameGenres->fetchAll() as $gameGenre) {
                $data[] = [
                    "id" => $gameGenre->getIdValue(),
                    "game_id" => $gameGenre->getGameIdValue(),
                    "genre_id" => $gameGenre->getGenreIdValue()
                ];
            }

            $response
                ->setBody([
                    "number_found" => $gameGenres->count(),
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
