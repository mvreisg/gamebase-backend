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
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\HttpUnauthorizedException;
use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpRequest;
use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpResponse;
use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpRouter;
use Mvreisg\GamebaseBackend\Presentation\Exceptions\ControllerInvalidValueException;
use Mvreisg\GamebaseBackend\Presentation\Exceptions\ControllerUndefinedValueException;
use PDOException;

class AuthenticationController
{
    private AuthenticationService $service;

    public function __construct(AuthenticationService $service)
    {
        $this->service = $service;
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

            $token = $this->service->checkIfTokenExists($userName);
            if ($token !== null && $token !== "") {
                $isTokenValid = $this->service->validateToken($userName, $token);
                if ($isTokenValid) {
                    $response
                        ->appendArray([
                            'message' => 'Já existe uma sessão!',
                            'token' => $token
                        ])
                        ->status(HttpRouter::STATUS_CODES[200])
                        ->send();
                    return;
                }
            }

            $hasCredentials = $this->service->tryLogin($userName, $passWord);
            if ($hasCredentials) {
                $token = $this->service->checkIfTokenExists($userName);
                if ($token === null || $token === "") {
                    $token = $this->service->generateToken($userName, $oneWeek);
                    $response
                        ->appendArray([
                            'message' =>
                                'Login realizado com sucesso! Durará ' .
                                ($oneWeek ? '1 semana' : '1 dia') . '.',
                            'token' => $token
                        ])
                        ->status(HttpRouter::STATUS_CODES[200])
                        ->send();
                    return;
                } else {
                    $response
                        ->appendArray([
                            'message' => 'Já existe uma sessão!',
                            'token' => $token
                        ])
                        ->status(HttpRouter::STATUS_CODES[200])
                        ->send();
                    return;
                }
            } else {
                $response
                    ->appendArray([
                        'message' => 'Verifique seu nome de usuário ou senha!'
                    ])
                    ->status(HttpRouter::STATUS_CODES[401])
                    ->send();
                return;
            }
        } catch (
            HttpJsonParseException |
            AuthenticationException |
            ControllerInvalidValueException |
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
            EncryptionException |
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

            $isTokenValid = $this->service->validateToken($token);
            if ($isTokenValid) {
                $response
                    ->appendArray([
                        'message' => 'Usuário possui sessão ativa'
                    ])
                    ->status(HttpRouter::STATUS_CODES[200])
                    ->send();
                return;
            } else {
                $response
                    ->appendArray([
                        'message' => 'Token inválido'
                    ])
                    ->status(HttpRouter::STATUS_CODES[401])
                    ->send();
                return;
            }
        } catch (
            AuthenticationException |
            HttpUnauthorizedException $e
        ) {
            $response
                ->appendArray([
                    'message' => $e->getMessage()
                ])
                ->status(HttpRouter::STATUS_CODES[401])
                ->send();
            return;
        } catch (
            HttpJsonParseException |
            EntityInvalidValueException |
            ControllerUndefinedValueException $e
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

            $hasSuccess = $this->service->tryLogoff($token);

            if ($hasSuccess) {
                $response
                    ->appendArray([
                        'message' => 'Logoff realizado com sucesso!'
                    ])
                    ->status(HttpRouter::STATUS_CODES[200])
                    ->send();
                return;
            } else {
                $response
                    ->appendArray([
                        'message' => 'Erro ao realizar o logoff!'
                    ])
                    ->status(HttpRouter::STATUS_CODES[401])
                    ->send();
                return;
            }
        } catch (
            AuthenticationException |
            HttpUnauthorizedException $e
        ) {
            $response
                ->appendArray([
                    'message' => $e->getMessage()
                ])
                ->status(HttpRouter::STATUS_CODES[401])
                ->send();
            return;
        } catch (
            HttpJsonParseException |
            EntityInvalidValueException |
            ControllerUndefinedValueException $e
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
}
