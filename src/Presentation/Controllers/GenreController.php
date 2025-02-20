<?php

namespace Mvreisg\GamebaseBackend\Presentation\Controllers;

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
use Mvreisg\GamebaseBackend\Presentation\Exceptions\ControllerOperationErrorException;
use Mvreisg\GamebaseBackend\Presentation\Exceptions\ControllerUndefinedValueException;
use PDOException;

/**
 * Genre controller class.
 */
class GenreController
{
    /**
     * @var GenreService $service The service to be used by this controller.
     */
    private GenreService $service;

    /**
     * Genre controller class constructor.
     * @param GenreService $service The service to be used by this controller.
     * @return void
     */
    public function __construct(GenreService $service)
    {
        $this->service = $service;
    }

    /**
     * Method that handles the HTTP request and response of a Genre insertion.
     * @param HttpRequest $request The HTTP request object.
     * @param HttpResponse $response The HTTP response object.
     * @return void
     */
    public function insert(HttpRequest $request, HttpResponse $response)
    {
        try {
            $body = $request->parseBodyFromJSONString();

            $isNameFieldSetted = isset($body['name']);
            if ($isNameFieldSetted === false) {
                throw new ControllerUndefinedValueException('A chave name não existe ou seu valor é null');
            }

            $name = $body['name'];

            $genre = $this->service->insert($name);

            $response
                ->appendArray([
                    'message' => 'Gênero inserido com sucesso!',
                    'data' => [
                        'id' => $genre->getId(),
                        'name' => $genre->getName()
                    ]
                ])
                ->status(HttpRouter::STATUS_CODES[201])
                ->sendJSON();
            return;
        } catch (ControllerUndefinedValueException | HttpJsonParseException | EntityInvalidValueException | DatabaseDuplicatedEntryException $e) {
            $response
                ->appendArray([
                    'message' => $e->getMessage()
                ])
                ->status(HttpRouter::STATUS_CODES[400])
                ->sendJSON();
            return;
        } catch (DatabaseStatementCreationFailureException | DatabaseStatementExecutionFailureException | DatabaseFetchFailureException | PDOException $e) {
            $response
                ->appendArray([
                    'message' => $e->getMessage()
                ])
                ->status(HttpRouter::STATUS_CODES[500])
                ->sendJSON();
            return;
        }
    }

    /**
     * Method that handles the HTTP request and response of a Genre update.
     * @param HttpRequest $request The HTTP request object.
     * @param HttpResponse $response The HTTP response object.
     * @return void
     */
    public function update(HttpRequest $request, HttpResponse $response)
    {
        try {
            $body = $request->parseBodyFromJSONString();
            $params = $request->getParams();

            $isGenreIdSetted = isset($params['genreId']);
            if ($isGenreIdSetted === false) {
                throw new ControllerUndefinedValueException('O parâmetro genreId não foi informado na URL ou seu valor é null!');
            }

            $isNameFieldSetted = isset($body['name']);
            if ($isNameFieldSetted === false) {
                throw new ControllerUndefinedValueException('A chave name não foi informada ou seu valor é null!');
            }

            $genreId = $params['genreId'];
            $name = $body['name'];

            $wasTheUpdateASuccess = $this->service->update($genreId, $name);

            if ($wasTheUpdateASuccess === false) {
                throw new ControllerOperationErrorException('Ocorreu uma falha na atualização!');
            }

            $response
                ->appendArray([
                    'message' => 'Gênero atualizado com sucesso!'
                ])
                ->status(HttpRouter::STATUS_CODES[200])
                ->sendJSON();
            return;
        } catch (ControllerUndefinedValueException | HttpJsonParseException | EntityInvalidValueException | DatabaseDuplicatedEntryException $e) {
            $response
                ->appendArray([
                    'message' => $e->getMessage()
                ])
                ->status(HttpRouter::STATUS_CODES[400])
                ->sendJSON();
            return;
        } catch (ControllerOperationErrorException | DatabaseStatementCreationFailureException | DatabaseStatementExecutionFailureException | PDOException $e) {
            $response
                ->appendArray([
                    'message' => $e->getMessage()
                ])
                ->status(HttpRouter::STATUS_CODES[500])
                ->sendJSON();
            return;
        }
    }

    /**
     * Method that handles the HTTP request and response of a Genre finding.
     * @param HttpRequest $request The HTTP request object.
     * @param HttpResponse $response The HTTP response object.
     * @return void
     */
    public function findById(HttpRequest $request, HttpResponse $response)
    {
        $messages = [];
        $data = [];

        $params = $request->getParams();

        $isGenreIdSetted = isset($params['genreId']);
        if ($isGenreIdSetted === false) {
            $messages[] = 'O parâmetro genreId não foi informado ou seu valor é null!';
            $response
                ->appendArray([
                    'messages' => $messages
                ])
                ->status(HttpRouter::STATUS_CODES[400])
                ->sendJSON();
            return;
        }

        $genreId = $params['genreId'];
        $genre = null;
        try {
            $genre = $this->service->findById($genreId);
        } catch (EntityInvalidValueException $e) {
            $messages[] = $e->getMessage();
            $response
                ->appendArray([
                    'messages' => $messages
                ])
                ->status(HttpRouter::STATUS_CODES[400])
                ->sendJSON();
            return;
        } catch (DatabaseStatementCreationFailureException | DatabaseStatementExecutionFailureException | PDOException $e) {
            $messages[] = $e->getMessage();
            $response
                ->appendArray([
                    'messages' => $messages
                ])
                ->status(HttpRouter::STATUS_CODES[500])
                ->sendJSON();
            return;
        }

        if ($genre === null) {
            $messages[] = 'O gênero com o id ' . $genreId . ' não existe!';
            $response
                ->appendArray([
                    'messages' => $messages
                ])
                ->status(HttpRouter::STATUS_CODES[400])
                ->sendJSON();
            return;
        }

        $genreId = $genre->getId();
        $genreName = $genre->getName();

        $data = [
            'id' => $genreId,
            'name' => $genreName,
        ];

        $messages[] = 'Gênero encontrado com sucesso!';
        $response
            ->appendArray([
                'messages' => $messages,
                'data' => $data
            ])
            ->status(HttpRouter::STATUS_CODES[200])
            ->sendJSON();
    }

    /**
     * Method that handles the HTTP request and response of a finding of all Genres.
     * @param HttpRequest $request The HTTP request object.
     * @param HttpResponse $response The HTTP response object.
     * @return void
     */
    public function findAll(HttpRequest $request, HttpResponse $response)
    {
        $messages = [];
        $data = [];

        $genres = null;
        try {
            $genres = $this->service->findAll();
        } catch (DatabaseStatementCreationFailureException | DatabaseStatementExecutionFailureException | PDOException $e) {
            $messages[] = $e->getMessage();
            $response
                ->appendArray([
                    'messages' => $messages
                ])
                ->status(HttpRouter::STATUS_CODES[500])
                ->sendJSON();
            return;
        }

        $numberOfGenresFound = count($genres);
        if ($numberOfGenresFound === 0) {
            $messages[] = 'A busca foi concluída e nenhum gênero foi encontrado.';
            $response
                ->appendArray([
                    'messages' => $messages
                ])
                ->status(HttpRouter::STATUS_CODES[200])
                ->sendJSON();
            return;
        }

        foreach ($genres as $genre) {
            $genreId = $genre->getId();
            $genreName = $genre->getName();

            $data[] = [
                'id' => $genreId,
                'name' => $genreName
            ];
        }

        $messages[] = 'Gêneros buscados com sucesso!';
        $response
            ->appendArray([
                'messages' => $messages,
                'data' => $data
            ])
            ->status(HttpRouter::STATUS_CODES[200])
            ->sendJSON();
    }
}
