<?php

namespace Mvreisg\GamebaseBackend\Presentation\Controllers;

use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpRequest;
use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpResponse;
use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpRouter;
use Mvreisg\GamebaseBackend\Application\Services\GamePlatformService;
use Mvreisg\GamebaseBackend\Domain\Exceptions\EntityInvalidValueException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseFetchFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseStatementCreationFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseStatementExecutionFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseTransactionCreationFailureException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\HttpJsonParseException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\HttpResourceNotFoundException;
use Mvreisg\GamebaseBackend\Presentation\Exceptions\ControllerInvalidValueException;
use Mvreisg\GamebaseBackend\Presentation\Exceptions\ControllerOperationErrorException;
use Mvreisg\GamebaseBackend\Presentation\Exceptions\ControllerUndefinedValueException;
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
        try {
            $body = $request->parseBodyFromJSONString();

            $isGameIdSetted = isset($body['gameId']);
            if ($isGameIdSetted === false) {
                throw new ControllerUndefinedValueException('A chave gameId não existe ou seu valor é null!');
            }

            $isPlatformIdSetted = isset($body['platformsIds']);
            if ($isPlatformIdSetted === false) {
                throw new ControllerUndefinedValueException('A chave platformsIds não existe ou seu valor é null!');
            }

            $gameId = $body['gameId'];
            $platformsIds = $body['platformsIds'];

            $isPlatformsIdsArrayIterable = is_iterable($platformsIds);
            if ($isPlatformsIdsArrayIterable === false) {
                throw new ControllerInvalidValueException('O valor de platformsIds não é um array!');
            }

            $numberOfPlatformsIds = count($platformsIds);
            if ($numberOfPlatformsIds === 0) {
                throw new ControllerInvalidValueException('O array platformsIds está vazio!');
            }

            foreach ($platformsIds as $platformId) {
                $gamePlatform = $this->service->insert($platformId, $gameId);

                $data[] = [
                    'id' => $gamePlatform->getId(),
                    'platformId' => $gamePlatform->getPlatformId(),
                    'gameId' => $gamePlatform->getGameId()
                ];
            }

            $response
                ->appendArray([
                    'message' => 'Vínculo entre jogo e plataforma inserido com sucesso!',
                    'data' => $data
                ])
                ->status(HttpRouter::STATUS_CODES[201])
                ->sendJSON();
            return;
        } catch (
            HttpJsonParseException |
            ControllerUndefinedValueException |
            ControllerInvalidValueException |
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
            DatabaseTransactionCreationFailureException |
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

    /**
     * Method that handles the HTTP request and response of a Game Platform update.
     * @param HttpRequest $request The HTTP request object.
     * @param HttpResponse $response The HTTP response object.
     * @return void
     */
    public function update(HttpRequest $request, HttpResponse $response): void
    {
        try {
            $params = $request->getParams();
            $body = $request->parseBodyFromJSONString();

            $isIdSetted = isset($params['id']);
            if ($isIdSetted === false) {
                throw new ControllerUndefinedValueException('O parâmetro id não foi informado ou seu valor é nulo!');
            }

            $isGameIdSetted = isset($body['gameId']);
            if ($isGameIdSetted === false) {
                throw new ControllerUndefinedValueException('A chave gameId não existe ou seu valor é null!');
            }

            $isPlatformsIdsSetted = isset($body['platformsIds']);
            if ($isPlatformsIdsSetted === false) {
                throw new ControllerUndefinedValueException('A chave platformsIds não existe ou seu valor é null!');
            }

            $id = $params['id'];
            $gameId = $body['gameId'];
            $platformsIds = $body['platformsIds'];

            $isPlaformsArrayIterable = is_iterable($platformsIds);
            if ($isPlaformsArrayIterable === false) {
                throw new ControllerInvalidValueException('O valor de platformsIds não é um array!');
            }

            $numberOfPlatformsIds = count($platformsIds);
            if ($numberOfPlatformsIds === 0) {
                throw new ControllerInvalidValueException('O valor de platformsIds é um array vazio!');
            }

            foreach ($platformsIds as $platformId) {
                $wasTheUpdateSuccessful = $this->service->update($id, $platformId, $gameId);
                if ($wasTheUpdateSuccessful === false) {
                    throw new HttpResourceNotFoundException('A atualização não aconteceu. Verifique se o id é válido.');
                }
            }

            $response
                ->appendArray([
                    'message' => 'Vínculos entre jogos e plataformas editados com sucesso!'
                ])
                ->status(HttpRouter::STATUS_CODES[200])
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
            ControllerInvalidValueException |
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

    /**
     * Method that handles the HTTP request and response of a Game Platform deletion.
     * @param HttpRequest $request The HTTP request object.
     * @param HttpResponse $response The HTTP response object.
     * @return void
     */
    public function delete(HttpRequest $request, HttpResponse $response): void
    {
        try {
            $params = $request->getParams();

            $isIdSetted = isset($params['id']);
            if ($isIdSetted === false) {
                throw new ControllerUndefinedValueException('O parâmetro id não foi informado ou é null!');
            }

            $id = $params['id'];

            $wasDeletionSuccessful = $this->service->delete($id);
            if ($wasDeletionSuccessful === false) {
                throw new HttpResourceNotFoundException('O registro com o id ' . $id . ' não foi encontrado!');
            }

            $response
                ->appendArray([
                    'message' => 'Vínculo entre jogos e plataformas deletado com sucesso!'
                ])
                ->status(HttpRouter::STATUS_CODES[200])
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
        } catch (ControllerUndefinedValueException | HttpJsonParseException | EntityInvalidValueException $e) {
            $response
                ->appendArray([
                    'message' => $e->getMessage()
                ])
                ->status(HttpRouter::STATUS_CODES[400])
                ->sendJSON();
            return;
        } catch (DatabaseStatementCreationFailureException | PDOException $e) {
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
     * Method that handles the HTTP request and response of a Game Platform find by id.
     * @param HttpRequest $request The HTTP request object.
     * @param HttpResponse $response The HTTP response object.
     * @return void
     */
    public function findById(HttpRequest $request, HttpResponse $response): void
    {
        try {
            $params = $request->getParams();

            $isIdSetted = isset($params['id']);
            if ($isIdSetted === false) {
                throw new ControllerUndefinedValueException('O parâmetro id não foi informado ou seu valor é null!');
            }

            $id = $params['id'];

            $gamePlatform = $this->service->findById($id);
            if ($gamePlatform === null) {
                throw new HttpResourceNotFoundException(
                    'A busca foi concluída e nenhum valor com o id ' . $id . ' foi encontrado!'
                );
            }

            $response
                ->appendArray([
                    'message' => 'Busca realizada com sucesso!',
                    'data' => [
                        'id' => $gamePlatform->getId(),
                        'platformId' => $gamePlatform->getPlatformId(),
                        'gameId' => $gamePlatform->getGameId()
                    ]
                ])
                ->status(HttpRouter::STATUS_CODES[200])
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

    /**
     * Method that handles the HTTP request and response of a find of all Game Platforms.
     * @param HttpRequest $request The HTTP request object.
     * @param HttpResponse $response The HTTP response object.
     * @return void
     */
    public function findAll(HttpRequest $request, HttpResponse $response): void
    {
        try {
            $gamePlatforms = $this->service->findAll();

            $numberOfGamePlatforms = count($gamePlatforms);
            if ($numberOfGamePlatforms === 0) {
                throw new HttpResourceNotFoundException(
                    'A busca foi realizada com sucesso mas nenhum valor foi encontrado!'
                );
            }

            foreach ($gamePlatforms as $gamePlatform) {
                $data[] = [
                    'id' => $gamePlatform->getId(),
                    'platformId' => $gamePlatform->getPlatformId(),
                    'gameId' => $gamePlatform->getGameId()
                ];
            }

            $response
                ->appendArray([
                    'message' => 'Busca realizada com sucesso!',
                    'data' => $data
                ])
                ->status(HttpRouter::STATUS_CODES[200])
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
