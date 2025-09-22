<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Controllers;

use Mvreisg\GamebaseBackend\Application\Exceptions\Authentication\AuthenticationException;
use Mvreisg\GamebaseBackend\Application\Services\AuthenticationService;
use Mvreisg\GamebaseBackend\Application\Services\UserPermissionService;
use Mvreisg\GamebaseBackend\Presentation\Http\Entities\HttpRequest;
use Mvreisg\GamebaseBackend\Presentation\Http\Entities\HttpResponse;
use Mvreisg\GamebaseBackend\Presentation\Exceptions\Http\HttpInvalidParameterException;
use Mvreisg\GamebaseBackend\Presentation\Exceptions\Http\HttpUnauthorizedException;
use Mvreisg\GamebaseBackend\Presentation\Exceptions\Http\HttpUndefinedValueException;
use Mvreisg\GamebaseBackend\Presentation\Http\Enums\HttpContentTypesEnum;
use Mvreisg\GamebaseBackend\Presentation\Http\Enums\HttpStatusCodeTypesEnum;
use Mvreisg\GamebaseBackend\Presentation\Http\Middlewares\HttpJWTBearerTokenRetriever;

class HttpUserPermissionController
{
    private UserPermissionService $userPermissionService;
    private AuthenticationService $authenticationService;

    public function __construct(
        UserPermissionService $userPermissionService,
        AuthenticationService $authenticationService
    ) {
        $this->userPermissionService = $userPermissionService;
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

            $isUserIdSetted = isset($body['userId']);
            if ($isUserIdSetted === false) {
                throw new HttpUndefinedValueException(
                    'userId value not informed!'
                );
            }

            $isPermissionIdSetted = isset($body['permissionId']);
            if ($isPermissionIdSetted === false) {
                throw new HttpUndefinedValueException(
                    'permissionId value not informed!'
                );
            }

            $userId = $body['userId'];
            $permissionId = $body['permissionId'];

            $userPermission = $this->userPermissionService->insert($userId, $permissionId);

            $data = [
                'id' => $userPermission->getId(),
                'userId' => $userPermission->getUserId(),
                'permissionId' => $userPermission->getPermissionId()
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

            $isUserIdSetted = isset($body['userId']);
            if ($isUserIdSetted === false) {
                throw new HttpUndefinedValueException(
                    'userId value not informed!'
                );
            }

            $isPermissionIdSetted = isset($body['permissionId']);
            if ($isPermissionIdSetted === false) {
                throw new HttpUndefinedValueException(
                    'permissionId value not informed!'
                );
            }

            $id = $params['id'];
            $userId = $body['userId'];
            $permissionId = $body['permissionId'];

            $wasUpdated = $this->userPermissionService->update($id, $userId, $permissionId);

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

            $wasDeleted = $this->userPermissionService->delete($id);

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

            $userPermission = $this->userPermissionService->findById($id);

            if ($userPermission === null) {
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
                'id' => $userPermission->getId(),
                'userId' => $userPermission->getUserId(),
                'permissionId' => $userPermission->getPermissionId()
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

            $userPermissions = $this->userPermissionService->findAll();

            $numberOfGameGenres = count($userPermissions);
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

            foreach ($userPermissions as $userPermission) {
                $data[] = [
                    'id' => $userPermission->getId(),
                    'userId' => $userPermission->getUserId(),
                    'permissionId' => $userPermission->getPermissionId()
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
