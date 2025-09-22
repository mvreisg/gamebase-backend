<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Controllers;

use Mvreisg\GamebaseBackend\Application\Exceptions\Authentication\AuthenticationException;
use Mvreisg\GamebaseBackend\Application\Services\AuthenticationService;
use Mvreisg\GamebaseBackend\Application\Services\GamePlatformService;
use Mvreisg\GamebaseBackend\Presentation\Http\Entities\HttpRequest;
use Mvreisg\GamebaseBackend\Presentation\Http\Entities\HttpResponse;
use Mvreisg\GamebaseBackend\Presentation\Http\Router\HttpRouter;
use Mvreisg\GamebaseBackend\Presentation\Exceptions\Http\HttpInvalidParameterException;
use Mvreisg\GamebaseBackend\Presentation\Exceptions\Http\HttpUnauthorizedException;
use Mvreisg\GamebaseBackend\Presentation\Exceptions\Http\HttpUndefinedValueException;
use Mvreisg\GamebaseBackend\Presentation\Http\Enums\HttpContentTypesEnum;
use Mvreisg\GamebaseBackend\Presentation\Http\Enums\HttpStatusCodeTypesEnum;
use Mvreisg\GamebaseBackend\Presentation\Http\Middlewares\HttpJWTBearerTokenRetriever;

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
            $token = HttpJWTBearerTokenRetriever::retrieveFromHeaders($request->getHeaders());
            $isTokenValid = $this->authenticationService->validateToken($token);
            if ($isTokenValid === false) {
                throw new HttpUnauthorizedException(
                    'Invalid token!'
                );
            }

            $body = $request->parseBodyFromJSONString();

            $isGameIdSetted = isset($body['gameId']);
            if ($isGameIdSetted === false) {
                throw new HttpUndefinedValueException(
                    'gameId value not informed!'
                );
            }

            $isPlatformIdSetted = isset($body['platformId']);
            if ($isPlatformIdSetted === false) {
                throw new HttpUndefinedValueException(
                    'platformId value not informed!'
                );
            }

            $gameId = $body['gameId'];
            $platformId = $body['platformId'];

            $gamePlatform = $this->gamePlatformService->insert($platformId, $gameId);

            $data = [
                'id' => $gamePlatform->getId(),
                'platformId' => $gamePlatform->getPlatformId(),
                'gameId' => $gamePlatform->getGameId()
            ];

            $response
                ->setBody([
                    'message' => 'Register created!',
                    'data' => $data
                ])
                ->setStatusCreated()
                ->sendJson();
            return;
        } catch (
            AuthenticationException |
            HttpUnauthorizedException $e
        ) {
            $response
                ->setBody([
                    'message' => $e->getMessage()
                ])
                ->setStatusUnauthorized()
                ->sendJson();
            return;
        } catch (
            HttpUndefinedValueException |
            HttpInvalidParameterException $e
        ) {
            $response
                ->setBody([
                    'message' => $e->getMessage()
                ])
                ->setStatusBadRequest()
                ->sendJson();
            return;
        } catch (\Throwable $e) {
            $response
                ->setBody([
                    'message' => $e->getMessage()
                ])
                ->setStatusInternalServerError()
                ->sendJson();
            return;
        }
    }

    public function update(HttpRequest $request, HttpResponse $response): void
    {
        try {
            $token = HttpJWTBearerTokenRetriever::retrieveFromHeaders($request->getHeaders());
            $isTokenValid = $this->authenticationService->validateToken($token);
            if ($isTokenValid === false) {
                throw new HttpUnauthorizedException(
                    'Invalid token!'
                );
            }

            $params = $request->getParams();
            $body = $request->parseBodyFromJSONString();

            $isIdSetted = isset($params['id']);
            if ($isIdSetted === false) {
                throw new HttpUndefinedValueException(
                    'id parameter not informed!'
                );
            }

            $isGameIdSetted = isset($body['gameId']);
            if ($isGameIdSetted === false) {
                throw new HttpUndefinedValueException(
                    'gameId value not informed!'
                );
            }

            $isPlatformIdSetted = isset($body['platformId']);
            if ($isPlatformIdSetted === false) {
                throw new HttpUndefinedValueException(
                    'platformId value not informed!'
                );
            }

            $id = $params['id'];
            $gameId = $body['gameId'];
            $platformId = $body['platformId'];

            $wasUpdated = $this->gamePlatformService->update($id, $platformId, $gameId);

            $response
                ->setBody([
                    'message' => $wasUpdated ? 'State updated!' : 'No state changes!'
                ])
                ->setStatusOk()
                ->sendJson();
            return;
        } catch (
            AuthenticationException |
            HttpUnauthorizedException $e
        ) {
            $response
                ->setBody([
                    'message' => $e->getMessage()
                ])
                ->setStatusUnauthorized()
                ->sendJson();
            return;
        } catch (
            HttpUndefinedValueException |
            HttpInvalidParameterException $e
        ) {
            $response
                ->setBody([
                    'message' => $e->getMessage()
                ])
                ->setStatusBadRequest()
                ->sendJson();
            return;
        } catch (\Throwable $e) {
            $response
                ->setBody([
                    'message' => $e->getMessage()
                ])
                ->setStatusInternalServerError()
                ->sendJson();
            return;
        }
    }

    public function delete(HttpRequest $request, HttpResponse $response): void
    {
        try {
            $token = HttpJWTBearerTokenRetriever::retrieveFromHeaders($request->getHeaders());
            $isTokenValid = $this->authenticationService->validateToken($token);
            if ($isTokenValid === false) {
                throw new HttpUnauthorizedException(
                    'Invalid token!'
                );
            }

            $params = $request->getParams();

            $isIdSetted = isset($params['id']);
            if ($isIdSetted === false) {
                throw new HttpUndefinedValueException(
                    'id parameter not informed!'
                );
            }

            $id = $params['id'];

            $wasDeleted = $this->gamePlatformService->delete($id);

            $response
                ->setBody([
                    'message' => $wasDeleted ? 'Register deleted!' : 'No deletions!'
                ])
                ->setStatusOk()
                ->sendJson();
            return;
        } catch (
            AuthenticationException |
            HttpUnauthorizedException $e
        ) {
            $response
                ->setBody([
                    'message' => $e->getMessage()
                ])
                ->setStatusUnauthorized()
                ->sendJson();
            return;
        } catch (
            HttpUndefinedValueException |
            HttpInvalidParameterException $e
        ) {
            $response
                ->setBody([
                    'message' => $e->getMessage()
                ])
                ->setStatusBadRequest()
                ->sendJson();
            return;
        } catch (\Throwable $e) {
            $response
                ->setBody([
                    'message' => $e->getMessage()
                ])
                ->setStatusInternalServerError()
                ->sendJson();
            return;
        }
    }
    public function findById(HttpRequest $request, HttpResponse $response): void
    {
        try {
            $token = HttpJWTBearerTokenRetriever::retrieveFromHeaders($request->getHeaders());
            $isTokenValid = $this->authenticationService->validateToken($token);
            if ($isTokenValid === false) {
                throw new HttpUnauthorizedException(
                    'Invalid token!'
                );
            }

            $params = $request->getParams();

            $isIdSetted = isset($params['id']);
            if ($isIdSetted === false) {
                throw new HttpUndefinedValueException(
                    'id parameter not informed!'
                );
            }

            $id = $params['id'];

            $gamePlatform = $this->gamePlatformService->findById($id);
            if ($gamePlatform === null) {
                $response
                    ->setBody([
                        'message' => 'Register not found!',
                    ])
                    ->setStatus(
                        HttpStatusCodeTypesEnum::NotFound
                    )
                    ->send(
                        HttpContentTypesEnum::Json
                    );
                return;
            }

            $data = [
                'id' => $gamePlatform->getId(),
                'platformId' => $gamePlatform->getPlatformId(),
                'gameId' => $gamePlatform->getGameId()
            ];

            $response
                ->setBody([
                    'message' => 'Register found!',
                    'data' => $data
                ])
                ->setStatusOk()
                ->sendJson();
            return;
        } catch (
            AuthenticationException |
            HttpUnauthorizedException $e
        ) {
            $response
                ->setBody([
                    'message' => $e->getMessage()
                ])
                ->setStatusUnauthorized()
                ->sendJson();
            return;
        } catch (
            HttpUndefinedValueException |
            HttpInvalidParameterException $e
        ) {
            $response
                ->setBody([
                    'message' => $e->getMessage()
                ])
                ->setStatusBadRequest()
                ->sendJson();
            return;
        } catch (\Throwable $e) {
            $response
                ->setBody([
                    'message' => $e->getMessage()
                ])
                ->setStatusInternalServerError()
                ->sendJson();
            return;
        }
    }

    public function findAll(HttpRequest $request, HttpResponse $response): void
    {
        try {
            $token = HttpJWTBearerTokenRetriever::retrieveFromHeaders($request->getHeaders());
            $isTokenValid = $this->authenticationService->validateToken($token);
            if ($isTokenValid === false) {
                throw new HttpUnauthorizedException(
                    'Invalid token!'
                );
            }

            $gamePlatforms = $this->gamePlatformService->findAll();

            $numberOfGamePlatforms = count($gamePlatforms);
            if ($numberOfGamePlatforms === 0) {
                $response
                    ->setBody([
                        'message' => 'No values found!',
                    ])
                    ->setStatus(
                        HttpStatusCodeTypesEnum::NotFound
                    )
                    ->send(
                        HttpContentTypesEnum::Json
                    );
                return;
            }

            foreach ($gamePlatforms as $gamePlatform) {
                $data[] = [
                    'id' => $gamePlatform->getId(),
                    'platformId' => $gamePlatform->getPlatformId(),
                    'gameId' => $gamePlatform->getGameId()
                ];
            }

            $response
                ->setBody([
                    'message' => 'Game Platform bonds successfully found!',
                    'data' => $data
                ])
                ->setStatusOk()
                ->sendJson();
            return;
        } catch (
            AuthenticationException |
            HttpUnauthorizedException $e
        ) {
            $response
                ->setBody([
                    'message' => $e->getMessage()
                ])
                ->setStatusUnauthorized()
                ->sendJson();
            return;
        } catch (
            HttpUndefinedValueException |
            HttpInvalidParameterException $e
        ) {
            $response
                ->setBody([
                    'message' => $e->getMessage()
                ])
                ->setStatusBadRequest()
                ->sendJson();
            return;
        } catch (\Throwable $e) {
            $response
                ->setBody([
                    'message' => $e->getMessage()
                ])
                ->setStatusInternalServerError()
                ->sendJson();
            return;
        }
    }
}
