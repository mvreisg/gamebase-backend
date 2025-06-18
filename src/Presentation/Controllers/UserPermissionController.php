<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Controllers;

use Mvreisg\GamebaseBackend\Application\Exceptions\AuthenticationException;
use Mvreisg\GamebaseBackend\Application\Services\AuthenticationService;
use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpRequest;
use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpResponse;
use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpRouter;
use Mvreisg\GamebaseBackend\Application\Services\UserPermissionService;
use Mvreisg\GamebaseBackend\Domain\Exceptions\EntityInvalidValueException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseFetchFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseStatementCreationFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseStatementExecutionFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseTransactionCreationFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\HttpJsonParseException;
use Mvreisg\GamebaseBackend\Presentation\Exceptions\ControllerUndefinedValueException;
use Mvreisg\GamebaseBackend\Presentation\Middlewares\RouteAuthenticator;
use PDOException;
use Throwable;

class UserPermissionController
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
            RouteAuthenticator::make($this->authenticationService)->validate($request, $response);

            $body = $request->parseBodyFromJSONString();

            $isUserIdSetted = isset($body['userId']);
            if ($isUserIdSetted === false) {
                throw new ControllerUndefinedValueException(
                    'A chave userId não existe no JSON ou seu valor é null!'
                );
            }

            $isPermissionIdSetted = isset($body['permissionId']);
            if ($isPermissionIdSetted === false) {
                throw new ControllerUndefinedValueException(
                    'A chave permissionId não existe no JSON ou seu valor é null!'
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
                    'message' => 'Vínculo inserido com sucesso!',
                    'data' => $data
                ])
                ->setStatus(HttpRouter::$STATUS_CODES[201])
                ->send(HttpRouter::$CONTENT_TYPES['JSON']);
            return;
        } catch (AuthenticationException $e) {
            $response
                ->setBody([
                    'message' => $e->getMessage()
                ])
                ->setStatus(HttpRouter::$STATUS_CODES[401])
                ->send(HttpRouter::$CONTENT_TYPES['JSON']);
            return;
        } catch (
            ControllerUndefinedValueException |
            HttpJsonParseException |
            EntityInvalidValueException $e
        ) {
            $response
                ->setBody([
                    'message' => $e->getMessage()
                ])
                ->setStatus(HttpRouter::$STATUS_CODES[400])
                ->send(HttpRouter::$CONTENT_TYPES['JSON']);
            return;
        } catch (
            DatabaseTransactionCreationFailureException |
            DatabaseStatementCreationFailureException |
            DatabaseStatementExecutionFailureException |
            DatabaseFetchFailureException |
            PDOException |
            Throwable $e
        ) {
            $response
                ->setBody([
                    'message' => $e->getMessage()
                ])
                ->setStatus(HttpRouter::$STATUS_CODES[500])
                ->send(HttpRouter::$CONTENT_TYPES['JSON']);
            return;
        }
    }

    public function update(HttpRequest $request, HttpResponse $response): void
    {
        try {
            RouteAuthenticator::make($this->authenticationService)->validate($request, $response);

            $body = $request->parseBodyFromJSONString();
            $params = $request->getParams();

            $isIdSetted = isset($params['id']);
            if ($isIdSetted === false) {
                throw new ControllerUndefinedValueException(
                    'O parâmetro id não foi informado na URL ou seu valor é null!'
                );
            }

            $isUserIdSetted = isset($body['userId']);
            if ($isUserIdSetted === false) {
                throw new ControllerUndefinedValueException(
                    'A chave userId não foi informada no JSON ou seu valor é null!'
                );
            }

            $isPermissionIdSetted = isset($body['permissionId']);
            if ($isPermissionIdSetted === false) {
                throw new ControllerUndefinedValueException(
                    'A chave permissionId não foi informada no JSON ou seu valor é null!'
                );
            }

            $id = $params['id'];
            $userId = $body['userId'];
            $permissionId = $body['permissionId'];

            $wasTheUpdateSuccessful = $this->userPermissionService->update($id, $userId, $permissionId);
            if ($wasTheUpdateSuccessful === false) {
                $response
                    ->setBody([
                        'message' => 'Não houve nenhuma atualização!'
                    ])
                    ->setStatus(HttpRouter::$STATUS_CODES[200])
                    ->send(HttpRouter::$CONTENT_TYPES['JSON']);
                return;
            }

            $response
                ->setBody([
                    'message' => 'Vínculo atualizado com sucesso!'
                ])
                ->setStatus(HttpRouter::$STATUS_CODES[200])
                ->send(HttpRouter::$CONTENT_TYPES['JSON']);
            return;
        } catch (AuthenticationException $e) {
            $response
                ->setBody([
                    'message' => $e->getMessage()
                ])
                ->setStatus(HttpRouter::$STATUS_CODES[401])
                ->send(HttpRouter::$CONTENT_TYPES['JSON']);
            return;
        } catch (
            ControllerUndefinedValueException |
            HttpJsonParseException |
            EntityInvalidValueException $e
        ) {
            $response
                ->setBody([
                    'message' => $e->getMessage()
                ])
                ->setStatus(HttpRouter::$STATUS_CODES[400])
                ->send(HttpRouter::$CONTENT_TYPES['JSON']);
            return;
        } catch (
            DatabaseStatementCreationFailureException |
            DatabaseStatementExecutionFailureException |
            PDOException $e
        ) {
            $response
                ->setBody([
                    'message' => $e->getMessage()
                ])
                ->setStatus(HttpRouter::$STATUS_CODES[500])
                ->send(HttpRouter::$CONTENT_TYPES['JSON']);
            return;
        }
    }

    public function delete(HttpRequest $request, HttpResponse $response): void
    {
        try {
            RouteAuthenticator::make($this->authenticationService)->validate($request, $response);

            $params = $request->getParams();

            $isIdSetted = isset($params['id']);
            if ($isIdSetted === false) {
                throw new ControllerUndefinedValueException(
                    'O parâmetro id não foi informado na URL ou seu valor é null!'
                );
            }

            $id = $params['id'];

            $wasTheDeleteSuccessful = $this->userPermissionService->delete($id);
            if ($wasTheDeleteSuccessful === false) {
                $response
                    ->setBody([
                        'message' => 'Vínculo com o id ' . $id . ' não encontrado!'
                    ])
                    ->setStatus(HttpRouter::$STATUS_CODES[200])
                    ->send(HttpRouter::$CONTENT_TYPES['JSON']);
                return;
            }

            $response
                ->setBody([
                    'message' => 'Vínculo deletado com sucesso!'
                ])
                ->setStatus(HttpRouter::$STATUS_CODES[200])
                ->send(HttpRouter::$CONTENT_TYPES['JSON']);
            return;
        } catch (AuthenticationException $e) {
            $response
                ->setBody([
                    'message' => $e->getMessage()
                ])
                ->setStatus(HttpRouter::$STATUS_CODES[401])
                ->send(HttpRouter::$CONTENT_TYPES['JSON']);
            return;
        } catch (
            HttpJsonParseException |
            ControllerUndefinedValueException |
            EntityInvalidValueException $e
        ) {
            $response
                ->setBody([
                    'message' => $e->getMessage()
                ])
                ->setStatus(HttpRouter::$STATUS_CODES[400])
                ->send(HttpRouter::$CONTENT_TYPES['JSON']);
            return;
        } catch (
            DatabaseStatementExecutionFailureException |
            DatabaseStatementCreationFailureException |
            PDOException $e
        ) {
            $response
                ->setBody([
                    'message' => $e->getMessage()
                ])
                ->setStatus(HttpRouter::$STATUS_CODES[500])
                ->send(HttpRouter::$CONTENT_TYPES['JSON']);
            return;
        }
    }

    public function findById(HttpRequest $request, HttpResponse $response): void
    {
        try {
            RouteAuthenticator::make($this->authenticationService)->validate($request, $response);

            $params = $request->getParams();

            $isIdSetted = isset($params['id']);
            if ($isIdSetted === false) {
                throw new ControllerUndefinedValueException(
                    'O parâmetro id não foi informado na URL ou seu valor é null!'
                );
            }

            $id = $params['id'];

            $userPermission = $this->userPermissionService->findById($id);

            if ($userPermission === null) {
                $response
                    ->setBody([
                        'message' => 'O vínculo procurado não existe!'
                    ])
                    ->setStatus(HttpRouter::$STATUS_CODES[200])
                    ->send(HttpRouter::$CONTENT_TYPES['JSON']);
                return;
            }

            $response
                ->setBody([
                    'message' => 'Vínculo entre gênero e jogo encontrado com sucesso!',
                    'data' => [
                        'id' => $userPermission->getId(),
                        'userId' => $userPermission->getUserId(),
                        'permissionId' => $userPermission->getPermissionId()
                    ]
                ])
                ->setStatus(HttpRouter::$STATUS_CODES[200])
                ->send(HttpRouter::$CONTENT_TYPES['JSON']);
            return;
        } catch (AuthenticationException $e) {
            $response
                ->setBody([
                    'message' => $e->getMessage()
                ])
                ->setStatus(HttpRouter::$STATUS_CODES[401])
                ->send(HttpRouter::$CONTENT_TYPES['JSON']);
            return;
        } catch (
            ControllerUndefinedValueException |
            HttpJsonParseException |
            EntityInvalidValueException $e
        ) {
            $response
                ->setBody([
                    'message' => $e->getMessage()
                ])
                ->setStatus(HttpRouter::$STATUS_CODES[400])
                ->send(HttpRouter::$CONTENT_TYPES['JSON']);
            return;
        } catch (
            DatabaseStatementCreationFailureException |
            DatabaseStatementExecutionFailureException |
            PDOException $e
        ) {
            $response
                ->setBody([
                    'message' => $e->getMessage()
                ])
                ->setStatus(HttpRouter::$STATUS_CODES[500])
                ->send(HttpRouter::$CONTENT_TYPES['JSON']);
            return;
        }
    }

    public function findAll(HttpRequest $request, HttpResponse $response): void
    {
        try {
            RouteAuthenticator::make($this->authenticationService)->validate($request, $response);

            $userPermissions = $this->userPermissionService->findAll();

            $numberOfGameGenres = count($userPermissions);
            if ($numberOfGameGenres === 0) {
                $response
                    ->setBody([
                        'message' => 'Nenhum registro encontrado!',
                    ])
                    ->setStatus(HttpRouter::$STATUS_CODES[200])
                    ->send(HttpRouter::$CONTENT_TYPES['JSON']);
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
                    'message' => 'Vínculos encontrados com sucesso!',
                    'data' => $data
                ])
                ->setStatus(HttpRouter::$STATUS_CODES[200])
                ->send(HttpRouter::$CONTENT_TYPES['JSON']);
            return;
        } catch (AuthenticationException $e) {
            $response
                ->setBody([
                    'message' => $e->getMessage()
                ])
                ->setStatus(HttpRouter::$STATUS_CODES[401])
                ->send(HttpRouter::$CONTENT_TYPES['JSON']);
            return;
        } catch (
            DatabaseStatementCreationFailureException |
            DatabaseStatementExecutionFailureException |
            PDOException $e
        ) {
            $response
                ->setBody([
                    'message' => $e->getMessage()
                ])
                ->setStatus(HttpRouter::$STATUS_CODES[500])
                ->send(HttpRouter::$CONTENT_TYPES['JSON']);
            return;
        }
    }
}
