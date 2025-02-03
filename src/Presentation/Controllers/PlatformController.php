<?php

namespace Mvreisg\GamebaseBackend\Presentation\Controllers;

use Exception;
use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpRequest;
use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpResponse;
use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpApplication;
use Mvreisg\GamebaseBackend\Application\Services\PlatformService;
use Mvreisg\GamebaseBackend\Domain\Exceptions\EntityInvalidValueException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseDuplicatedEntryException;
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

        $body = $request->parseBodyFromJSONString();
        $name = $body['name'] ?? null;

        $platform = null;
        try {
            $platform = $this->service->insert($name);
        } catch (EntityInvalidValueException | DatabaseDuplicatedEntryException $e) {
            $messages[] = $e->getMessage();
            $response
                ->appendArray([
                    'messages' => $messages
                ])
                ->status(HttpApplication::STATUS_CODES[400])
                ->sendJSON();
            return;
        } catch (PDOException | Exception $e) {
            $messages[] = $e->getMessage();
            $response
                ->appendArray([
                    'messages' => $messages
                ])
                ->status(HttpApplication::STATUS_CODES[500])
                ->sendJSON();
            return;
        }

        if ($platform == false) {
            $messages[] = 'Ocorreu um erro ao inserir a plataforma. Contate o suporte.';
            $response
                ->appendArray([
                    'messages' => $messages
                ])
                ->status(HttpApplication::STATUS_CODES[500])
                ->sendJSON();
            return;
        }

        $messages[] = 'Plataforma incluída com sucesso!';
        $response
            ->appendArray([
                'messages' => $messages
            ])
            ->status(HttpApplication::STATUS_CODES[201])
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

        $body = $request->parseBodyFromJSONString();
        $params = $request->getParams();

        $platformId = $params['platformId'] ?? null;
        $name = $body['name'] ?? null;

        $wasEditAnSuccess = false;
        try {
            $platformId = intval($platformId);
            $wasEditAnSuccess = $this->service->update($platformId, $name);
        } catch (EntityInvalidValueException | DatabaseDuplicatedEntryException $e) {
            $messages[] = $e->getMessage();
            $response
                ->appendArray([
                    'messages' => $messages
                ])
                ->status(HttpApplication::STATUS_CODES[400])
                ->sendJSON();
            return;
        } catch (PDOException | Exception $e) {
            $messages[] = $e->getMessage();
            $response
                ->appendArray([
                    'messages' => $messages
                ])
                ->status(HttpApplication::STATUS_CODES[500])
                ->sendJSON();
            return;
        }

        if ($wasEditAnSuccess === false) {
            $messages[] = 'Verifique se o id da plataforma existe no banco de dados.';
            $response
                ->appendArray([
                    'messages' => $messages
                ])
                ->status(HttpApplication::STATUS_CODES[400])
                ->sendJSON();
            return;
        }

        $messages[] = 'Plataforma editada com sucesso!';
        $response
            ->appendArray([
                'messages' => $messages
            ])
            ->status(HttpApplication::STATUS_CODES[200])
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

        $params = $request->getParams();
        $platformId = $params['platformId'] ?? null;

        $platform = null;
        try {
            $platformId = intval($platformId);
            $platform = $this->service->findById($platformId);
        } catch (PDOException | Exception $e) {
            $messages[] = $e->getMessage();
            $response
                ->appendArray([
                    'messages' => $messages
                ])
                ->status(HttpApplication::STATUS_CODES[500])
                ->sendJSON();
            return;
        }

        if ($platform == null) {
            $messages[] = 'A plataforma procurada não existe!';
            $response
                ->appendArray([
                    'messages' => $messages
                ])
                ->status(HttpApplication::STATUS_CODES[404])
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
            ->status(HttpApplication::STATUS_CODES[200])
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
        } catch (PDOException | Exception $e) {
            $messages[] = $e->getMessage();
            $response
                ->appendArray([
                    'messages' => $messages
                ])
                ->status(HttpApplication::STATUS_CODES[500])
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
                ->status(HttpApplication::STATUS_CODES[200])
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
            ->status(HttpApplication::STATUS_CODES[200])
            ->sendJSON();
    }
}
