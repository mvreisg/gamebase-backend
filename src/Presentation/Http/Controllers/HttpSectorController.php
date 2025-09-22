<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Controllers;

use Mvreisg\GamebaseBackend\Application\Exceptions\Authentication\AuthenticationException;
use Mvreisg\GamebaseBackend\Application\Services\AuthenticationService;
use Mvreisg\GamebaseBackend\Application\Services\SectorService;
use Mvreisg\GamebaseBackend\Presentation\Http\Entities\HttpRequest;
use Mvreisg\GamebaseBackend\Presentation\Http\Entities\HttpResponse;
use Mvreisg\GamebaseBackend\Presentation\Exceptions\Http\HttpInvalidParameterException;
use Mvreisg\GamebaseBackend\Presentation\Exceptions\Http\HttpUnauthorizedException;
use Mvreisg\GamebaseBackend\Presentation\Exceptions\Http\HttpUndefinedValueException;
use Mvreisg\GamebaseBackend\Presentation\Http\Enums\HttpContentTypesEnum;
use Mvreisg\GamebaseBackend\Presentation\Http\Enums\HttpStatusCodeTypesEnum;
use Mvreisg\GamebaseBackend\Presentation\Http\Middlewares\HttpJWTBearerTokenRetriever;

class HttpSectorController
{
    private SectorService $sectorService;
    private AuthenticationService $authenticationService;

    public function __construct(
        SectorService $sectorService,
        AuthenticationService $authenticationService
    ) {
        $this->sectorService = $sectorService;
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

            $isNameFieldSetted = isset($body['name']);
            if ($isNameFieldSetted === false) {
                throw new HttpUndefinedValueException(
                    'name value not informed!'
                );
            }

            $isIsActiveFieldSetted = isset($body['isActive']);
            if ($isIsActiveFieldSetted === false) {
                throw new HttpUndefinedValueException(
                    'isActive value not informed!'
                );
            }

            $name = $body['name'];
            $isActive = $body['isActive'];

            $sector = $this->sectorService->insert($name, $isActive);

            $data = [
                'id' => $sector->getId(),
                'name' => $sector->getName(),
                'isActive' => $sector->getIsActive()
            ];

            $response
                ->setBody([
                    'message' => 'Sucessfully inserted!',
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

            $isNameFieldSetted = isset($body['name']);
            if ($isNameFieldSetted === false) {
                throw new HttpUndefinedValueException(
                    'name value not informed!'
                );
            }

            $isIsActiveFieldSetted = isset($body['isActive']);
            if ($isIsActiveFieldSetted === false) {
                throw new HttpUndefinedValueException(
                    'isActive value not informed!'
                );
            }

            $id = $params['id'];
            $name = $body['name'];
            $isActive = $body['isActive'];

            $wasUpdated = $this->sectorService->update($id, $name, $isActive);

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

    public function setIsActive(HttpRequest $request, HttpResponse $response): void
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
                    'id parameter not informed'
                );
            }

            $isIsActiveFieldSetted = isset($body['isActive']);
            if ($isIsActiveFieldSetted === false) {
                throw new HttpUndefinedValueException(
                    'isActive value not informed'
                );
            }

            $id = $params['id'];
            $isActive = $body['isActive'];

            $wasUpdated = $this->sectorService->setIsActive($id, $isActive);

            $response
                ->setBody([
                    'message' => $wasUpdated ? 'Active state updated!' : 'No state changes!'
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

            $sector = $this->sectorService->findById($id);

            if ($sector === null) {
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

            $data = [
                'id' => $sector->getId(),
                'name' => $sector->getName(),
                'isActive' => $sector->getIsActive()
            ];

            $response
                ->setBody([
                    'message' => 'Sector sucessfully found!',
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

            $sectors = $this->sectorService->findAll();

            $numberOfGenresFound = count($sectors);
            if ($numberOfGenresFound === 0) {
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

            foreach ($sectors as $sector) {
                $data[] = [
                    'id' => $sector->getId(),
                    'name' => $sector->getName(),
                    'isActive' => $sector->getIsActive()
                ];
            }

            $response
                ->setBody([
                    'message' => 'Results found!',
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
