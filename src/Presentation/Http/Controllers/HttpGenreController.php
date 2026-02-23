<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Controllers;

use Mvreisg\GamebaseBackend\Application\Services\Authentication\AuthenticationService;
use Mvreisg\GamebaseBackend\Application\Services\Authorization\AuthorizationService;
use Mvreisg\GamebaseBackend\Application\Services\Genre\GenreService;
use Mvreisg\GamebaseBackend\Domain\Authorization\Enums\PermissionTypes;
use Mvreisg\GamebaseBackend\Domain\Authorization\Enums\SectorTypes;
use Mvreisg\GamebaseBackend\Domain\Data\Genre;
use Mvreisg\GamebaseBackend\Domain\Data\Id;
use Mvreisg\GamebaseBackend\Domain\Data\Name;
use Mvreisg\GamebaseBackend\Presentation\Http\Entities\HttpRequest;
use Mvreisg\GamebaseBackend\Presentation\Http\Entities\HttpResponse;
use Mvreisg\GamebaseBackend\Presentation\Http\Enums\HttpRequestBodyPartTypes;
use Mvreisg\GamebaseBackend\Presentation\Http\Enums\HttpRouteParameterTypes;
use Mvreisg\GamebaseBackend\Presentation\Http\Middlewares\Authentication\Token\Jwt\HttpJwtAuthenticationTokenValidator;

class HttpGenreController
{
    private GenreService $genreService;
    private AuthenticationService $authenticationService;
    private AuthorizationService $authorizationService;

    public function __construct(
        GenreService $genreService,
        AuthenticationService $authenticationService,
        AuthorizationService $authorizationService
    ) {
        $this->genreService = $genreService;
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
                SectorTypes::Genre,
                PermissionTypes::Create
            );

            $name = $request->getBodyOrDieTrying("name", HttpRequestBodyPartTypes::String);
            $isActive = $request->getBodyOrDieTrying("is_active", HttpRequestBodyPartTypes::Bool);

            $genre = $this->genreService->insert(
                new Genre(
                    Name::make($name),
                    $isActive
                )
            );

            $response
                ->setBody([
                    "data" => [
                        "id" => $genre->getIdValue(),
                        "name" => $genre->getNameValue(),
                        "is_active" => $genre->getIsActive()
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
                SectorTypes::Genre,
                PermissionTypes::Update
            );

            $id = $request->getParamOrDieTrying("id", HttpRouteParameterTypes::Integer);
            $name = $request->getBodyOrDieTrying("name", HttpRequestBodyPartTypes::String);
            $isActive = $request->getBodyOrDieTrying("is_active", HttpRequestBodyPartTypes::Bool);

            $genre = new Genre(
                Name::make($name),
                $isActive
            );
            $genre->setId(Id::make($id));

            $wasUpdated = $this->genreService->update(
                $genre
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
                SectorTypes::Genre,
                PermissionTypes::Activate
            );

            $id = $request->getParamOrDieTrying("id", HttpRouteParameterTypes::Integer);
            $isActive = $request->getBodyOrDieTrying("is_active", HttpRequestBodyPartTypes::Bool);

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

            $validationResult = HttpJwtAuthenticationTokenValidator::validate(
                $request->getHeaderOrDieTrying("Authorization"),
                $this->authenticationService
            );

            $this->authorizationService->check(
                $validationResult->getUserSectorPermissionCollection(),
                SectorTypes::Genre,
                PermissionTypes::List
            );

            $id = $request->getParamOrDieTrying("id", HttpRouteParameterTypes::Integer);

            $genre = $this->genreService->findById(
                Id::make($id)
            );

            $response
                ->setBody([
                    "data" => [
                        "id" => $genre->getIdValue(),
                        "name" => $genre->getNameValue(),
                        "is_active" => $genre->getIsActive()
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
                SectorTypes::Genre,
                PermissionTypes::List
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
                    "is_active" => $genre->getIsActive()
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
