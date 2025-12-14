<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Controllers;

use Mvreisg\GamebaseBackend\Application\Exceptions\Authentication\AuthenticationException;
use Mvreisg\GamebaseBackend\Application\Services\Authentication\AuthenticationService;
use Mvreisg\GamebaseBackend\Application\Services\GamePlatform\Exceptions\GamePlatformServiceInvalidGameIdException;
use Mvreisg\GamebaseBackend\Application\Services\GamePlatform\Exceptions\GamePlatformServiceInvalidIdException;
use Mvreisg\GamebaseBackend\Application\Services\GamePlatform\Exceptions\GamePlatformServiceInvalidPlatformIdException;
use Mvreisg\GamebaseBackend\Application\Services\GamePlatform\Exceptions\GamePlatformServiceUnexistantGameException;
use Mvreisg\GamebaseBackend\Application\Services\GamePlatform\Exceptions\GamePlatformServiceUnexistantGamePlatformException;
use Mvreisg\GamebaseBackend\Application\Services\GamePlatform\Exceptions\GamePlatformServiceUnexistantPlatformException;
use Mvreisg\GamebaseBackend\Application\Services\GamePlatform\GamePlatformService;
use Mvreisg\GamebaseBackend\Presentation\Http\Entities\HttpRequest;
use Mvreisg\GamebaseBackend\Presentation\Http\Entities\HttpResponse;
use Mvreisg\GamebaseBackend\Presentation\Http\Router\HttpRouter;
use Mvreisg\GamebaseBackend\Presentation\Http\Exceptions\HttpInvalidParameterException;
use Mvreisg\GamebaseBackend\Presentation\Http\Exceptions\HttpUnauthorizedException;
use Mvreisg\GamebaseBackend\Presentation\Http\Exceptions\HttpUndefinedValueException;
use Mvreisg\GamebaseBackend\Presentation\Http\Enums\HttpContentTypesEnum;
use Mvreisg\GamebaseBackend\Presentation\Http\Enums\HttpStatusCodeTypesEnum;
use Mvreisg\GamebaseBackend\Presentation\Http\Exceptions\HttpBadRequestException;
use Mvreisg\GamebaseBackend\Presentation\Http\Exceptions\HttpNotFoundException;
use Mvreisg\GamebaseBackend\Presentation\Http\Middlewares\Authentication\Token\Jwt\HttpJwtAuthenticationTokenValidator;
use Mvreisg\GamebaseBackend\Presentation\Http\Middlewares\HttpJwtAuthenticationTokenRetriever;

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

    public function insert(HttpRequest $request, HttpResponse $response): void
    {
        try {
            HttpJwtAuthenticationTokenValidator::validate(
                $request->getHeaderOrDieTrying('Authorization'),
                $this->authenticationService
            );

            $gameId = $request->getParsedBodyPartOrDieTrying('gameId');
            $platformId = $request->getParsedBodyPartOrDieTrying('platformId');

            $gamePlatform = $this->gamePlatformService->insert($gameId, $platformId);

            $data = [
                'id' => $gamePlatform->getId(),
                'gameId' => $gamePlatform->getGameId(),
                'platformId' => $gamePlatform->getPlatformId()
            ];

            $response
                ->setBody([
                    'data' => $data
                ])
                ->setStatusCreated()
                ->sendJson();
        } catch (
            GamePlatformServiceUnexistantGameException |
            GamePlatformServiceUnexistantPlatformException
            $e
        ) {
            throw new HttpNotFoundException(
                "Not found: {$e->getMessage()}",
                $e
            );
        } catch (
            GamePlatformServiceInvalidGameIdException |
            GamePlatformServiceInvalidPlatformIdException
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
            $platformId = $request->getParsedBodyPartOrDieTrying('platformId');

            $wasUpdated = $this->gamePlatformService->update($id, $gameId, $platformId);
            $response
                ->setBody([
                    'hasChanged' => $wasUpdated
                ])
                ->setStatusOk()
                ->sendJson();
        } catch (
            GamePlatformServiceInvalidIdException |
            GamePlatformServiceInvalidGameIdException |
            GamePlatformServiceInvalidPlatformIdException
            $e
        ) {
            throw new HttpBadRequestException(
                "Bad request: {$e->getMessage()}",
                $e
            );
        } catch (
            GamePlatformServiceUnexistantGameException |
            GamePlatformServiceUnexistantPlatformException |
            GamePlatformServiceUnexistantGamePlatformException
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

            $wasDeleted = $this->gamePlatformService->delete($id);
            $response
                ->setBody([
                    'wasDeleted' => $wasDeleted
                ])
                ->setStatusOk()
                ->sendJson();
        } catch (GamePlatformServiceInvalidIdException $e) {
            throw new HttpBadRequestException(
                "Bad request: {$e->getMessage()}",
                $e
            );
        } catch (GamePlatformServiceUnexistantGamePlatformException $e) {
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

            $gamePlatform = $this->gamePlatformService->findById($id);

            if ($gamePlatform === null) {
                throw new HttpNotFoundException(
                    "Game platform with the id $id not found!"
                );
            }

            $response
                ->setBody([
                    'data' => [
                        'id' => $gamePlatform->getId(),
                        'gameId' => $gamePlatform->getGameId(),
                        'platformId' => $gamePlatform->getPlatformId()
                    ]
                ])
                ->setStatusOk()
                ->sendJson();
        } catch (HttpNotFoundException $e) {
            throw $e;
        } catch (GamePlatformServiceInvalidIdException $e) {
            throw new HttpBadRequestException(
                "Bad request: {$e->getMessage()}",
                $e
            );
        } catch (GamePlatformServiceUnexistantGamePlatformException $e) {
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

            $gamePlatforms = $this->gamePlatformService->findAll();

            $numberOfGamePlatforms = count($gamePlatforms);
            if ($numberOfGamePlatforms === 0) {
                throw new HttpNotFoundException(
                    'No game platforms found!'
                );
            }

            $data = [];
            foreach ($gamePlatforms as $gamePlatform) {
                $data[] = [
                    'id' => $gamePlatform->getId(),
                    'gameId' => $gamePlatform->getGameId(),
                    'platformId' => $gamePlatform->getPlatformId()
                ];
            }

            $response
                ->setBody([
                    'number' => $numberOfGamePlatforms,
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
