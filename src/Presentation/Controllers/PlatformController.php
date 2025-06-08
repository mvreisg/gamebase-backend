<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Controllers;

use Mvreisg\GamebaseBackend\Application\Exceptions\AuthenticationException;
use Mvreisg\GamebaseBackend\Application\Services\AuthenticationService;
use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpRequest;
use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpResponse;
use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpRouter;
use Mvreisg\GamebaseBackend\Application\Services\PlatformService;
use Mvreisg\GamebaseBackend\Domain\Exceptions\EntityInvalidValueException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseDuplicatedEntryException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseFetchFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseStatementCreationFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseStatementExecutionFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseTransactionCreationFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\HttpJsonParseException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\HttpResourceNotFoundException;
use Mvreisg\GamebaseBackend\Infrastructure\Http\AuthorizationTokenRetriever;
use Mvreisg\GamebaseBackend\Presentation\Exceptions\ControllerOperationErrorException;
use Mvreisg\GamebaseBackend\Presentation\Exceptions\ControllerUndefinedValueException;
use PDOException;

class PlatformController
{
    private PlatformService $service;
    private AuthenticationService $authService;

    public function __construct(PlatformService $service, AuthenticationService $authService)
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

            $isNameSetted = isset($body['name']);
            if ($isNameSetted === false) {
                throw new ControllerUndefinedValueException(
                    'A chave name não foi definida no JSON ou seu valor é null!'
                );
            }

            $isIsActiveFieldSetted = isset($body['isActive']);
            if ($isIsActiveFieldSetted === false) {
                throw new ControllerUndefinedValueException('A chave isActive não existe ou seu valor é null');
            }

            $name = $body['name'];
            $isActive = $body['isActive'];

            $platform = $this->service->insert($name, $isActive);

            $response
                ->appendArray([
                    'message' => 'Plataforma incluída com sucesso!',
                    'data' => [
                        'id' => $platform->getId(),
                        'name' => $platform->getName(),
                        'isActive' => $platform->getIsActive()
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
            DatabaseDuplicatedEntryException |
            EntityInvalidValueException $e
        ) {
            $messages[] = $e->getMessage();
            $response
                ->appendArray([
                    'messages' => $messages
                ])
                ->status(HttpRouter::STATUS_CODES[400])
                ->send();
            return;
        } catch (
            DatabaseTransactionCreationFailureException |
            DatabaseStatementCreationFailureException |
            DatabaseStatementExecutionFailureException |
            DatabaseFetchFailureException |
            PDOException $e
        ) {
            $messages[] = $e->getMessage();
            $response
                ->appendArray([
                    'messages' => $messages
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

            $params = $request->getParams();
            $body = $request->parseBodyFromJSONString();

            $isPlatformIdSetted = isset($params['platformId']);
            if ($isPlatformIdSetted === false) {
                throw new ControllerUndefinedValueException(
                    'O parâmetro platformId não foi informado na URL ou seu valor é null!'
                );
            }

            $isNameSetted = isset($body['name']);
            if ($isNameSetted === false) {
                throw new ControllerUndefinedValueException(
                    'A chave name não foi definida no JSON ou seu valor é null!'
                );
            }

            $isIsActiveFieldSetted = isset($body['isActive']);
            if ($isIsActiveFieldSetted === false) {
                throw new ControllerUndefinedValueException('A chave isActive não foi informada ou seu valor é null!');
            }

            $platformId = $params['platformId'];
            $name = $body['name'];
            $isActive = $body['isActive'];

            $wasTheUpdateSuccessful = $this->service->update($platformId, $name, $isActive);
            if ($wasTheUpdateSuccessful === false) {
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
                    'message' => 'Plataforma atualizada com sucesso!'
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
        } catch (HttpResourceNotFoundException $e) {
            $response
                ->appendArray([
                    'message' => $e->getMessage()
                ])
                ->status(HttpRouter::STATUS_CODES[404])
                ->send();
            return;
        } catch (
            ControllerOperationErrorException |
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
        } catch (DatabaseStatementCreationFailureException | PDOException $e) {
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

            $isPlatformIdSetted = isset($params['platformId']);
            if ($isPlatformIdSetted === false) {
                throw new ControllerUndefinedValueException('O parâmetro platformId não foi informado na URL!');
            }

            $isIsActiveFieldSetted = isset($body['isActive']);
            if ($isIsActiveFieldSetted === false) {
                throw new ControllerUndefinedValueException('A chave isActive não existe ou seu valor é null!');
            }

            $platformId = $params['platformId'];
            $isActive = $body['isActive'];

            $wasTheUpdateOcurred = $this->service->setIsActive($platformId, $isActive);
            if ($wasTheUpdateOcurred === false) {
                throw new ControllerOperationErrorException(
                    'Ocorreu um erro! Verifique se o id ' .
                    $platformId .
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
        } catch (
            ControllerUndefinedValueException |
            HttpJsonParseException |
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

            $isPlatformIdSetted = isset($params['platformId']);
            if ($isPlatformIdSetted === false) {
                throw new ControllerUndefinedValueException(
                    'O parâmetro platformId não está definido no JSON ou seu valor é null!'
                );
            }

            $platformId = $params['platformId'];

            $platform = $this->service->findById($platformId);
            if ($platform === null) {
                throw new HttpResourceNotFoundException(
                    'A plataforma procurada não foi encontrada!'
                );
            }

            $response
                ->appendArray([
                    'message' => 'Plataforma encontrada com sucesso!',
                    'data' => [
                        'id' => $platform->getId(),
                        'name' => $platform->getName(),
                        'isActive' => $platform->getIsActive()
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
        } catch (HttpResourceNotFoundException $e) {
            $response
                ->appendArray([
                    'message' => $e->getMessage()
                ])
                ->status(HttpRouter::STATUS_CODES[404])
                ->send();
            return;
        } catch (ControllerUndefinedValueException | EntityInvalidValueException $e) {
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

            $platforms = $this->service->findAll();

            $numberOfPlatforms = count($platforms);
            if ($numberOfPlatforms === 0) {
                throw new HttpResourceNotFoundException(
                    'A busca foi concluída e nenhuma plataforma foi encontrada.'
                );
            }

            foreach ($platforms as $platform) {
                $data[] = [
                    'id' => $platform->getId(),
                    'name' => $platform->getName(),
                    'isActive' => $platform->getIsActive()
                ];
            }

            $response
                ->appendArray([
                    'message' => 'Plataformas buscadas com sucesso!',
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
        } catch (HttpResourceNotFoundException $e) {
            $response
                ->appendArray([
                    'message' => $e->getMessage()
                ])
                ->status(HttpRouter::STATUS_CODES[404])
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
