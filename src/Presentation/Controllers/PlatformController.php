<?php

namespace Mvreisg\GamebaseBackend\Presentation\Controllers;

use Exception;
use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpRequest;
use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpResponse;
use Mvreisg\GamebaseBackend\Infrastructure\Http\HttpApplication;
use Mvreisg\GamebaseBackend\Application\Services\PlatformService;
use Mvreisg\GamebaseBackend\Domain\Exceptions\EntityInvalidValueException;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseDuplicatedEntryException;

class PlatformController
{
    private PlatformService $service;

    public function __construct(PlatformService $service)
    {
        $this->service = $service;
    }

    public function insert(HttpRequest $request, HttpResponse $response)
    {
        $messages = [];

        $body = $request->parseBodyFromJSONString();

        $name = $body['name'] ?? null;

        if ($name === null) {
            $messages[] = "O parâmetro 'name' não foi informado no JSON.";
            $response->appendArray(['messages' => $messages])->status(HttpApplication::STATUS_CODES[400])->sendJSON();
            return;
        }

        $platform = null;
        try {
            $platform = $this->service->insert($name);
        } catch (EntityInvalidValueException | DatabaseDuplicatedEntryException $e) {
            $messages[] = $e->getMessage();
            $response->appendArray(['messages' => $messages])->status(HttpApplication::STATUS_CODES[400])->sendJSON();
            return;
        } catch (Exception $e) {
            $messages[] = $e->getMessage();
            $response->appendArray(['messages' => $messages])->status(HttpApplication::STATUS_CODES[500])->sendJSON();
            return;
        }

        if ($platform == false) {
            $messages[] = 'Ocorreu um erro ao inserir a plataforma. Contate o suporte.';
            $response->appendArray(['messages' => $messages])->status(HttpApplication::STATUS_CODES[500])->sendJSON();
            return;
        }

        $messages[] = 'Plataforma incluída com sucesso!';
        $response->appendArray(['messages' => $messages])->status(HttpApplication::STATUS_CODES[201])->sendJSON();
    }

    public function edit(HttpRequest $request, HttpResponse $response)
    {
        $messages = [];

        $body = $request->parseBodyFromJSONString();
        $params = $request->getParams();

        $platformId = $params['platformId'] ?? null;
        $name = $body['name'] ?? null;

        $hasParameterError = false;
        if ($platformId === null) {
            $hasParameterError = true;
            $messages[] = 'O id da plataforma não foi informado na URL.';
        }

        if ($name === null) {
            $hasParameterError = true;
            $messages[] = "O parâmetro 'name' não foi informado no JSON.";
        }

        if ($hasParameterError) {
            $response->appendArray(['messages' => $messages])->status(HttpApplication::STATUS_CODES[400])->sendJSON();
            return;
        }

        $isPlatformIdNumeric = is_numeric($platformId);
        if ($isPlatformIdNumeric === false) {
            $messages[] = "O parâmetro 'platformId' informado precisa ser um número inteiro.";
            $response->appendArray(['messages' => $messages])->status(HttpApplication::STATUS_CODES[400])->sendJSON();
            return;
        }

        $platformId = intval($platformId);
        if ($platformId <= 0) {
            $messages[] = "O parâmetro 'platformId' informado precisa ser un número inteiro maior que zero.";
            $response->appendArray(['messages' => $messages])->status(HttpApplication::STATUS_CODES[400])->sendJSON();
            return;
        }

        $wasEditAnSuccess = false;
        try {
            $wasEditAnSuccess = $this->service->edit($platformId, $name);
        } catch (EntityInvalidValueException | DatabaseDuplicatedEntryException $e) {
            $messages[] = $e->getMessage();
            $response->appendArray(['messages' => $messages])->status(HttpApplication::STATUS_CODES[400])->sendJSON();
            return;
        } catch (Exception $e) {
            $messages[] = $e->getMessage();
            $response->appendArray(['messages' => $messages])->status(HttpApplication::STATUS_CODES[500])->sendJSON();
            return;
        }

        if ($wasEditAnSuccess === false) {
            $messages[] = 'Verifique se o id da plataforma existe no banco de dados.';
            $response->appendArray(['messages' => $messages])->status(HttpApplication::STATUS_CODES[400])->sendJSON();
            return;
        }

        $messages[] = 'Plataforma editada com sucesso!';
        $response->appendArray(['messages' => $messages])->status(HttpApplication::STATUS_CODES[200])->sendJSON();
    }

    public function findById(HttpRequest $request, HttpResponse $response)
    {
        $messages = [];
        $data = [];

        $params = $request->getParams();
        $platformId = $params['platformId'] ?? null;

        if ($platformId === null) {
            $messages[] = 'O id da plataforma não foi informado na URL.';
            $response->appendArray(['messages' => $messages])->status(HttpApplication::STATUS_CODES[400])->sendJSON();
            return;
        }

        $isPlatformIdNumeric = is_numeric($platformId);
        if ($isPlatformIdNumeric === false) {
            $messages[] = 'O id da plataforma precisa ser um número inteiro.';
            $response->appendArray(['messages' => $messages])->status(HttpApplication::STATUS_CODES[400])->sendJSON();
            return;
        }

        $platformId = intval($platformId);
        if ($platformId <= 0) {
            $messages[] = 'O id da plataforma precisa ser un número inteiro maior que zero.';
            $response->appendArray(['messages' => $messages])->status(HttpApplication::STATUS_CODES[400])->sendJSON();
            return;
        }

        $platform = null;
        try {
            $platform = $this->service->findById($platformId);
        } catch (Exception $e) {
            $messages[] = $e->getMessage();
            $response->appendArray(['messages' => $messages])->status(HttpApplication::STATUS_CODES[500])->sendJSON();
            return;
        }

        if ($platform == null) {
            $messages[] = 'A plataforma procurada não existe!';
            $response->appendArray(['messages' => $messages])->status(HttpApplication::STATUS_CODES[404])->sendJSON();
            return;
        }

        $platformId = $platform->getId();
        $platformName = $platform->getName();

        $data = [
            'id' => $platformId,
            'name' => $platformName,
        ];

        $messages[] = 'Plataforma encontrada com sucesso!';
        $response->appendArray(['messages' => $messages, 'data' => $data])->status(HttpApplication::STATUS_CODES[200])->sendJSON();
    }

    public function findAll(HttpRequest $request, HttpResponse $response)
    {
        $messages = [];
        $data = [];

        $platforms = null;
        try {
            $platforms = $this->service->findAll();
        } catch (Exception $e) {
            $messages[] = $e->getMessage();
            $response->appendArray(['messages' => $messages])->status(HttpApplication::STATUS_CODES[500])->sendJSON();
            return;
        }

        $numberOfPlatforms = count($platforms);
        if ($numberOfPlatforms === 0) {
            $messages[] = 'A busca foi concluída e nenhuma plataforma foi encontrada.';
            $response->appendArray(['messages' => $messages])->status(HttpApplication::STATUS_CODES[200])->sendJSON();
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
        $response->appendArray(['messages' => $messages, 'data' => $data])->status(HttpApplication::STATUS_CODES[200])->sendJSON();
    }
}
