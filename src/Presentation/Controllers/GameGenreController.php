<?php

namespace Mvreisg\GamebaseBackend\Presentation\Controllers;

use Exception;
use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpRequest;
use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpResponse;
use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpRouter;
use Mvreisg\GamebaseBackend\Application\Services\GameGenreService;
use Mvreisg\GamebaseBackend\Domain\Exceptions\EntityInvalidValueException;
use PDOException;

/**
 * Game Genre controller class.
 */
class GameGenreController
{
    /**
     * @var GameGenreService $service The service to be used by this controller.
     */
    private GameGenreService $service;

    /**
     * Game Genre controller class controller.
     * @param GameGenreService $service The service to be used by this controller.
     */
    public function __construct(GameGenreService $service)
    {
        $this->service = $service;
    }

    /**
     * Method that handles the HTTP request and response of a Game Genre insertion.
     * @param HttpRequest $request The HTTP request object.
     * @param HttpResponse $response The HTTP response object.
     * @return void
     */
    public function insert(HttpRequest $request, HttpResponse $response)
    {
        $messages = [];
        $data = [];

        $body = $request->parseBodyFromJSONString();

        $gameId = $body['gameId'] ?? null;
        $genresIds = $body['genresIds'] ?? [];

        if ($genresIds == false) {
            $messages[] = 'Os ids de gêneros não foram informados.';
            $response
                ->appendArray([
                    'messages' => $messages
                ])
                ->status(HttpRouter::STATUS_CODES[400])
                ->sendJSON();
            return;
        }

        try {
            foreach ($genresIds as $genreId) {
                $gameId = intval($gameId);
                $genreId = intval($genreId);
                $gameGenre = $this->service->insert($genreId, $gameId);
                $data[] = [
                    'id' => $gameGenre->getId(),
                    'gameId' => $gameGenre->getGameId(),
                    'genreId' => $gameGenre->getGenreId()
                ];

                if ($gameGenre == false) {
                    $messages[] = 'Ocorreu um erro ao inserir o vínculo entre jogo e gênero. Contate o suporte.';
                    $response
                        ->appendArray([
                            'messages' => $messages
                        ])
                        ->status(HttpRouter::STATUS_CODES[500])
                        ->sendJSON();
                    return;
                }
            }
        } catch (EntityInvalidValueException $e) {
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

        $messages[] = 'Vínculo entre jogo e gênero inserido com sucesso!';
        $response
            ->appendArray([
                'messages' => $messages,
                'data' => $data
            ])
            ->status(HttpRouter::STATUS_CODES[201])
            ->sendJSON();
    }

    /**
     * Method that handles the HTTP request and response of a Game Genre update.
     * @param HttpRequest $request The HTTP request object.
     * @param HttpResponse $response The HTTP response object.
     * @return void
     */
    public function update(HttpRequest $request, HttpResponse $response)
    {
        $messages = [];

        $body = $request->parseBodyFromJSONString();
        $params = $request->getParams();

        $id = $params['id'] ?? null;
        $gameId = $body['gameId'] ?? null;
        $genresIds = $body['genresIds'] ?? null;

        if ($genresIds == false) {
            $messages[] = 'Os ids de gêneros não foram informados.';
            $response
                ->appendArray([
                    'messages' => $messages
                ])
                ->status(HttpRouter::STATUS_CODES[400])
                ->sendJSON();
            return;
        }

        try {
            $id = intval($id);
            $gameId = intval($gameId);

            foreach ($genresIds as $genreId) {
                $genreId = intval($genreId);

                $wasItSuccessful = $this->service->update($id, $genreId, $gameId);

                if ($wasItSuccessful === false) {
                    $messages[] = 'Ocorreu um erro ao tentar atualizar!';
                    $response
                        ->appendArray([
                            'messages' => $messages
                        ])
                        ->status(HttpRouter::STATUS_CODES[500])
                        ->sendJSON();
                    return;
                }
            }
        } catch (EntityInvalidValueException $e) {
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

        $messages[] = 'Vínculos entre jogos e gêneros editado com sucesso!';
        $response
            ->appendArray([
                'messages' => $messages
            ])
            ->status(HttpRouter::STATUS_CODES[200])
            ->sendJSON();
    }

    /**
     * Method that handles the HTTP request and response of a Game Genre deletion.
     * @param HttpRequest $request The HTTP request object.
     * @param HttpResponse $response The HTTP response object.
     * @return void
     */
    public function delete(HttpRequest $request, HttpResponse $response)
    {
        $messages = [];

        $params = $request->getParams();

        $id = $params['id'] ?? null;

        try {
            $id = intval($id);

            $wasItSuccessful = $this->service->delete($id);

            if ($wasItSuccessful === false) {
                $messages[] = 'Ocorreu um erro ao tentar deletar!';
                $response
                    ->appendArray([
                        'messages' => $messages
                    ])
                    ->status(HttpRouter::STATUS_CODES[500])
                    ->sendJSON();
                return;
            }
        } catch (EntityInvalidValueException $e) {
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

        $messages[] = 'Vínculos entre jogos e gêneros deletado com sucesso!';
        $response
            ->appendArray([
                'messages' => $messages
            ])
            ->status(HttpRouter::STATUS_CODES[200])
            ->sendJSON();
    }

    /**
     * Method that handles the HTTP request and response of a Game Genre search by the id.
     * @param HttpRequest $request The HTTP request object.
     * @param HttpResponse $response The HTTP response object.
     * @return void
     */
    public function findById(HttpRequest $request, HttpResponse $response)
    {
        $messages = [];

        $params = $request->getParams();

        $id = $params['id'] ?? null;

        $gameGenre = null;
        try {
            $id = intval($id);
            $gameGenre = $this->service->findById($id);
        } catch (EntityInvalidValueException $e) {
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

        if ($gameGenre === null) {
            $messages[] = 'O vínculo entre gênero e jogo procurado não existe!';
            $response
                ->appendArray([
                    'messages' => $messages
                ])
                ->status(HttpRouter::STATUS_CODES[400])
                ->sendJSON();
            return;
        }

        $gameGenreId = $gameGenre->getId();
        $gameGenreGameId = $gameGenre->getGameId();
        $gameGenreGenreId = $gameGenre->getGenreId();

        $data = [
            'id' => $gameGenreId,
            'gameId' => $gameGenreGameId,
            'genreId' => $gameGenreGenreId
        ];

        $messages[] = 'Vínculo entre gênero e jogo encontrado com sucesso!';
        $response
            ->appendArray([
                'messages' => $messages,
                'data' => $data
            ])
            ->status(HttpRouter::STATUS_CODES[200])
            ->sendJSON();
        return;
    }

    /**
     * Method that handles the HTTP request and response of a search of all Game Genres.
     * @param HttpRequest $request The HTTP request object.
     * @param HttpResponse $response The HTTP response object.
     * @return void
     */
    public function findAll(HttpRequest $request, HttpResponse $response)
    {
        $messages = [];
        $data = [];

        $result = null;
        try {
            $result = $this->service->findAll();
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

        if ($result === null) {
            $messages[] = 'Os vínculos entre gêneros e jogos procurados não existem!';
            $response
                ->appendArray([
                    'messages' => $messages
                ])
                ->status(HttpRouter::STATUS_CODES[400])
                ->sendJSON();
            return;
        }

        foreach ($result as $gameGenre) {
            $gameGenreId = $gameGenre->getId();
            $gameGenreGameId = $gameGenre->getGameId();
            $gameGenreGenreId = $gameGenre->getGenreId();

            $data[] = [
                'id' => $gameGenreId,
                'gameId' => $gameGenreGameId,
                'genreId' => $gameGenreGenreId
            ];
        }

        $messages[] = 'Vínculo entre gênero e jogo encontrado com sucesso!';
        $response
            ->appendArray([
                'messages' => $messages,
                'data' => $data
            ])
            ->status(HttpRouter::STATUS_CODES[200])
            ->sendJSON();
        return;
    }
}
