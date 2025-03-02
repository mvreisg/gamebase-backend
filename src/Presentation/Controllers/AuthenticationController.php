<?php

namespace Mvreisg\GamebaseBackend\Presentation\Controllers;

use Mvreisg\GamebaseBackend\Application\Exceptions\AuthenticationException;
use Mvreisg\GamebaseBackend\Application\Exceptions\SessionException;
use Mvreisg\GamebaseBackend\Application\Services\AuthenticationService;
use Mvreisg\GamebaseBackend\Domain\Exceptions\EntityInvalidValueException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseFetchFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseStatementCreationFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseStatementExecutionFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\EncryptionErrorException;
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

            $userName = $body['username'];
            $passWord = $body['password'];

            $hasSession = $this->service->checkIfHasSession($userName);
            if ($hasSession) {
                $token = $this->service->getSessionToken($userName);
                $isTokenValid = $this->service->validateToken($token);
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

            $hasCredentials = $this->service->login($userName, $passWord);
            if ($hasCredentials === false) {
                $response
                    ->appendArray([
                        'message' => 'Verifique seu nome de usuário ou senha!'
                    ])
                    ->status(HttpRouter::STATUS_CODES[401])
                    ->sendJSON();
                return;
            }

            $token = $this->service->generateToken($userName);
            $this->service->setSessionToken($userName, $token);
            $response
                ->appendArray([
                    'token' => $token
                ])
                ->status(HttpRouter::STATUS_CODES[200])
                ->send();
            return;
        } catch (
            AuthenticationException |
            SessionException |
            ControllerInvalidValueException |
            ControllerUndefinedValueException |
            EntityInvalidValueException $e
        ) {
            $this->service->logoff($userName);
            $response
                ->appendArray([
                    'message' => $e->getMessage()
                ])
                ->status(HttpRouter::STATUS_CODES[400])
                ->sendJSON();
            return;
        } catch (
            EncryptionErrorException |
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
                ->sendJSON();
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

            $isValid = $this->service->validateToken($token);
            if ($isValid === false) {
                throw new HttpUnauthorizedException('Usuário não possui sessão!');
            }

            $response
                ->status(HttpRouter::STATUS_CODES[200])
                ->sendJSON();
            return;
        } catch (
            SessionException |
            HttpUnauthorizedException $e
        ) {
            $response
                ->appendArray([
                    'message' => $e->getMessage()
                ])
                ->status(HttpRouter::STATUS_CODES[401])
                ->sendJSON();
            return;
        } catch (
            EntityInvalidValueException |
            ControllerUndefinedValueException $e
        ) {
            $response
                ->appendArray([
                    'message' => $e->getMessage()
                ])
                ->status(HttpRouter::STATUS_CODES[400])
                ->sendJSON();
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
                ->sendJSON();
            return;
        }
    }
}
