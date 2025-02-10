<?php

namespace Mvreisg\GamebaseBackend\Presentation\Controllers;

use Exception;
use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpRequest;
use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpResponse;
use Mvreisg\GamebaseBackend\Application\Services\GenreService;
use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpRouter;
use Mvreisg\GamebaseBackend\Domain\Exceptions\EntityInvalidValueException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseDuplicatedEntryException;
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
        $messages = [];

        $body = $request->parseBodyFromJSONString();
        $name = $body['name'] ?? null;

        $genre = null;
        try {
            $genre = $this->service->insert($name);
        } catch (DatabaseDuplicatedEntryException | EntityInvalidValueException $e) {
            $messages[] = $e->getMessage();
            $response
                ->appendArray([
                    'messages' => $messages
                ])
                ->status(HttpRouter::STATUS_CODES[400])
                ->sendJSON();
            return;
        } catch (PDOException | Exception $e) {
            $messages = $e->getMessage();
            $response
                ->appendArray([
                    'messages' => $messages
                ])
                ->status(HttpRouter::STATUS_CODES[500])
                ->sendJSON();
            return;
        }

        if ($genre == false) {
            $messages[] = 'Ocorreu um erro ao inserir o gênero. Contate o suporte.';
            $response
                ->appendArray([
                    'messages' => $messages
                ])
                ->status(HttpRouter::STATUS_CODES[500])
                ->sendJSON();
            return;
        }

        $messages[] = 'Gênero incluído com sucesso!';
        $response
            ->appendArray([
                'messages' => $messages
            ])
            ->status(HttpRouter::STATUS_CODES[201])
            ->sendJSON();
    }

    /**
     * Method that handles the HTTP request and response of a Genre update.
     * @param HttpRequest $request The HTTP request object.
     * @param HttpResponse $response The HTTP response object.
     * @return void
     */
    public function update(HttpRequest $request, HttpResponse $response)
    {
        $messages = [];

        $body = $request->parseBodyFromJSONString();
        $params = $request->getParams();

        $genreId = $params['genreId'] ?? null;
        $name = $body['name'] ?? null;

        try {
            $genreId = intval($genreId);
            $wasEditAnSuccess = $this->service->update($genreId, $name);
        } catch (EntityInvalidValueException | DatabaseDuplicatedEntryException $e) {
            $messages[] = $e->getMessage();
            $response
                ->appendArray([
                    'messages' => $messages
                ])
                ->status(HttpRouter::STATUS_CODES[400])
                ->sendJSON();
            return;
        } catch (PDOException | Exception $e) {
            $messages[] = $e->getMessage();
            $response
                ->appendArray([
                    'messages' => $messages
                ])
                ->status(HttpRouter::STATUS_CODES[500])
                ->sendJSON();
            return;
        }

        if ($wasEditAnSuccess === false) {
            $messages[] = 'Verifique se o id do gênero existe no banco de dados.';
            $response
                ->appendArray([
                    'messages' => $messages
                ])
                ->status(HttpRouter::STATUS_CODES[400])
                ->sendJSON();
            return;
        }

        $messages[] = 'Gênero editado com sucesso!';
        $response
            ->appendArray([
                'messages' => $messages
            ])
            ->status(HttpRouter::STATUS_CODES[200])
            ->sendJSON();
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
        $genreId = $params['genreId'] ?? null;

        $genre = null;
        try {
            $genreId = intval($genreId);
            $genre = $this->service->findById($genreId);
        } catch (DatabaseDuplicatedEntryException $e) {
            $messages[] = $e->getMessage();
            $response
                ->appendArray([
                    'messages' => $messages
                ])
                ->status(HttpRouter::STATUS_CODES[400])
                ->sendJSON();
            return;
        } catch (PDOException | Exception $e) {
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
            $messages[] = 'O gênero procurado não existe!';
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

        $messages[] = 'Gênero buscado com sucesso!';
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
        } catch (PDOException | Exception $e) {
            $messages[] = $e->getMessage();
            $response
                ->appendArray([
                    'messages' => $messages
                ])
                ->status(HttpRouter::STATUS_CODES[500])
                ->sendJSON();
            return;
        }

        $numberOfGenres = count($genres);
        if ($numberOfGenres === 0) {
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
