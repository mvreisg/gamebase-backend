<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Controllers;

use Mvreisg\GamebaseBackend\Application\Exceptions\AuthenticationException;
use Mvreisg\GamebaseBackend\Application\Services\AuthenticationService;
use Mvreisg\GamebaseBackend\Domain\Exceptions\EntityInvalidValueException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseFetchFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseStatementCreationFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseStatementExecutionFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\EncryptionException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\HttpJsonParseException;
use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpRequest;
use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpResponse;
use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpRouter;
use Mvreisg\GamebaseBackend\Presentation\Exceptions\ControllerUndefinedValueException;
use PDOException;

class AuthenticationController
{
    private AuthenticationService $authenticationService;

    public function __construct(
        AuthenticationService $authenticationService
    ) {
        $this->authenticationService = $authenticationService;
    }

    public function handleLogin(HttpRequest $request, HttpResponse $response): void
    {
        $userName = null;
        try {
            $body = $request->parseBodyFromJSONString();

            $isUserNameFieldSetted = isset($body['username']);
            if ($isUserNameFieldSetted === false) {
                throw new ControllerUndefinedValueException('A chave username não existe ou seu valor é null!');
            }

            $isPassWordFieldSetted = isset($body['password']);
            if ($isPassWordFieldSetted === false) {
                throw new ControllerUndefinedValueException('A chave password não existe ou seu valor é null!');
            }

            $isOneWeekFieldSetted = isset($body['oneWeek']);
            if ($isOneWeekFieldSetted === false) {
                throw new ControllerUndefinedValueException('A chave oneWeek não existe ou seu valor é null!');
            }

            $userName = $body['username'];
            $passWord = $body['password'];
            $oneWeek = $body['oneWeek'];

            $token = $this->authenticationService->checkIfTokenExists($userName);
            if ($token !== null && $token !== "") {
                $isTokenValid = $this->authenticationService->validateToken($userName, $token);
                if ($isTokenValid) {
                    $response
                        ->setBody([
                            'message' => 'Já existe uma sessão!',
                            'token' => $token
                        ])
                        ->setStatus(HttpRouter::$STATUS_CODES[200])
                        ->send(HttpRouter::$CONTENT_TYPES['JSON']);
                    return;
                }
            }

            $hasCredentials = $this->authenticationService->tryLogin($userName, $passWord);
            if ($hasCredentials) {
                $token = $this->authenticationService->checkIfTokenExists($userName);
                if ($token === null || $token === "") {
                    $token = $this->authenticationService->generateToken($userName, $oneWeek);
                    $response
                        ->setBody([
                            'message' =>
                                'Login realizado com sucesso! Durará ' .
                                ($oneWeek ? '1 semana' : '1 dia') . '.',
                            'token' => $token
                        ])
                        ->setStatus(HttpRouter::$STATUS_CODES[200])
                        ->send(HttpRouter::$CONTENT_TYPES['JSON']);
                    return;
                } else {
                    $response
                        ->setBody([
                            'message' => 'Já existe uma sessão!',
                            'token' => $token
                        ])
                        ->setStatus(HttpRouter::$STATUS_CODES[200])
                        ->send(HttpRouter::$CONTENT_TYPES['JSON']);
                    return;
                }
            } else {
                throw new AuthenticationException('Verifique seu nome de usuário e senha.');
            }
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
            EncryptionException |
            DatabaseFetchFailureException |
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

    public function handleValidation(HttpRequest $request, HttpResponse $response)
    {
        try {
            $body = $request->parseBodyFromJSONString();
            $isTokenSetted = isset($body['token']);
            if ($isTokenSetted === false) {
                throw new ControllerUndefinedValueException(
                    'A chave token não foi definida no JSON ou seu valor é null!'
                );
            }

            $token = $body['token'];

            $isTokenValid = $this->authenticationService->validateToken($token);
            if ($isTokenValid) {
                $response
                    ->setBody([
                        'message' => 'Usuário possui sessão ativa'
                    ])
                    ->setStatus(HttpRouter::$STATUS_CODES[200])
                    ->send(HttpRouter::$CONTENT_TYPES['JSON']);
                return;
            } else {
                throw new AuthenticationException('Token inválido');
            }
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
            EntityInvalidValueException |
            ControllerUndefinedValueException $e
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

    public function handleLogoff(HttpRequest $request, HttpResponse $response)
    {
        try {
            $body = $request->parseBodyFromJSONString();
            $isTokenSetted = isset($body['token']);
            if ($isTokenSetted === false) {
                throw new ControllerUndefinedValueException(
                    'A chave token não foi definida no JSON ou seu valor é null!'
                );
            }

            $token = $body['token'];

            $hasSuccess = $this->authenticationService->tryLogoff($token);

            if ($hasSuccess) {
                $response
                    ->setBody([
                        'message' => 'Logoff realizado com sucesso!'
                    ])
                    ->setStatus(HttpRouter::$STATUS_CODES[200])
                    ->send(HttpRouter::$CONTENT_TYPES['JSON']);
                return;
            } else {
                throw new AuthenticationException('Erro ao realizar o logoff!');
            }
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
            EntityInvalidValueException |
            ControllerUndefinedValueException $e
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
