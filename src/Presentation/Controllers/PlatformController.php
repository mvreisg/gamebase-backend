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
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\HttpResourceNotFoundException;
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
        try {
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

            $platform = $this->service->insert($name, $isActive);

            $response
                ->appendArray([
                    'message' => 'Plataforma incluída com sucesso!',
                    'data' => [
                        'id' => $platform->getId(),
                        'name' => $platform->getName(),
                        'isActive' => $platform->getIsActive()
                    ]
                ])
                ->status(HttpRouter::STATUS_CODES[201])
                ->sendJSON();
            return;
        } catch (
            ControllerUndefinedValueException |
            HttpJsonParseException |
            DatabaseDuplicatedEntryException |
            EntityInvalidValueException $e
        ) {
            $messages[] = $e->getMessage();
            $response
                ->appendArray([
                    'messages' => $messages
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
     * Method that handles the HTTP request and response of a Platform update.
     * @param HttpRequest $request The HTTP request object.
     * @param HttpResponse $response The HTTP response object.
     */
    public function update(HttpRequest $request, HttpResponse $response)
    {
        try {
            $params = $request->getParams();
            $body = $request->parseBodyFromJSONString();

            $isPlatformIdSetted = isset($params['platformId']);
            if ($isPlatformIdSetted === false) {
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

            $platformId = $params['platformId'];
            $name = $body['name'];
            $isActive = $body['isActive'];

            $wasTheUpdateSuccessful = $this->service->update($platformId, $name, $isActive);
            if ($wasTheUpdateSuccessful === false) {
                throw new HttpResourceNotFoundException(
                    'Não foi possível atualizar a plataforma com o id ' . $platformId . '!'
                );
            }

            $response
                ->appendArray([
                    'message' => 'Plataforma atualizada com sucesso!'
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
            ControllerOperationErrorException |
            ControllerUndefinedValueException |
            HttpJsonParseException |
            DatabaseDuplicatedEntryException |
            EntityInvalidValueException $e
        ) {
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

    public function setIsActive(HttpRequest $request, HttpResponse $response)
    {
        try {
            $params = $request->getParams();
            $body = $request->parseBodyFromJSONString();

            $isPlatformIdSetted = isset($params['platformId']);
            if ($isPlatformIdSetted === false) {
                throw new ControllerUndefinedValueException('O parâmetro platformId não foi informado na URL!');
            }

            $isIsActiveFieldSetted = isset($body['isActive']);
            if ($isIsActiveFieldSetted === false) {
                throw new ControllerUndefinedValueException('A chave isActive não existe ou seu valor é null!');
            }

            $platformId = $params['platformId'];
            $isActive = $body['isActive'];

            $wasItSuccessful = $this->service->setIsActive($platformId, $isActive);
            if ($wasItSuccessful === false) {
                throw new ControllerOperationErrorException(
                    'Ocorreu um erro ao alterar o estado de ativo da plataforma!'
                );
            }

            $response
                ->appendArray([
                    'message' => 'Estado atualizado com sucesso!'
                ])
                ->status(HttpRouter::STATUS_CODES[200])
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

    /**
     * Method that handles the HTTP request and response of a Platform search by its id.
     * @param HttpRequest $request The HTTP request object.
     * @param HttpResponse $response The HTTP response object.
     */
    public function findById(HttpRequest $request, HttpResponse $response)
    {
        try {
            $params = $request->getParams();

            $isPlatformIdSetted = isset($params['platformId']);
            if ($isPlatformIdSetted === false) {
                throw new ControllerUndefinedValueException(
                    'O parâmetro platformId não está definido no JSON ou seu valor é null!'
                );
            }

            $platformId = $params['platformId'];

            $platform = $this->service->findById($platformId);
            if ($platform === null) {
                throw new HttpResourceNotFoundException(
                    'A plataforma procurada não foi encontrada!'
                );
            }

            $response
                ->appendArray([
                    'message' => 'Plataforma encontrada com sucesso!',
                    'data' => [
                        'id' => $platform->getId(),
                        'name' => $platform->getName(),
                        'isActive' => $platform->getIsActive()
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
     * Method that handles the HTTP request and response of a retrieval of all Platform.
     * @param HttpRequest $request The HTTP request object.
     * @param HttpResponse $response The HTTP response object.
     */
    public function findAll(HttpRequest $request, HttpResponse $response)
    {
        try {
            $platforms = $this->service->findAll();

            $numberOfPlatforms = count($platforms);
            if ($numberOfPlatforms === 0) {
                throw new HttpResourceNotFoundException(
                    'A busca foi concluída e nenhuma plataforma foi encontrada.'
                );
            }

            foreach ($platforms as $platform) {
                $data[] = [
                    'id' => $platform->getId(),
                    'name' => $platform->getName(),
                    'isActive' => $platform->getIsActive()
                ];
            }

            $response
                ->appendArray([
                    'message' => 'Plataformas buscadas com sucesso!',
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
