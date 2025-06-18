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
use Mvreisg\GamebaseBackend\Presentation\Exceptions\ControllerUndefinedValueException;
use Mvreisg\GamebaseBackend\Presentation\Middlewares\RouteAuthenticator;
use PDOException;

class PlatformController
{
    private PlatformService $platformService;
    private AuthenticationService $authenticationService;

    public function __construct(
        PlatformService $platformService,
        AuthenticationService $authenticationService
    ) {
        $this->platformService = $platformService;
        $this->authenticationService = $authenticationService;
    }

    public function insert(HttpRequest $request, HttpResponse $response): void
    {
        try {
            RouteAuthenticator::make($this->authenticationService)->validate($request, $response);

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

            $platform = $this->platformService->insert($name, $isActive);

            $response
                ->setBody([
                    'message' => 'Plataforma incluída com sucesso!',
                    'data' => [
                        'id' => $platform->getId(),
                        'name' => $platform->getName(),
                        'isActive' => $platform->getIsActive()
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
            DatabaseTransactionCreationFailureException |
            DatabaseStatementCreationFailureException |
            DatabaseStatementExecutionFailureException |
            DatabaseFetchFailureException |
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

    public function update(HttpRequest $request, HttpResponse $response): void
    {
        try {
            RouteAuthenticator::make($this->authenticationService)->validate($request, $response);

            $params = $request->getParams();
            $body = $request->parseBodyFromJSONString();

            $isIdSetted = isset($params['id']);
            if ($isIdSetted === false) {
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

            $id = $params['id'];
            $name = $body['name'];
            $isActive = $body['isActive'];

            $wasTheUpdateSuccessful = $this->platformService->update($id, $name, $isActive);
            if ($wasTheUpdateSuccessful === false) {
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
                    'message' => 'Plataforma atualizada com sucesso!'
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
        } catch (DatabaseStatementCreationFailureException | PDOException $e) {
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
                throw new ControllerUndefinedValueException('O parâmetro platformId não foi informado na URL!');
            }

            $isIsActiveFieldSetted = isset($body['isActive']);
            if ($isIsActiveFieldSetted === false) {
                throw new ControllerUndefinedValueException('A chave isActive não existe ou seu valor é null!');
            }

            $id = $params['id'];
            $isActive = $body['isActive'];

            $wasTheUpdateOcurred = $this->platformService->setIsActive($id, $isActive);
            if ($wasTheUpdateOcurred === false) {
                $response
                    ->setBody([
                        'message' => 'Nenhum registro modificado!'
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

    public function findById(HttpRequest $request, HttpResponse $response): void
    {
        try {
            RouteAuthenticator::make($this->authenticationService)->validate($request, $response);

            $params = $request->getParams();

            $isIdSetted = isset($params['id']);
            if ($isIdSetted === false) {
                throw new ControllerUndefinedValueException(
                    'O parâmetro platformId não está definido no JSON ou seu valor é null!'
                );
            }

            $id = $params['id'];

            $platform = $this->platformService->findById($id);
            if ($platform === null) {
                $response
                    ->setBody([
                        'message' => 'A plataforma procurada não foi encontrada!',
                    ])
                    ->setStatus(HttpRouter::$STATUS_CODES[200])
                    ->send(HttpRouter::$CONTENT_TYPES['JSON']);
                return;
            }

            $response
                ->setBody([
                    'message' => 'Plataforma encontrada com sucesso!',
                    'data' => [
                        'id' => $platform->getId(),
                        'name' => $platform->getName(),
                        'isActive' => $platform->getIsActive()
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

            $platforms = $this->platformService->findAll();

            $numberOfPlatforms = count($platforms);
            if ($numberOfPlatforms === 0) {
                $response
                    ->setBody([
                        'message' => 'A busca foi concluída e nenhuma plataforma foi encontrada.',
                    ])
                    ->setStatus(HttpRouter::$STATUS_CODES[200])
                    ->send(HttpRouter::$CONTENT_TYPES['JSON']);
                return;
            }

            foreach ($platforms as $platform) {
                $data[] = [
                    'id' => $platform->getId(),
                    'name' => $platform->getName(),
                    'isActive' => $platform->getIsActive()
                ];
            }

            $response
                ->setBody([
                    'message' => 'Plataformas buscadas com sucesso!',
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
