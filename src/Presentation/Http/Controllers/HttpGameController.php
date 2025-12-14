<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Controllers;

use Mvreisg\GamebaseBackend\Application\Services\Authentication\AuthenticationService;
use Mvreisg\GamebaseBackend\Application\Services\Game\Exceptions\GameServiceDuplicatedNameException;
use Mvreisg\GamebaseBackend\Application\Services\Game\Exceptions\GameServiceInvalidIdException;
use Mvreisg\GamebaseBackend\Application\Services\Game\Exceptions\GameServiceInvalidNameException;
use Mvreisg\GamebaseBackend\Application\Services\Game\Exceptions\GameServiceUnexistantGameException;
use Mvreisg\GamebaseBackend\Application\Services\Game\GameService;
use Mvreisg\GamebaseBackend\Presentation\Http\Entities\HttpRequest;
use Mvreisg\GamebaseBackend\Presentation\Http\Entities\HttpResponse;
use Mvreisg\GamebaseBackend\Presentation\Http\Exceptions\HttpBadRequestException;
use Mvreisg\GamebaseBackend\Presentation\Http\Exceptions\HttpForbiddenException;
use Mvreisg\GamebaseBackend\Presentation\Http\Exceptions\HttpInternalServerError;
use Mvreisg\GamebaseBackend\Presentation\Http\Exceptions\HttpNotFoundException;
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

    public function insert(HttpRequest $request, HttpResponse $response): void
    {
        try {
            HttpJwtAuthenticationTokenValidator::validate(
                $request->getHeaderOrDieTrying('Authorization'),
                $this->authenticationService
            );

            $name = $request->getParsedBodyPartOrDieTrying('name');
            $isActive = $request->getParsedBodyPartOrDieTrying('isActive');

            $game = $this->gameService->insert($name, $isActive);

            $data = [
                'id' => $game->getId(),
                'name' => $game->getName(),
                'isActive' => $game->getIsActive()
            ];

            $response
                ->setBody([
                    'data' => $data
                ])
                ->setStatusCreated()
                ->sendJson();
        } catch (GameServiceDuplicatedNameException $e) {
            throw new HttpForbiddenException(
                "Forbidden: {$e->getMessage()}",
                $e
            );
        } catch (GameServiceInvalidNameException $e) {
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

            $wasUpdated = $this->gameService->update($id, $name, $isActive);

            $response
                ->setBody([
                    'hasChanged' => $wasUpdated
                ])
                ->setStatusOk()
                ->sendJson();
        } catch (GameServiceDuplicatedNameException $e) {
            throw new HttpForbiddenException(
                "Forbidden: {$e->getMessage()}",
                $e
            );
        } catch (
            GameServiceInvalidIdException |
            GameServiceInvalidNameException
            $e
        ) {
            throw new HttpBadRequestException(
                "Bad request: {$e->getMessage()}",
                $e
            );
        } catch (GameServiceUnexistantGameException $e) {
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

            $wasUpdated = $this->gameService->setIsActive($id, $isActive);
            $response
                ->setBody([
                    'hasChanged' => $wasUpdated
                ])
                ->setStatusOk()
                ->sendJson();
        } catch (GameServiceInvalidIdException $e) {
            throw new HttpBadRequestException(
                "Bad request: {$e->getMessage()}",
                $e
            );
        } catch (GameServiceUnexistantGameException $e) {
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

            $game = $this->gameService->findById($id);

            $response
                ->setBody([
                    'data' => [
                        'id' => $game->getId(),
                        'name' => $game->getName(),
                        'isActive' => $game->getIsActive()
                    ]
                ])
                ->setStatusOk()
                ->sendJson();
        } catch (GameServiceUnexistantGameException $e) {
            throw new HttpNotFoundException(
                "Not found: {$e->getMessage()}",
                $e
            );
        } catch (GameServiceInvalidIdException $e) {
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

            $games = $this->gameService->findAll();

            $numberOfGamesFound = count($games);
            if ($numberOfGamesFound === 0) {
                throw new HttpNotFoundException(
                    "Nothing found!"
                );
            }

            foreach ($games as $game) {
                $data[] = [
                    'id' => $game->getId(),
                    'name' => $game->getName(),
                    'isActive' => $game->getIsActive()
                ];
            }

            $response
                ->setBody([
                    'number' => $numberOfGamesFound,
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
