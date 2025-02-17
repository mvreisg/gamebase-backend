<?php

namespace Mvreisg\GamebaseBackend\Presentation\Controllers;

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
use Mvreisg\GamebaseBackend\Presentation\Exceptions\ControllerOperationErrorException;
use Mvreisg\GamebaseBackend\Presentation\Exceptions\ControllerUndefinedValueException;
use PDOException;

/**
 * Platform controller class.
 */
class PlatformController
{
    /**
     * @var PlatformService $service The service to be user by this controller.
     */
    private PlatformService $service;

    /**
     * Platform controller class constructor.
     * @param PlatformService $service The service to be user by this controller.
     * @return void
     */
    public function __construct(PlatformService $service)
    {
        $this->service = $service;
    }

    /**
     * Method that handles the HTTP request and response of a Platform insertion.
     * @param HttpRequest $request The HTTP request object.
     * @param HttpResponse $response The HTTP response object.
     */
    public function insert(HttpRequest $request, HttpResponse $response)
    {
        $messages = [];
        $data = [];
        $platform = null;
        try {
            $body = $request->parseBodyFromJSONString();
            $isNameSetted = isset($body['name']);
            if ($isNameSetted === false) {
                throw new ControllerUndefinedValueException('A chave name não foi definida no JSON ou seu valor é null!');
            }

            $name = $body['name'];
            $platform = $this->service->insert($name);
        } catch (ControllerUndefinedValueException | HttpJsonParseException | DatabaseDuplicatedEntryException | EntityInvalidValueException $e) {
            $messages[] = $e->getMessage();
            $response
                ->appendArray([
                    'messages' => $messages
                ])
                ->status(HttpRouter::STATUS_CODES[400])
                ->sendJSON();
            return;
        } catch (DatabaseTransactionCreationFailureException | DatabaseStatementCreationFailureException | DatabaseStatementExecutionFailureException | DatabaseFetchFailureException | PDOException $e) {
            $messages[] = $e->getMessage();
            $response
                ->appendArray([
                    'messages' => $messages
                ])
                ->status(HttpRouter::STATUS_CODES[500])
                ->sendJSON();
            return;
        }

        $data = [
            'id' => $platform->getId(),
            'name' => $platform->getName()
        ];

        $messages[] = 'Plataforma incluída com sucesso!';
        $response
            ->appendArray([
                'messages' => $messages,
                'data' => $data
            ])
            ->status(HttpRouter::STATUS_CODES[201])
            ->sendJSON();
    }

    /**
     * Method that handles the HTTP request and response of a Platform update.
     * @param HttpRequest $request The HTTP request object.
     * @param HttpResponse $response The HTTP response object.
     */
    public function update(HttpRequest $request, HttpResponse $response)
    {
        $messages = [];
        $wasTheUpdateSuccessful = false;
        try {
            $params = $request->getParams();
            $body = $request->parseBodyFromJSONString();

            $isPlatformIdSetted = isset($params['platformId']);
            if ($isPlatformIdSetted === false) {
                $messages[] = 'O parâmetro platformId não foi informado na URL ou seu valor é null!';
            }

            $isNameSetted = isset($body['name']);
            if ($isNameSetted === false) {
                $messages[] = 'A chave name não foi definida no JSON ou seu valor é null!';
            }

            $itHaveUndefinedKeys = $isPlatformIdSetted === false || $isNameSetted === false;
            if ($itHaveUndefinedKeys) {
                throw new ControllerUndefinedValueException('Ocorreu um erro!');
            }

            $platformId = $params['platformId'];
            $name = $body['name'];

            $wasTheUpdateSuccessful = $this->service->update($platformId, $name);
            if ($wasTheUpdateSuccessful === false) {
                throw new ControllerOperationErrorException('Não foi possível atualizar a plataforma com o id .' . $platformId . '!');
            }
        } catch (ControllerOperationErrorException | ControllerUndefinedValueException | HttpJsonParseException | DatabaseDuplicatedEntryException | EntityInvalidValueException $e) {
            $messages[] = $e->getMessage();
            $response
                ->appendArray([
                    'messages' => $messages
                ])
                ->status(HttpRouter::STATUS_CODES[400])
                ->sendJSON();
            return;
        } catch (DatabaseStatementCreationFailureException | PDOException $e) {
            $messages[] = $e->getMessage();
            $response
                ->appendArray([
                    'messages' => $messages
                ])
                ->status(HttpRouter::STATUS_CODES[500])
                ->sendJSON();
            return;
        }

        $messages[] = 'Plataforma atualizada com sucesso!';
        $response
            ->appendArray([
                'messages' => $messages
            ])
            ->status(HttpRouter::STATUS_CODES[200])
            ->sendJSON();
    }

    /**
     * Method that handles the HTTP request and response of a Platform search by its id.
     * @param HttpRequest $request The HTTP request object.
     * @param HttpResponse $response The HTTP response object.
     */
    public function findById(HttpRequest $request, HttpResponse $response)
    {
        $messages = [];
        $data = [];
        $platform = null;

        try {
            $params = $request->getParams();
            $isPlatformIdSetted = isset($params['platformId']);
            if ($isPlatformIdSetted === false) {
                throw new ControllerUndefinedValueException('O parâmetro platformId não está definido no JSON ou seu valor é null!');
            }

            $platformId = $params['platformId'];
            $platform = $this->service->findById($platformId);
        } catch (ControllerUndefinedValueException | EntityInvalidValueException $e) {
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

        if ($platform === null) {
            $messages[] = 'A plataforma procurada não foi encontrada!';
            $response
                ->appendArray([
                    'messages' => $messages
                ])
                ->status(HttpRouter::STATUS_CODES[404])
                ->sendJSON();
            return;
        }

        $platformId = $platform->getId();
        $platformName = $platform->getName();

        $data = [
            'id' => $platformId,
            'name' => $platformName,
        ];

        $messages[] = 'Plataforma encontrada com sucesso!';
        $response
            ->appendArray([
                'messages' => $messages,
                'data' => $data
            ])
            ->status(HttpRouter::STATUS_CODES[200])
            ->sendJSON();
    }

    /**
     * Method that handles the HTTP request and response of a retrieval of all Platform.
     * @param HttpRequest $request The HTTP request object.
     * @param HttpResponse $response The HTTP response object.
     */
    public function findAll(HttpRequest $request, HttpResponse $response)
    {
        $messages = [];
        $data = [];

        $platforms = null;
        try {
            $platforms = $this->service->findAll();
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

        $numberOfPlatforms = count($platforms);
        if ($numberOfPlatforms === 0) {
            $messages[] = 'A busca foi concluída e nenhuma plataforma foi encontrada.';
            $response
                ->appendArray([
                    'messages' => $messages
                ])
                ->status(HttpRouter::STATUS_CODES[200])
                ->sendJSON();
            return;
        }

        foreach ($platforms as $platform) {
            $platformId = $platform->getId();
            $platformName = $platform->getName();

            $data[] = [
                'id' => $platformId,
                'name' => $platformName,
            ];
        }

        $messages[] = 'Plataformas buscadas com sucesso!';
        $response
            ->appendArray([
                'messages' => $messages,
                'data' => $data
            ])
            ->status(HttpRouter::STATUS_CODES[200])
            ->sendJSON();
    }
}
