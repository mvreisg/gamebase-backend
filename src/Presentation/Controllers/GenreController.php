<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Controllers;

use Mvreisg\GamebaseBackend\Application\Exceptions\AuthenticationException;
use Mvreisg\GamebaseBackend\Application\Services\AuthenticationService;
use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpRequest;
use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpResponse;
use Mvreisg\GamebaseBackend\Application\Services\GenreService;
use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpRouter;
use Mvreisg\GamebaseBackend\Domain\Exceptions\EntityInvalidValueException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseDuplicatedEntryException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseFetchFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseStatementCreationFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseStatementExecutionFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\HttpJsonParseException;
use Mvreisg\GamebaseBackend\Presentation\Exceptions\ControllerUndefinedValueException;
use Mvreisg\GamebaseBackend\Presentation\Middlewares\RouteAuthenticator;
use PDOException;
use Throwable;

class GenreController
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
            RouteAuthenticator::make($this->authenticationService)->validate($request, $response);

            $body = $request->parseBodyFromJSONString();

            $isNameFieldSetted = isset($body['name']);
            if ($isNameFieldSetted === false) {
                throw new ControllerUndefinedValueException('A chave name não existe ou seu valor é null');
            }

            $isIsActiveFieldSetted = isset($body['isActive']);
            if ($isIsActiveFieldSetted === false) {
                throw new ControllerUndefinedValueException('A chave isActive não existe ou seu valor é null');
            }

            $name = $body['name'];
            $isActive = $body['isActive'];

            $genre = $this->genreService->insert($name, $isActive);

            $response
                ->setBody([
                    'message' => 'Gênero inserido com sucesso!',
                    'data' => [
                        'id' => $genre->getId(),
                        'name' => $genre->getName(),
                        'isActive' => $genre->getIsActive()
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
                throw new ControllerUndefinedValueException(
                    'O parâmetro genreId não foi informado na URL ou seu valor é null!'
                );
            }

            $isNameFieldSetted = isset($body['name']);
            if ($isNameFieldSetted === false) {
                throw new ControllerUndefinedValueException('A chave name não foi informada ou seu valor é null!');
            }

            $isIsActiveFieldSetted = isset($body['isActive']);
            if ($isIsActiveFieldSetted === false) {
                throw new ControllerUndefinedValueException('A chave isActive não foi informada ou seu valor é null!');
            }

            $id = $params['id'];
            $name = $body['name'];
            $isActive = $body['isActive'];

            $wasAUpdateOcurred = $this->genreService->update($id, $name, $isActive);
            if ($wasAUpdateOcurred === false) {
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
                    'message' => 'Gênero atualizado com sucesso!'
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
                throw new ControllerUndefinedValueException('O parâmetro genreId não foi informado na URL!');
            }

            $isIsActiveFieldSetted = isset($body['isActive']);
            if ($isIsActiveFieldSetted === false) {
                throw new ControllerUndefinedValueException('A chave isActive não existe ou seu valor é null!');
            }

            $id = $params['id'];
            $isActive = $body['isActive'];

            $wasTheUpdateOcurred = $this->genreService->setIsActive($id, $isActive);
            if ($wasTheUpdateOcurred === false) {
                $response
                    ->setBody([
                        'message' => 'Nenhum registro foi modificado!'
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
                    'O parâmetro genreId não foi informado ou seu valor é null!'
                );
            }

            $id = $params['id'];

            $genre = $this->genreService->findById($id);

            if ($genre === null) {
                $response
                    ->setBody([
                        'message' => 'O gênero com o id ' . $id . ' não existe!',
                    ])
                    ->setStatus(HttpRouter::$STATUS_CODES[200])
                    ->send(HttpRouter::$CONTENT_TYPES['JSON']);
                return;
            }

            $response
                ->setBody([
                    'message' => 'Gênero encontrado com sucesso!',
                    'data' => [
                        'id' => $genre->getId(),
                        'name' => $genre->getName(),
                        'isActive' => $genre->getIsActive()
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

            $genres = $this->genreService->findAll();

            $numberOfGenresFound = count($genres);
            if ($numberOfGenresFound === 0) {
                $response
                    ->setBody([
                        'message' => 'Nenhum registro encontrado!',
                    ])
                    ->setStatus(HttpRouter::$STATUS_CODES[200])
                    ->send(HttpRouter::$CONTENT_TYPES['JSON']);
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
                    'message' => 'Gêneros buscados com sucesso!',
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
