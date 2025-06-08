<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Controllers;

use Mvreisg\GamebaseBackend\Application\Exceptions\AuthenticationException;
use Mvreisg\GamebaseBackend\Application\Services\AuthenticationService;
use PDOException;
use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpRequest;
use Mvreisg\GamebaseBackend\Application\Services\UserService;
use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpResponse;
use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpRouter;
use Mvreisg\GamebaseBackend\Domain\Exceptions\EntityInvalidValueException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseDuplicatedEntryException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseFetchFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseStatementCreationFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseStatementExecutionFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\HttpJsonParseException;
use Mvreisg\GamebaseBackend\Infrastructure\Http\AuthorizationTokenRetriever;
use Mvreisg\GamebaseBackend\Presentation\Exceptions\ControllerOperationErrorException;
use Mvreisg\GamebaseBackend\Presentation\Exceptions\ControllerUndefinedValueException;

class UserController
{
    private UserService $service;
    private AuthenticationService $authService;

    public function __construct(UserService $service, AuthenticationService $authService)
    {
        $this->service = $service;
        $this->authService = $authService;
    }

    public function insert(HttpRequest $request, HttpResponse $response)
    {
        try {
            $headers = $request->getHeaders();
            $token = AuthorizationTokenRetriever::getFromHeaders($headers);
            $isAuthenticated = $this->authService->validateToken($token);
            if ($isAuthenticated === false) {
                throw new AuthenticationException('Usuário não autenticado!');
            }

            $body = $request->parseBodyFromJSONString();

            $isUserNameFieldSetted = isset($body['username']);
            if ($isUserNameFieldSetted === false) {
                throw new ControllerUndefinedValueException(
                    'A chave username não foi informada no JSON ou seu valor é null!'
                );
            }

            $isPassWordFieldSetted = isset($body['password']);
            if ($isPassWordFieldSetted === false) {
                throw new ControllerUndefinedValueException(
                    'A chave password não foi informada no JSON ou seu valor é null!'
                );
            }

            $isIsActiveFieldSetted = isset($body['isActive']);
            if ($isIsActiveFieldSetted === false) {
                throw new ControllerUndefinedValueException(
                    'A chave isActive não foi informada no JSON ou seu valor é null!'
                );
            }

            $userName = $body['username'];
            $passWord = $body['password'];
            $isActive = $body['isActive'];

            $user = $this->service->insert($userName, $passWord, $isActive);

            $response
                ->appendArray([
                    'message' => 'Registro de usuário inserido com sucesso!',
                    'data' => [
                        'id' => $user->getId(),
                        'username' => $user->getUserName(),
                        'password' => $user->getPassWord(),
                        'isActive' => $user->getIsActive()
                    ]
                ])
                ->status(HttpRouter::STATUS_CODES[201])
                ->send();

            return;
        } catch (AuthenticationException $e) {
            $response
                ->appendArray([
                    'message' => $e->getMessage()
                ])
                ->status(HttpRouter::STATUS_CODES[401])
                ->send();
            return;
        } catch (
            ControllerUndefinedValueException |
            HttpJsonParseException |
            EntityInvalidValueException |
            DatabaseDuplicatedEntryException $e
        ) {
            $response
                ->appendArray([
                    'message' => $e->getMessage()
                ])
                ->status(HttpRouter::STATUS_CODES[400])
                ->send();
            return;
        } catch (
            DatabaseStatementCreationFailureException |
            DatabaseStatementExecutionFailureException |
            DatabaseFetchFailureException |
            PDOException $e
        ) {
            $response
                ->appendArray([
                    'message' => $e->getMessage()
                ])
                ->status(HttpRouter::STATUS_CODES[500])
                ->send();
            return;
        }
    }

    public function update(HttpRequest $request, HttpResponse $response)
    {
        try {
            $headers = $request->getHeaders();
            $token = AuthorizationTokenRetriever::getFromHeaders($headers);
            $isAuthenticated = $this->authService->validateToken($token);
            if ($isAuthenticated === false) {
                throw new AuthenticationException('Usuário não autenticado!');
            }

            $body = $request->parseBodyFromJSONString();
            $params = $request->getParams();

            $isUserIdSetted = isset($params['userId']);
            if ($isUserIdSetted === false) {
                throw new ControllerUndefinedValueException('A chave userId não existe ou seu valor é null!');
            }

            $isUserNameSetted = isset($body['username']);
            if ($isUserNameSetted === false) {
                throw new ControllerUndefinedValueException('A chave username não existe ou seu valor é null!');
            }

            $isPassWordSetted = isset($body['password']);
            if ($isPassWordSetted === false) {
                throw new ControllerUndefinedValueException('A chave password não existe ou seu valor é null!');
            }

            $isIsActiveSetted = isset($body['isActive']);
            if ($isIsActiveSetted === false) {
                throw new ControllerUndefinedValueException('A chave isActive não existe ou seu valor é null!');
            }

            $userId = $params['userId'];
            $userName = $body['username'];
            $passWord = $body['password'];
            $isActive = $body['isActive'];

            $wasSomeUpdateHappened = $this->service->update($userId, $userName, $passWord, $isActive);
            if ($wasSomeUpdateHappened === false) {
                $response
                    ->appendArray([
                        'message' => 'Nenhuma linha afetada.'
                    ])
                    ->status(HttpRouter::STATUS_CODES[200])
                    ->send();
                return;
            }

            $response
                ->appendArray([
                    'message' => 'Dados do usuário atualizados com sucesso!'
                ])
                ->status(HttpRouter::STATUS_CODES[200])
                ->send();
            return;
        } catch (AuthenticationException $e) {
            $response
                ->appendArray([
                    'message' => $e->getMessage()
                ])
                ->status(HttpRouter::STATUS_CODES[401])
                ->send();
            return;
        } catch (
            ControllerUndefinedValueException |
            HttpJsonParseException |
            DatabaseDuplicatedEntryException |
            EntityInvalidValueException $e
        ) {
            $response
                ->appendArray([
                    'message' => $e->getMessage()
                ])
                ->status(HttpRouter::STATUS_CODES[400])
                ->send();
            return;
        } catch (
            ControllerOperationErrorException |
            DatabaseStatementCreationFailureException |
            DatabaseStatementExecutionFailureException |
            PDOException $e
        ) {
            $response
                ->appendArray([
                    'message' => $e->getMessage()
                ])
                ->status(HttpRouter::STATUS_CODES[500])
                ->send();
            return;
        }
    }

    public function setIsActive(HttpRequest $request, HttpResponse $response)
    {
        try {
            $headers = $request->getHeaders();
            $token = AuthorizationTokenRetriever::getFromHeaders($headers);
            $isAuthenticated = $this->authService->validateToken($token);
            if ($isAuthenticated === false) {
                throw new AuthenticationException('Usuário não autenticado!');
            }

            $params = $request->getParams();
            $body = $request->parseBodyFromJSONString();

            $isUserIdSetted = isset($params['userId']);
            if ($isUserIdSetted === false) {
                throw new ControllerUndefinedValueException(
                    'O parâmetro userId não foi informado ou seu valor é null!'
                );
            }

            $isIsActiveSetted = isset($body['isActive']);
            if ($isIsActiveSetted === false) {
                throw new ControllerUndefinedValueException(
                    'A chave isActive não foi definida no JSON ou seu valor é null!'
                );
            }

            $userId = $params['userId'];
            $isActive = $body['isActive'];

            $wasTheUpdateOcurred = $this->service->setIsActive($userId, $isActive);
            if ($wasTheUpdateOcurred === false) {
                throw new ControllerOperationErrorException(
                    'Ocorreu um erro! Verifique se o id ' .
                    $userId .
                    ' existe ' .
                    'ou se o valor de atividade foi modificado!'
                );
            }

            $response
                ->appendArray([
                    'message' => 'Estado atualizado com sucesso!'
                ])
                ->status(HttpRouter::STATUS_CODES[200])
                ->send();
            return;
        } catch (AuthenticationException $e) {
            $response
                ->appendArray([
                    'message' => $e->getMessage()
                ])
                ->status(HttpRouter::STATUS_CODES[401])
                ->send();
            return;
        } catch (ControllerUndefinedValueException | HttpJsonParseException | EntityInvalidValueException $e) {
            $response
                ->appendArray([
                    'message' => $e->getMessage()
                ])
                ->status(HttpRouter::STATUS_CODES[400])
                ->send();
            return;
        } catch (
            ControllerOperationErrorException |
            DatabaseStatementCreationFailureException |
            DatabaseStatementExecutionFailureException |
            PDOException $e
        ) {
            $response
                ->appendArray([
                    'message' => $e->getMessage()
                ])
                ->status(HttpRouter::STATUS_CODES[500])
                ->send();
            return;
        }
    }

    public function findById(HttpRequest $request, HttpResponse $response)
    {
        try {
            $headers = $request->getHeaders();
            $token = AuthorizationTokenRetriever::getFromHeaders($headers);
            $isAuthenticated = $this->authService->validateToken($token);
            if ($isAuthenticated === false) {
                throw new AuthenticationException('Usuário não autenticado!');
            }

            $params = $request->getParams();

            $isUserIdSetted = isset($params['userId']);
            if ($isUserIdSetted === false) {
                throw new ControllerUndefinedValueException(
                    'O id do usuário não foi informado na URL ou seu valor é null!'
                );
            }

            $userId = $params['userId'];

            $user = $this->service->findById($userId);

            if ($user === null) {
                $response
                    ->appendArray([
                        'message' => 'O registro de usuário com o id ' . $userId . ' não pôde ser encontrado!',
                    ])
                    ->status(HttpRouter::STATUS_CODES[200])
                    ->send();
                return;
            }

            $response
                ->appendArray([
                    'message' => 'Usuário buscado com sucesso!',
                    'data' => [
                        'id' => $user->getId(),
                        'username' => $user->getUserName(),
                        'password' => $user->getPassWord(),
                        'isActive' => $user->getIsActive()
                    ]
                ])
                ->status(HttpRouter::STATUS_CODES[200])
                ->send();
            return;
        } catch (AuthenticationException $e) {
            $response
                ->appendArray([
                    'message' => $e->getMessage()
                ])
                ->status(HttpRouter::STATUS_CODES[401])
                ->send();
            return;
        } catch (
            ControllerUndefinedValueException |
            EntityInvalidValueException $e
        ) {
            $response
                ->appendArray([
                    'message' => $e->getMessage()
                ])
                ->status(HttpRouter::STATUS_CODES[400])
                ->send();
            return;
        } catch (
            DatabaseFetchFailureException |
            DatabaseStatementCreationFailureException |
            DatabaseStatementExecutionFailureException |
            PDOException $e
        ) {
            $response
                ->appendArray([
                    'message' => $e->getMessage()
                ])
                ->status(HttpRouter::STATUS_CODES[500])
                ->send();
            return;
        }
    }

    public function findByUserName(HttpRequest $request, HttpResponse $response)
    {
        try {
            $headers = $request->getHeaders();
            $token = AuthorizationTokenRetriever::getFromHeaders($headers);
            $isAuthenticated = $this->authService->validateToken($token);
            if ($isAuthenticated === false) {
                throw new AuthenticationException('Usuário não autenticado!');
            }

            $params = $request->getParams();

            $isUserNameSetted = isset($params['userName']);
            if ($isUserNameSetted === false) {
                throw new ControllerUndefinedValueException(
                    'O nome de usuário não foi informado na URL ou seu valor é null!'
                );
            }

            $userName = $params['userName'];

            $user = $this->service->findByUserName($userName);

            if ($user === null) {
                $response
                    ->appendArray([
                        'message' => 'O registro de usuário com o nome de usuário ' .
                            $userName . ' não pôde ser encontrado!',
                    ])
                    ->status(HttpRouter::STATUS_CODES[200])
                    ->send();
                return;
            }

            $response
                ->appendArray([
                    'message' => 'Usuário buscado com sucesso!',
                    'data' => [
                        'id' => $user->getId(),
                        'username' => $user->getUserName(),
                        'password' => $user->getPassWord(),
                        'isActive' => $user->getIsActive()
                    ]
                ])
                ->status(HttpRouter::STATUS_CODES[200])
                ->send();
            return;
        } catch (AuthenticationException $e) {
            $response
                ->appendArray([
                    'message' => $e->getMessage()
                ])
                ->status(HttpRouter::STATUS_CODES[401])
                ->send();
            return;
        } catch (
            ControllerUndefinedValueException |
            EntityInvalidValueException $e
        ) {
            $response
                ->appendArray([
                    'message' => $e->getMessage()
                ])
                ->status(HttpRouter::STATUS_CODES[400])
                ->send();
            return;
        } catch (
            DatabaseFetchFailureException |
            DatabaseStatementCreationFailureException |
            DatabaseStatementExecutionFailureException |
            PDOException $e
        ) {
            $response
                ->appendArray([
                    'message' => $e->getMessage()
                ])
                ->status(HttpRouter::STATUS_CODES[500])
                ->send();
            return;
        }
    }

    public function findAll(HttpRequest $request, HttpResponse $response)
    {
        try {
            $headers = $request->getHeaders();
            $token = AuthorizationTokenRetriever::getFromHeaders($headers);
            $isAuthenticated = $this->authService->validateToken($token);
            if ($isAuthenticated === false) {
                throw new AuthenticationException('Usuário não autenticado!');
            }

            $users = $this->service->findAll();

            $numberOfUsersFound = count($users);
            if ($numberOfUsersFound === 0) {
                $response
                    ->appendArray([
                        'message' => 'A busca foi concluída e nenhum usuário foi encontrado.',
                    ])
                    ->status(HttpRouter::STATUS_CODES[200])
                    ->send();
                return;
            }

            foreach ($users as $user) {
                $data[] = [
                    'id' => $user->getId(),
                    'username' => $user->getUserName(),
                    'password' => $user->getPassWord(),
                    'isActive' => $user->getIsActive()
                ];
            }

            $response
                ->appendArray([
                    'message' => 'Usuários buscados com sucesso!',
                    'data' => $data
                ])
                ->status(HttpRouter::STATUS_CODES[200])
                ->send();
            return;
        } catch (AuthenticationException $e) {
            $response
                ->appendArray([
                    'message' => $e->getMessage()
                ])
                ->status(HttpRouter::STATUS_CODES[401])
                ->send();
            return;
        } catch (
            DatabaseStatementCreationFailureException |
            DatabaseStatementExecutionFailureException |
            PDOException $e
        ) {
            $response
                ->appendArray([
                    'message' => $e->getMessage()
                ])
                ->status(HttpRouter::STATUS_CODES[500])
                ->send();
            return;
        }
    }
}
