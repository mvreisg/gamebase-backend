<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Controllers;

use Mvreisg\GamebaseBackend\Application\Services\Authentication\AuthenticationService;
use Mvreisg\GamebaseBackend\Application\Services\Genre\Exceptions\GenreServiceDuplicatedNameException;
use Mvreisg\GamebaseBackend\Application\Services\Genre\Exceptions\GenreServiceInvalidIdException;
use Mvreisg\GamebaseBackend\Application\Services\Genre\Exceptions\GenreServiceInvalidNameException;
use Mvreisg\GamebaseBackend\Application\Services\Genre\Exceptions\GenreServiceUnexistantGenreException;
use Mvreisg\GamebaseBackend\Application\Services\Genre\GenreService;
use Mvreisg\GamebaseBackend\Presentation\Http\Entities\HttpRequest;
use Mvreisg\GamebaseBackend\Presentation\Http\Entities\HttpResponse;
use Mvreisg\GamebaseBackend\Presentation\Http\Exceptions\HttpBadRequestException;
use Mvreisg\GamebaseBackend\Presentation\Http\Exceptions\HttpForbiddenException;
use Mvreisg\GamebaseBackend\Presentation\Http\Exceptions\HttpNotFoundException;
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

    public function insert(HttpRequest $request, HttpResponse $response): void
    {
        try {
            HttpJwtAuthenticationTokenValidator::validate(
                $request->getHeaderOrDieTrying('Authorization'),
                $this->authenticationService
            );

            $name = $request->getParsedBodyPartOrDieTrying('name');
            $isActive = $request->getParsedBodyPartOrDieTrying('isActive');

            $genre = $this->genreService->insert($name, $isActive);

            $response
                ->setBody([
                    'data' => [
                        'id' => $genre->getId(),
                        'name' => $genre->getName(),
                        'isActive' => $genre->getIsActive()
                    ]
                ])
                ->setStatusCreated()
                ->sendJson();
        } catch (GenreServiceDuplicatedNameException $e) {
            throw new HttpForbiddenException(
                "Forbidden: {$e->getMessage()}",
                $e
            );
        } catch (GenreServiceInvalidNameException $e) {
            throw new HttpBadRequestException(
                "Bad request: {$e->getMessage()}",
                $e
            );
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function update(HttpRequest $request, HttpResponse $response): void
    {
        try {
            HttpJwtAuthenticationTokenValidator::validate(
                $request->getHeaderOrDieTrying('Authorization'),
                $this->authenticationService
            );

            $id = $request->getParamOrDieTrying('id');
            $name = $request->getParsedBodyPartOrDieTrying('name');
            $isActive = $request->getParsedBodyPartOrDieTrying('isActive');

            $wasUpdated = $this->genreService->update($id, $name, $isActive);

            $response
                ->setBody([
                    'hasChanged' => $wasUpdated
                ])
                ->setStatusOk()
                ->sendJson();
        } catch (GenreServiceDuplicatedNameException $e) {
            throw new HttpForbiddenException(
                "Forbidden: {$e->getMessage()}",
                $e
            );
        } catch (
            GenreServiceInvalidIdException |
            GenreServiceInvalidNameException
            $e
        ) {
            throw new HttpBadRequestException(
                "Bad request: {$e->getMessage()}",
                $e
            );
        } catch (GenreServiceUnexistantGenreException $e) {
            throw new HttpNotFoundException(
                "Not found: {$e->getMessage()}",
                $e
            );
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function setIsActive(HttpRequest $request, HttpResponse $response): void
    {
        try {
            HttpJwtAuthenticationTokenValidator::validate(
                $request->getHeaderOrDieTrying('Authorization'),
                $this->authenticationService
            );

            $id = $request->getParamOrDieTrying('id');
            $isActive = $request->getParsedBodyPartOrDieTrying('isActive');

            $wasUpdated = $this->genreService->setIsActive($id, $isActive);

            $response
                ->setBody([
                    'hasChanged' => $wasUpdated
                ])
                ->setStatusOk()
                ->sendJson();
        } catch (GenreServiceInvalidIdException $e) {
            throw new HttpBadRequestException(
                "Bad request: {$e->getMessage()}",
                $e
            );
        } catch (GenreServiceUnexistantGenreException $e) {
            throw new HttpNotFoundException(
                "Not found: {$e->getMessage()}",
                $e
            );
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findById(HttpRequest $request, HttpResponse $response): void
    {
        try {
            HttpJwtAuthenticationTokenValidator::validate(
                $request->getHeaderOrDieTrying('Authorization'),
                $this->authenticationService
            );

            $id = $request->getParamOrDieTrying('id');

            $genre = $this->genreService->findById($id);

            $response
                ->setBody([
                    'data' => [
                        'id' => $genre->getId(),
                        'name' => $genre->getName(),
                        'isActive' => $genre->getIsActive()
                    ]
                ])
                ->setStatusOk()
                ->sendJson();
        } catch (GenreServiceUnexistantGenreException $e) {
            throw new HttpNotFoundException(
                "Not found: {$e->getMessage()}",
                $e
            );
        } catch (GenreServiceInvalidIdException $e) {
            throw new HttpBadRequestException(
                "Bad request: {$e->getMessage()}",
                $e
            );
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function findAll(HttpRequest $request, HttpResponse $response): void
    {
        try {
            HttpJwtAuthenticationTokenValidator::validate(
                $request->getHeaderOrDieTrying('Authorization'),
                $this->authenticationService
            );

            $genres = $this->genreService->findAll();

            $numberOfGenresFound = count($genres);
            if ($numberOfGenresFound === 0) {
                throw new HttpNotFoundException(
                    "Nothing found!"
                );
            }

            foreach ($genres as $genre) {
                $data[] = [
                    'id' => $genre->getId(),
                    'name' => $genre->getName(),
                    'isActive' => $genre->getIsActive()
                ];
            }

            $response
                ->setBody([
                    'number' => $numberOfGenresFound,
                    'data' => $data
                ])
                ->setStatusOk()
                ->sendJson();
        } catch (HttpNotFoundException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
