<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Controllers;

use Mvreisg\GamebaseBackend\Application\Exceptions\Authentication\AuthenticationException;
use Mvreisg\GamebaseBackend\Application\Services\AuthenticationService;
use Mvreisg\GamebaseBackend\Application\Services\GenreService;
use Mvreisg\GamebaseBackend\Presentation\Http\Entities\HttpRequest;
use Mvreisg\GamebaseBackend\Presentation\Http\Entities\HttpResponse;
use Mvreisg\GamebaseBackend\Presentation\Exceptions\Http\HttpInvalidParameterException;
use Mvreisg\GamebaseBackend\Presentation\Exceptions\Http\HttpUnauthorizedException;
use Mvreisg\GamebaseBackend\Presentation\Exceptions\Http\HttpUndefinedValueException;
use Mvreisg\GamebaseBackend\Presentation\Http\Enums\HttpContentTypesEnum;
use Mvreisg\GamebaseBackend\Presentation\Http\Enums\HttpStatusCodeTypesEnum;
use Mvreisg\GamebaseBackend\Presentation\Http\Middlewares\HttpJWTBearerTokenRetriever;

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

            $genre = $this->genreService->insert($name, $isActive);

            $data = [
                'id' => $genre->getId(),
                'name' => $genre->getName(),
                'isActive' => $genre->getIsActive()
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

            $wasUpdated = $this->genreService->update($id, $name, $isActive);

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
                    'id parameter not informed!'
                );
            }

            $isIsActiveFieldSetted = isset($body['isActive']);
            if ($isIsActiveFieldSetted === false) {
                throw new HttpUndefinedValueException(
                    'isActive value not informed!'
                );
            }

            $id = $params['id'];
            $isActive = $body['isActive'];

            $wasUpdated = $this->genreService->setIsActive($id, $isActive);

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

            $genre = $this->genreService->findById($id);

            if ($genre === null) {
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
                'id' => $genre->getId(),
                'name' => $genre->getName(),
                'isActive' => $genre->getIsActive()
            ];

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

            $genres = $this->genreService->findAll();

            $numberOfGenresFound = count($genres);
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

            foreach ($genres as $genre) {
                $data[] = [
                    'id' => $genre->getId(),
                    'name' => $genre->getName(),
                    'isActive' => $genre->getIsActive()
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
