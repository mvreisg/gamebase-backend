<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Controllers;

use Mvreisg\GamebaseBackend\Application\Exceptions\Authentication\AuthenticationException;
use Mvreisg\GamebaseBackend\Application\Services\AuthenticationService;
use Mvreisg\GamebaseBackend\Application\Services\SectorPermissionService;
use Mvreisg\GamebaseBackend\Presentation\Http\Entities\HttpRequest;
use Mvreisg\GamebaseBackend\Presentation\Http\Entities\HttpResponse;
use Mvreisg\GamebaseBackend\Presentation\Exceptions\Http\HttpInvalidParameterException;
use Mvreisg\GamebaseBackend\Presentation\Exceptions\Http\HttpUnauthorizedException;
use Mvreisg\GamebaseBackend\Presentation\Exceptions\Http\HttpUndefinedValueException;
use Mvreisg\GamebaseBackend\Presentation\Http\Enums\HttpContentTypesEnum;
use Mvreisg\GamebaseBackend\Presentation\Http\Enums\HttpStatusCodeTypesEnum;
use Mvreisg\GamebaseBackend\Presentation\Http\Middlewares\HttpJWTBearerTokenRetriever;

class HttpSectorPermissionController
{
    private SectorPermissionService $sectorPermissionService;
    private AuthenticationService $authenticationService;

    public function __construct(
        SectorPermissionService $sectorPermissionService,
        AuthenticationService $authenticationService
    ) {
        $this->sectorPermissionService = $sectorPermissionService;
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

            $isSectorIdSetted = isset($body['sectorId']);
            if ($isSectorIdSetted === false) {
                throw new HttpUndefinedValueException(
                    'sectorId value not informed!'
                );
            }

            $isPermissionIdSetted = isset($body['permissionId']);
            if ($isPermissionIdSetted === false) {
                throw new HttpUndefinedValueException(
                    'permissionId value not informed!'
                );
            }

            $sectorId = $body['sectorId'];
            $permissionId = $body['permissionId'];

            $sectorPermission = $this->sectorPermissionService->insert($sectorId, $permissionId);

            $data = [
                'id' => $sectorPermission->getId(),
                'sectorId' => $sectorPermission->getSectorId(),
                'permissionId' => $sectorPermission->getPermissionId()
            ];

            $response
                ->setBody([
                    'message' => 'Successfully inserted!',
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

            $body = $request->parseBodyFromJSONString();
            $params = $request->getParams();

            $isIdSetted = isset($params['id']);
            if ($isIdSetted === false) {
                throw new HttpUndefinedValueException(
                    'id parameter not informed!'
                );
            }

            $isSectorIdSetted = isset($body['sectorId']);
            if ($isSectorIdSetted === false) {
                throw new HttpUndefinedValueException(
                    'sectorId value not informed!'
                );
            }

            $isPermissionIdSetted = isset($body['permissionId']);
            if ($isPermissionIdSetted === false) {
                throw new HttpUndefinedValueException(
                    'permissionId value not informed!'
                );
            }

            $id = $params['id'];
            $sectorId = $body['sectorId'];
            $permissionId = $body['permissionId'];

            $wasUpdated = $this->sectorPermissionService->update($id, $sectorId, $permissionId);

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

            $wasDeleted = $this->sectorPermissionService->delete($id);

            $response
                ->setBody([
                    'message' => $wasDeleted ? 'Register deleted' : 'No changes!'
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
                    'id paramter not informed!'
                );
            }

            $id = $params['id'];

            $sectorPermission = $this->sectorPermissionService->findById($id);

            if ($sectorPermission === null) {
                $response
                    ->setBody([
                        'message' => 'Not found!'
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
                'id' => $sectorPermission->getId(),
                'sectorId' => $sectorPermission->getSectorId(),
                'permissionId' => $sectorPermission->getPermissionId()
            ];

            $response
                ->setBody([
                    'message' => 'Value found!',
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

            $sectorPermissions = $this->sectorPermissionService->findAll();

            $numberOfGameGenres = count($sectorPermissions);
            if ($numberOfGameGenres === 0) {
                $response
                    ->setBody([
                        'message' => 'Nothing found!',
                    ])
                    ->setStatus(
                        HttpStatusCodeTypesEnum::NotFound
                    )
                    ->send(
                        HttpContentTypesEnum::Json
                    );
                return;
            }

            foreach ($sectorPermissions as $sectorPermission) {
                $data[] = [
                    'id' => $sectorPermission->getId(),
                    'sectorId' => $sectorPermission->getSectorId(),
                    'permissionId' => $sectorPermission->getPermissionId()
                ];
            }

            $response
                ->setBody([
                    'message' => 'Registers found!',
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
