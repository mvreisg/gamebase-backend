<?php

namespace Mvreisg\GamebaseBackend\Presentation\Controllers;

use Exception;
use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpRequest;
use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpResponse;
use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpRouter;
use Mvreisg\GamebaseBackend\Application\Services\GamePlatformService;
use Mvreisg\GamebaseBackend\Domain\Exceptions\EntityInvalidValueException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\HttpJsonParseException;
use PDOException;

/**
 * Game Platform controller class.
 */
class GamePlatformController
{
    /**
     * @var GamePlatformService $service The service to be used by this controller.
     */
    private GamePlatformService $service;

    /**
     * Game Platform controller class constructor.
     * @param GamePlatformService $service The service to be used by this controller.
     * @return void
     */
    public function __construct(GamePlatformService $service)
    {
        $this->service = $service;
    }

    /**
     * Method that handles the HTTP request and response of a Game Platform insertion.
     * @param HttpRequest $request The HTTP request object.
     * @param HttpResponse $response The HTTP response object.
     * @return void
     */
    public function insert(HttpRequest $request, HttpResponse $response): void
    {
        $messages = [];

        try {
            $body = $request->parseBodyFromJSONString();

            $gameId = $body['gameId'] ?? null;
            $platformsIds = $body['platformsIds'] ?? null;

            $gameId = intval($gameId);
            $isPlaformsArrayIterable = is_iterable($platformsIds);

            if ($isPlaformsArrayIterable === false) {
                $messages[] = 'O valor de platformsIds não é iterável!';
                $response
                    ->appendArray([
                        'messages' => $messages
                    ])
                    ->status(HttpRouter::STATUS_CODES[400])
                    ->sendJSON();
                return;
            }

            foreach ($platformsIds as $platformId) {
                $platformId = intval($platformId);

                $gamePlatform = $this->service->insert($platformId, $gameId);
                if ($gamePlatform == false) {
                    $messages[] = 'Ocorreu um erro ao inserir o vínculo entre jogo e plataforma. Contate o suporte.';
                    $response
                        ->appendArray([
                            'messages' => $messages
                        ])
                        ->status(HttpRouter::STATUS_CODES[500])
                        ->sendJSON();
                    return;
                }
            }

            $messages[] = 'Vínculo entre jogo e plataforma inserido com sucesso!';
            $response
                ->appendArray([
                    'messages' => $messages
                ])
                ->status(HttpRouter::STATUS_CODES[201])
                ->sendJSON();
            return;
        } catch (HttpJsonParseException | EntityInvalidValueException $e) {
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
    }

    /**
     * Method that handles the HTTP request and response of a Game Platform update.
     * @param HttpRequest $request The HTTP request object.
     * @param HttpResponse $response The HTTP response object.
     * @return void
     */
    public function update(HttpRequest $request, HttpResponse $response): void
    {
        $messages = [];

        try {
            $params = $request->getParams();
            $body = $request->parseBodyFromJSONString();

            $id = $params['id'] ?? null;
            $gameId = $body['gameId'] ?? null;
            $platformsIds = $body['platformsIds'] ?? null;

            $id = intval($id);
            $gameId = intval($gameId);
            $isPlaformsArrayIterable = is_iterable($platformsIds);

            if ($isPlaformsArrayIterable === false) {
                $messages[] = 'O valor de platformsIds não é iterável!';
                $response
                    ->appendArray([
                        'messages' => $messages
                    ])
                    ->status(HttpRouter::STATUS_CODES[400])
                    ->sendJSON();
                return;
            }

            $hasValuesToBeEdited = count($platformsIds);
            if ($hasValuesToBeEdited === 0) {
                $messages[] = 'Não há valores a serem editados!';
                $response
                    ->appendArray([
                        'messages' => $messages
                    ])
                    ->status(HttpRouter::STATUS_CODES[200])
                    ->sendJSON();
                return;
            }

            foreach ($platformsIds as $platformId) {
                $platformId = intval($platformId);
                $this->service->update($id, $platformId, $gameId);
            }

            $messages[] = 'Vínculos entre jogos e plataformas editados com sucesso!';
            $response
                ->appendArray([
                    'messages' => $messages
                ])
                ->status(HttpRouter::STATUS_CODES[200])
                ->sendJSON();
            return;
        } catch (HttpJsonParseException | EntityInvalidValueException $e) {
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
    }

    /**
     * Method that handles the HTTP request and response of a Game Platform deletion.
     * @param HttpRequest $request The HTTP request object.
     * @param HttpResponse $response The HTTP response object.
     * @return void
     */
    public function delete(HttpRequest $request, HttpResponse $response): void
    {
        $params = $request->getParams();
        try {
            $id = $params['id'] ?? null;

            $id = intval($id);

            $wasDeletionSuccessful = $this->service->delete($id);

            if ($wasDeletionSuccessful) {
                $messages[] = 'Vínculo entre jogos e plataformas deletado com sucesso!';
                $response
                    ->appendArray([
                        'messages' => $messages
                    ])
                    ->status(HttpRouter::STATUS_CODES[200])
                    ->sendJSON();
                return;
            }

            $messages[] = 'Ocorreu um erro ao deletar o vínculo entre jogo e plataforma!';
            $response
                ->appendArray([
                    'messages' => $messages
                ])
                ->status(HttpRouter::STATUS_CODES[500])
                ->sendJSON();
            return;
        } catch (HttpJsonParseException | EntityInvalidValueException $e) {
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
    }

    /**
     * Method that handles the HTTP request and response of a Game Platform find by id.
     * @param HttpRequest $request The HTTP request object.
     * @param HttpResponse $response The HTTP response object.
     * @return void
     */
    public function findById(HttpRequest $request, HttpResponse $response): void
    {
        $messages = [];
        $data = [];

        $params = $request->getParams();
        try {
            $id = $params['id'] ?? null;

            $id = intval($id);

            $gamePlatform = $this->service->findById($id);

            if ($gamePlatform == false) {
                $messages[] = 'A busca foi concluída e nenhum valor com o id ' . $id . ' foi encontrado!';
                $response
                    ->appendArray([
                        'messages' => $messages
                    ])
                    ->status(HttpRouter::STATUS_CODES[200])
                    ->sendJSON();
                return;
            }

            $gamePlatformId = $gamePlatform->getId();
            $gamePlatformPlatformId = $gamePlatform->getPlatformId();
            $gamePlatformGameId = $gamePlatform->getGameId();

            $data = [
                'id' => $gamePlatformId,
                'platformId' => $gamePlatformPlatformId,
                'gameId' => $gamePlatformGameId
            ];

            $messages[] = 'Busca realizada com sucesso!';
            $response
                ->appendArray([
                    'messages' => $messages,
                    'data' => $data
                ])
                ->status(HttpRouter::STATUS_CODES[200])
                ->sendJSON();
            return;
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
    }

    /**
     * Method that handles the HTTP request and response of a find of all Game Platforms.
     * @param HttpRequest $request The HTTP request object.
     * @param HttpResponse $response The HTTP response object.
     * @return void
     */
    public function findAll(HttpRequest $request, HttpResponse $response): void
    {
        $messages = [];
        $data = [];
        try {
            $gamePlatforms = $this->service->findAll();

            foreach ($gamePlatforms as $gamePlatform) {
                $id = $gamePlatform->getId();
                $platformId = $gamePlatform->getPlatformId();
                $gameId = $gamePlatform->getGameId();
                $data[] = [
                    'id' => $id,
                    'platformId' => $platformId,
                    'gameId' => $gameId
                ];
            }

            $messages[] = 'Busca realizada com sucesso!';
            $response
                ->appendArray([
                    'messages' => $messages,
                    'data' => $data
                ])
                ->status(HttpRouter::STATUS_CODES[200])
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
    }
}
