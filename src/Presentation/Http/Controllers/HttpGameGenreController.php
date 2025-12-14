<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Controllers;

use Mvreisg\GamebaseBackend\Application\Services\Authentication\AuthenticationService;
use Mvreisg\GamebaseBackend\Application\Services\GameGenre\Exceptions\GameGenreServiceInvalidGameIdException;
use Mvreisg\GamebaseBackend\Application\Services\GameGenre\Exceptions\GameGenreServiceInvalidGenreIdException;
use Mvreisg\GamebaseBackend\Application\Services\GameGenre\Exceptions\GameGenreServiceInvalidIdException;
use Mvreisg\GamebaseBackend\Application\Services\GameGenre\Exceptions\GameGenreServiceUnexistantGameException;
use Mvreisg\GamebaseBackend\Application\Services\GameGenre\Exceptions\GameGenreServiceUnexistantGameGenreException;
use Mvreisg\GamebaseBackend\Application\Services\GameGenre\Exceptions\GameGenreServiceUnexistantGenreException;
use Mvreisg\GamebaseBackend\Application\Services\GameGenre\GameGenreService;
use Mvreisg\GamebaseBackend\Presentation\Http\Entities\HttpRequest;
use Mvreisg\GamebaseBackend\Presentation\Http\Entities\HttpResponse;
use Mvreisg\GamebaseBackend\Presentation\Http\Exceptions\HttpBadRequestException;
use Mvreisg\GamebaseBackend\Presentation\Http\Exceptions\HttpNotFoundException;
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

    public function insert(HttpRequest $request, HttpResponse $response): void
    {
        try {
            HttpJwtAuthenticationTokenValidator::validate(
                $request->getHeaderOrDieTrying('Authorization'),
                $this->authenticationService
            );

            $gameId = $request->getParsedBodyPartOrDieTrying('gameId');
            $genreId = $request->getParsedBodyPartOrDieTrying('genreId');

            $gameGenre = $this->gameGenreService->insert($gameId, $genreId);

            $data = [
                'id' => $gameGenre->getId(),
                'gameId' => $gameGenre->getGameId(),
                'genreId' => $gameGenre->getGenreId()
            ];

            $response
                ->setBody([
                    'data' => $data
                ])
                ->setStatusCreated()
                ->sendJson();
        } catch (
            GameGenreServiceUnexistantGameException |
            GameGenreServiceUnexistantGenreException
            $e
        ) {
            throw new HttpNotFoundException(
                "Not found: {$e->getMessage()}",
                $e
            );
        } catch (
            GameGenreServiceInvalidGameIdException |
            GameGenreServiceInvalidGenreIdException
            $e
        ) {
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
            $gameId = $request->getParsedBodyPartOrDieTrying('gameId');
            $genreId = $request->getParsedBodyPartOrDieTrying('genreId');

            $wasUpdated = $this->gameGenreService->update($id, $gameId, $genreId);
            $response
                ->setBody([
                    'hasChanged' => $wasUpdated
                ])
                ->setStatusOk()
                ->sendJson();
        } catch (
            GameGenreServiceInvalidIdException |
            GameGenreServiceInvalidGameIdException |
            GameGenreServiceInvalidGenreIdException
            $e
        ) {
            throw new HttpBadRequestException(
                "Bad request: {$e->getMessage()}",
                $e
            );
        } catch (
            GameGenreServiceUnexistantGameException |
            GameGenreServiceUnexistantGenreException |
            GameGenreServiceUnexistantGameGenreException
            $e
        ) {
            throw new HttpNotFoundException(
                "Not found: {$e->getMessage()}",
                $e
            );
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function delete(HttpRequest $request, HttpResponse $response): void
    {
        try {
            HttpJwtAuthenticationTokenValidator::validate(
                $request->getHeaderOrDieTrying('Authorization'),
                $this->authenticationService
            );

            $id = $request->getParamOrDieTrying('id');

            $wasDeleted = $this->gameGenreService->delete($id);
            $response
                ->setBody([
                    'wasDeleted' => $wasDeleted
                ])
                ->setStatusOk()
                ->sendJson();
        } catch (GameGenreServiceInvalidIdException $e) {
            throw new HttpBadRequestException(
                "Bad request: {$e->getMessage()}",
                $e
            );
        } catch (GameGenreServiceUnexistantGameGenreException $e) {
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

            $gameGenre = $this->gameGenreService->findById($id);

            if ($gameGenre === null) {
                throw new HttpNotFoundException(
                    "Game genre with the id $id not found!"
                );
            }

            $response
                ->setBody([
                    'data' => [
                        'id' => $gameGenre->getId(),
                        'gameId' => $gameGenre->getGameId(),
                        'genreId' => $gameGenre->getGenreId()
                    ]
                ])
                ->setStatusOk()
                ->sendJson();
        } catch (HttpNotFoundException $e) {
            throw $e;
        } catch (GameGenreServiceInvalidIdException $e) {
            throw new HttpBadRequestException(
                "Bad request: {$e->getMessage()}",
                $e
            );
        } catch (GameGenreServiceUnexistantGameGenreException $e) {
            throw new HttpNotFoundException(
                "Not found: {$e->getMessage()}",
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

            $gameGenres = $this->gameGenreService->findAll();

            $numberOfGameGenres = count($gameGenres);
            if ($numberOfGameGenres === 0) {
                throw new HttpNotFoundException(
                    'No game genres found!'
                );
            }

            $data = [];
            foreach ($gameGenres as $gameGenre) {
                $data[] = [
                    'id' => $gameGenre->getId(),
                    'gameId' => $gameGenre->getGameId(),
                    'genreId' => $gameGenre->getGenreId()
                ];
            }

            $response
                ->setBody([
                    'number' => $numberOfGameGenres,
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
