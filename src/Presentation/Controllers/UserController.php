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
use Mvreisg\GamebaseBackend\Presentation\Exceptions\ControllerUndefinedValueException;
use Mvreisg\GamebaseBackend\Presentation\Middlewares\RouteAuthenticator;
use Throwable;

class UserController
{
    private UserService $userService;
    private AuthenticationService $authenticationService;

    public function __construct(
        UserService $userService,
        AuthenticationService $authenticationService
    ) {
        $this->userService = $userService;
        $this->authenticationService = $authenticationService;
    }

    public function insert(HttpRequest $request, HttpResponse $response): void
    {
        try {
            RouteAuthenticator::make($this->authenticationService)->validate($request, $response);

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

            $user = $this->userService->insert($userName, $passWord, $isActive);

            $response
                ->setBody([
                    'message' => 'Registro de usuário inserido com sucesso!',
                    'data' => [
                        'id' => $user->getId(),
                        'username' => $user->getUserName(),
                        'password' => $user->getPassWord(),
                        'isActive' => $user->getIsActive()
                    ]
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
            EntityInvalidValueException |
            DatabaseDuplicatedEntryException $e
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

            $id = $params['id'];
            $userName = $body['username'];
            $passWord = $body['password'];
            $isActive = $body['isActive'];

            $wasSomeUpdateHappened = $this->userService->update($id, $userName, $passWord, $isActive);
            if ($wasSomeUpdateHappened === false) {
                $response
                    ->setBody([
                        'message' => 'Nenhuma linha afetada.'
                    ])
                    ->setStatus(HttpRouter::$STATUS_CODES[200])
                    ->send(HttpRouter::$CONTENT_TYPES['JSON']);
                return;
            }

            $response
                ->setBody([
                    'message' => 'Dados do usuário atualizados com sucesso!'
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
            DatabaseDuplicatedEntryException |
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

    public function setIsActive(HttpRequest $request, HttpResponse $response): void
    {
        try {
            RouteAuthenticator::make($this->authenticationService)->validate($request, $response);

            $params = $request->getParams();
            $body = $request->parseBodyFromJSONString();

            $isIdSetted = isset($params['id']);
            if ($isIdSetted === false) {
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

            $id = $params['id'];
            $isActive = $body['isActive'];

            $wasTheUpdateOcurred = $this->userService->setIsActive($id, $isActive);
            if ($wasTheUpdateOcurred === false) {
                $response
                    ->setBody([
                        'message' => 'Nenhum valor foi modificado!'
                    ])
                    ->setStatus(HttpRouter::$STATUS_CODES[200])
                    ->send(HttpRouter::$CONTENT_TYPES['JSON']);
                return;
            }

            $response
                ->setBody([
                    'message' => 'Estado atualizado com sucesso!'
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
        } catch (ControllerUndefinedValueException | HttpJsonParseException | EntityInvalidValueException $e) {
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

    public function findById(HttpRequest $request, HttpResponse $response): void
    {
        try {
            RouteAuthenticator::make($this->authenticationService)->validate($request, $response);

            $params = $request->getParams();

            $isIdSetted = isset($params['id']);
            if ($isIdSetted === false) {
                throw new ControllerUndefinedValueException(
                    'O id do usuário não foi informado na URL ou seu valor é null!'
                );
            }

            $id = $params['id'];

            $user = $this->userService->findById($id);

            if ($user === null) {
                $response
                    ->setBody([
                        'message' => 'O registro de usuário com o id ' . $id . ' não pôde ser encontrado!',
                    ])
                    ->setStatus(HttpRouter::$STATUS_CODES[200])
                    ->send(HttpRouter::$CONTENT_TYPES['JSON']);
                return;
            }

            $response
                ->setBody([
                    'message' => 'Usuário buscado com sucesso!',
                    'data' => [
                        'id' => $user->getId(),
                        'username' => $user->getUserName(),
                        'password' => $user->getPassWord(),
                        'isActive' => $user->getIsActive()
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
            DatabaseFetchFailureException |
            DatabaseStatementCreationFailureException |
            DatabaseStatementExecutionFailureException |
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

    public function findByUserName(HttpRequest $request, HttpResponse $response): void
    {
        try {
            RouteAuthenticator::make($this->authenticationService)->validate($request, $response);

            $params = $request->getParams();

            $isUserNameSetted = isset($params['username']);
            if ($isUserNameSetted === false) {
                throw new ControllerUndefinedValueException(
                    'O nome de usuário não foi informado na URL ou seu valor é null!'
                );
            }

            $userName = $params['username'];

            $user = $this->userService->findByUserName($userName);

            if ($user === null) {
                $response
                    ->setBody([
                        'message' => 'O registro de usuário com o nome de usuário ' .
                            $userName . ' não pôde ser encontrado!',
                    ])
                    ->setStatus(HttpRouter::$STATUS_CODES[200])
                    ->send(HttpRouter::$CONTENT_TYPES['JSON']);
                return;
            }

            $response
                ->setBody([
                    'message' => 'Usuário buscado com sucesso!',
                    'data' => [
                        'id' => $user->getId(),
                        'username' => $user->getUserName(),
                        'password' => $user->getPassWord(),
                        'isActive' => $user->getIsActive()
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
            DatabaseFetchFailureException |
            DatabaseStatementCreationFailureException |
            DatabaseStatementExecutionFailureException |
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

    public function findAll(HttpRequest $request, HttpResponse $response): void
    {
        try {
            RouteAuthenticator::make($this->authenticationService)->validate($request, $response);

            $users = $this->userService->findAll();

            $numberOfUsersFound = count($users);
            if ($numberOfUsersFound === 0) {
                $response
                    ->setBody([
                        'message' => 'A busca foi concluída e nenhum usuário foi encontrado.',
                    ])
                    ->setStatus(HttpRouter::$STATUS_CODES[200])
                    ->send(HttpRouter::$CONTENT_TYPES['JSON']);
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
                ->setBody([
                    'message' => 'Usuários buscados com sucesso!',
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
}
