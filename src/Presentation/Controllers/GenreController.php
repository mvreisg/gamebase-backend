<?php

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
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\HttpResourceNotFoundException;
use Mvreisg\GamebaseBackend\Infrastructure\Http\AuthorizationTokenRetriever;
use Mvreisg\GamebaseBackend\Presentation\Exceptions\ControllerOperationErrorException;
use Mvreisg\GamebaseBackend\Presentation\Exceptions\ControllerUndefinedValueException;
use PDOException;

class GenreController
{
    private GenreService $service;
    private AuthenticationService $authService;

    public function __construct(GenreService $service, AuthenticationService $authService)
    {
        $this->service = $service;
        $this->authService = $authService;
    }

    public function insert(HttpRequest $request, HttpResponse $response)
    {
        try {
            $headers = $request->getHeaders();
            $token = AuthorizationTokenRetriever::getFromHeaders($headers);
            $this->authService->validateToken($token);

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

            $genre = $this->service->insert($name, $isActive);

            $response
                ->appendArray([
                    'message' => 'Gênero inserido com sucesso!',
                    'data' => [
                        'id' => $genre->getId(),
                        'name' => $genre->getName(),
                        'isActive' => $genre->getIsActive()
                    ]
                ])
                ->status(HttpRouter::STATUS_CODES[201])
                ->sendJSON();
            return;
        } catch (AuthenticationException $e) {
            $response
                ->appendArray([
                    'message' => $e->getMessage()
                ])
                ->status(HttpRouter::STATUS_CODES[401])
                ->sendJSON();
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
                ->sendJSON();
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
                ->sendJSON();
            return;
        }
    }

    public function update(HttpRequest $request, HttpResponse $response)
    {
        try {
            $headers = $request->getHeaders();
            $token = AuthorizationTokenRetriever::getFromHeaders($headers);
            $this->authService->validateToken($token);

            $body = $request->parseBodyFromJSONString();
            $params = $request->getParams();

            $isGenreIdSetted = isset($params['genreId']);
            if ($isGenreIdSetted === false) {
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

            $genreId = $params['genreId'];
            $name = $body['name'];
            $isActive = $body['isActive'];

            $wasAUpdateOcurred = $this->service->update($genreId, $name, $isActive);
            if ($wasAUpdateOcurred === false) {
                throw new HttpResourceNotFoundException(
                    'O gênero com o id ' . $genreId . ' não foi atualizado! Verifique se o registro realmente existe.'
                );
            }

            $response
                ->appendArray([
                    'message' => 'Gênero atualizado com sucesso!'
                ])
                ->status(HttpRouter::STATUS_CODES[200])
                ->sendJSON();
            return;
        } catch (AuthenticationException $e) {
            $response
                ->appendArray([
                    'message' => $e->getMessage()
                ])
                ->status(HttpRouter::STATUS_CODES[401])
                ->sendJSON();
            return;
        } catch (HttpResourceNotFoundException $e) {
            $response
                ->appendArray([
                    'message' => $e->getMessage()
                ])
                ->status(HttpRouter::STATUS_CODES[404])
                ->sendJSON();
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
                ->sendJSON();
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
                ->sendJSON();
            return;
        }
    }

    public function setIsActive(HttpRequest $request, HttpResponse $response)
    {
        try {
            $headers = $request->getHeaders();
            $token = AuthorizationTokenRetriever::getFromHeaders($headers);
            $this->authService->validateToken($token);

            $params = $request->getParams();
            $body = $request->parseBodyFromJSONString();

            $isGenreIdSetted = isset($params['genreId']);
            if ($isGenreIdSetted === false) {
                throw new ControllerUndefinedValueException('O parâmetro genreId não foi informado na URL!');
            }

            $isIsActiveFieldSetted = isset($body['isActive']);
            if ($isIsActiveFieldSetted === false) {
                throw new ControllerUndefinedValueException('A chave isActive não existe ou seu valor é null!');
            }

            $genreId = $params['genreId'];
            $isActive = $body['isActive'];

            $wasTheUpdateOcurred = $this->service->setIsActive($genreId, $isActive);
            if ($wasTheUpdateOcurred === false) {
                throw new ControllerOperationErrorException(
                    'Ocorreu um erro! Verifique se o id ' .
                    $genreId .
                    ' existe ' .
                    'ou se o valor de atividade foi modificado!'
                );
            }

            $response
                ->appendArray([
                    'message' => 'Estado atualizado com sucesso!'
                ])
                ->status(HttpRouter::STATUS_CODES[200])
                ->sendJSON();
            return;
        } catch (AuthenticationException $e) {
            $response
                ->appendArray([
                    'message' => $e->getMessage()
                ])
                ->status(HttpRouter::STATUS_CODES[401])
                ->sendJSON();
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
                ->sendJSON();
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
                ->sendJSON();
            return;
        }
    }

    public function findById(HttpRequest $request, HttpResponse $response)
    {
        try {
            $headers = $request->getHeaders();
            $token = AuthorizationTokenRetriever::getFromHeaders($headers);
            $this->authService->validateToken($token);

            $params = $request->getParams();

            $isGenreIdSetted = isset($params['genreId']);
            if ($isGenreIdSetted === false) {
                throw new ControllerUndefinedValueException(
                    'O parâmetro genreId não foi informado ou seu valor é null!'
                );
            }

            $genreId = $params['genreId'];

            $genre = $this->service->findById($genreId);

            if ($genre === null) {
                throw new HttpResourceNotFoundException('O gênero com o id ' . $genreId . ' não existe!');
            }

            $response
                ->appendArray([
                    'message' => 'Gênero encontrado com sucesso!',
                    'data' => [
                        'id' => $genre->getId(),
                        'name' => $genre->getName(),
                        'isActive' => $genre->getIsActive()
                    ]
                ])
                ->status(HttpRouter::STATUS_CODES[200])
                ->sendJSON();
            return;
        } catch (AuthenticationException $e) {
            $response
                ->appendArray([
                    'message' => $e->getMessage()
                ])
                ->status(HttpRouter::STATUS_CODES[401])
                ->sendJSON();
            return;
        } catch (HttpResourceNotFoundException $e) {
            $response
                ->appendArray([
                    'message' => $e->getMessage()
                ])
                ->status(HttpRouter::STATUS_CODES[404])
                ->sendJSON();
            return;
        } catch (ControllerUndefinedValueException | EntityInvalidValueException $e) {
            $response
                ->appendArray([
                    'message' => $e->getMessage()
                ])
                ->status(HttpRouter::STATUS_CODES[400])
                ->sendJSON();
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
                ->sendJSON();
            return;
        }
    }

    public function findAll(HttpRequest $request, HttpResponse $response)
    {
        try {
            $headers = $request->getHeaders();
            $token = AuthorizationTokenRetriever::getFromHeaders($headers);
            $this->authService->validateToken($token);

            $genres = $this->service->findAll();

            $numberOfGenresFound = count($genres);
            if ($numberOfGenresFound === 0) {
                throw new HttpResourceNotFoundException('A busca foi concluída e nenhum gênero foi encontrado.');
            }

            foreach ($genres as $genre) {
                $data[] = [
                    'id' => $genre->getId(),
                    'name' => $genre->getName(),
                    'isActive' => $genre->getIsActive()
                ];
            }

            $response
                ->appendArray([
                    'message' => 'Gêneros buscados com sucesso!',
                    'data' => $data
                ])
                ->status(HttpRouter::STATUS_CODES[200])
                ->sendJSON();
            return;
        } catch (AuthenticationException $e) {
            $response
                ->appendArray([
                    'message' => $e->getMessage()
                ])
                ->status(HttpRouter::STATUS_CODES[401])
                ->sendJSON();
            return;
        } catch (HttpResourceNotFoundException $e) {
            $response
                ->appendArray([
                    'message' => $e->getMessage()
                ])
                ->status(HttpRouter::STATUS_CODES[404])
                ->sendJSON();
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
                ->sendJSON();
            return;
        }
    }
}
