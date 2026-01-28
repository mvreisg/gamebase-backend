<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Controllers;

use Mvreisg\GamebaseBackend\Application\Services\Authentication\AuthenticationService;
use Mvreisg\GamebaseBackend\Application\Services\Genre\GenreService;
use Mvreisg\GamebaseBackend\Domain\Data\Genre;
use Mvreisg\GamebaseBackend\Domain\Data\Id;
use Mvreisg\GamebaseBackend\Domain\Data\Name;
use Mvreisg\GamebaseBackend\Presentation\Http\Entities\HttpRequest;
use Mvreisg\GamebaseBackend\Presentation\Http\Entities\HttpResponse;
use Mvreisg\GamebaseBackend\Presentation\Http\Middlewares\Authentication\Token\Jwt\HttpJwtAuthenticationTokenValidator;

class HttpGenreController
{
    private GenreService $genreService;
    private AuthenticationService $authenticationService;

    public function __construct(
        GenreService $genreService,
        AuthenticationService $authenticationService
    ) {
        $this->genreService = $genreService;
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

            $genre = $this->genreService->insert(
                new Genre(
                    null,
                    Name::make($name),
                    $isActive
                )
            );

            $response
                ->setBody([
                    "data" => [
                        "id" => $genre->getIdValue(),
                        "name" => $genre->getNameValue(),
                        "isActive" => $genre->getIsActive()
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

            $wasUpdated = $this->genreService->update(
                new Genre(
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

            $wasUpdated = $this->genreService->setIsActive(
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

            $genre = $this->genreService->findById(
                Id::make($id)
            );

            $response
                ->setBody([
                    "data" => [
                        "id" => $genre->getIdValue(),
                        "name" => $genre->getNameValue(),
                        "isActive" => $genre->getIsActive()
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

            $genres = $this->genreService->findAll();

            if ($genres->count() === 0) {
                $response
                    ->setBody([
                        "message" => "Nothing found!"
                    ])
                    ->setStatusNoContent()
                    ->setContentTypeAsJson();
                return $response;
            }

            foreach ($genres->fetchAll() as $genre) {
                $data[] = [
                    "id" => $genre->getIdValue(),
                    "name" => $genre->getNameValue(),
                    "isActive" => $genre->getIsActive()
                ];
            }

            $response
                ->setBody([
                    "number_found" => $genres->count(),
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
