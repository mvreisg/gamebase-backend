<?php
    namespace Gamebase\Presentation\Controllers;

    use Exception;
    use Gamebase\Application\Services\PlatformService;
    use Gamebase\Domain\Exceptions\InvalidValueException;
    use Gamebase\Infrastructure\Exceptions\DuplicatedEntryException;
    use Gamebase\Infrastructure\Http\HttpRequest;
    use Gamebase\Infrastructure\Http\HttpResponse;

    class PlatformController 
    {
        private PlatformService $service;

        public function __construct(PlatformService $service)
        {
            $this->service = $service;
        }

        public function insert(HttpRequest $request, HttpResponse $response)
        {
            $message = [];

            $body = $request->parseBodyFromJSON();

            $name = $body["name"] ?? null;

            $hasParameterError = false;
            if ($name === null){
                $hasParameterError = true;
                $message[] = "O parâmetro 'name' não foi informado.";
            }

            if ($hasParameterError){
                $response->appendArray(["message" => $message])->status(HTTP_STATUS_CODE_400)->sendJSON();
                return;
            }

            try
            {
                $platform = $this->service->insert($name);
                if ($platform == false) 
                {
                    $message[] = "Ocorreu um erro ao inserir a plataforma. Contate o suporte.";
                    $response->appendArray(["message" => $message])->status(HTTP_STATUS_CODE_500)->sendJSON();
                    return;
                }

                $message[] = "Plataforma incluída com sucesso!";
                $response->appendArray(["message" => $message])->status(HTTP_STATUS_CODE_201)->sendJSON();
            }
            catch(InvalidValueException | DuplicatedEntryException $e)
            {
                $message[] = $e->getMessage();
                $response->appendArray(["message" => $message])->status(HTTP_STATUS_CODE_400)->sendJSON();
            }
            catch (Exception $e) 
            {
                $message[] = $e->getMessage();
                $response->appendArray(["message" => $message])->status(HTTP_STATUS_CODE_500)->sendJSON();
            }
        }

        public function edit(HttpRequest $request, HttpResponse $response)
        {
            $message = [];

            try 
            {
                $body = $request->parseBodyFromJSON();
                $params = $request->getParams();

                $platformId = $params["platformId"] ?? null;
                $name = $body["name"];

                $hasParameterError = false; 
                if ($platformId === null){
                    $message[] = "O parâmetro 'platformId' é nulo.";
                    $response->appendArray(["message" => $message])->status(HTTP_STATUS_CODE_400)->sendJSON();
                    return;
                }
                    
                $isPlatformIdNumeric = is_numeric($platformId);
                if ($isPlatformIdNumeric === false)
                {
                    $message[] = "O parâmetro 'platformId' informado precisa ser um número inteiro.";
                    $response->appendArray(["message" => $message])->status(HTTP_STATUS_CODE_400)->sendJSON();
                    return;
                }

                $platformId = intval($platformId);

                if ($platformId <= 0)
                {
                    $message[] = "O parâmetro 'platformId' informado precisa ser un número inteiro maior que zero.";
                    $response->appendArray(["message" => $message])->status(HTTP_STATUS_CODE_400)->sendJSON();
                    return;
                }

                if ($name === null){
                    $hasParameterError = true;
                    $message[] = "O parâmetro 'name' não foi informado.";
                }
    
                if ($hasParameterError){
                    $response->appendArray(["message" => $message])->status(HTTP_STATUS_CODE_400)->sendJSON();
                    return;
                }

                $wasEditAnSuccess = $this->service->edit($platformId, $name);
                if ($wasEditAnSuccess === false) 
                {
                    $message[] = "Ocorreu algum erro ao editar a plataforma. Contate o suporte.";
                    $response->appendArray(["message" => $message])->status(HTTP_STATUS_CODE_500)->sendJSON();
                    return;
                }

                $message[] = "Plataforma editada com sucesso!";
                $response->appendArray(["message" => $message])->status(HTTP_STATUS_CODE_200)->sendJSON();
            }
            catch (Exception $e) 
            {
                $message[] = $e->getMessage();
                $response->appendArray(["message" => $message])->status(HTTP_STATUS_CODE_500)->sendJSON();
            }
        }

        public function findById(HttpRequest $request, HttpResponse $response)
        {
            $message = [];

            try
            {
                $params = $request->getParams();
                $platformId = $params["platformId"] ?? null;

                if ($platformId === null){
                    $message[] = "O parâmetro 'platformId' informado é nulo.";
                    $response->appendArray(["message" => $message])->status(HTTP_STATUS_CODE_400)->sendJSON();
                    return;
                }
                    
                $isPlatformIdNumeric = is_numeric($platformId);
                if ($isPlatformIdNumeric === false)
                {
                    $message[] = "O parâmetro 'platformId' informado precisa ser um número inteiro.";
                    $response->appendArray(["message" => $message])->status(HTTP_STATUS_CODE_400)->sendJSON();
                    return;
                }

                $platformId = intval($platformId);

                if ($platformId <= 0)
                {
                    $message[] = "O parâmetro 'platformId' informado precisa ser un número inteiro maior que zero.";
                    $response->appendArray(["message" => $message])->status(HTTP_STATUS_CODE_400)->sendJSON();
                    return;
                }

                $platform = $this->service->findById($platformId);
                if ($platform == null)
                {
                    $message[] = "A plataforma procurada não existe!";
                    $response->appendArray(["message" => $message])->status(HTTP_STATUS_CODE_404)->sendJSON();
                    return;
                }

                $platformId = $platform->getId();
                $platformName = $platform->getName();

                $message[] = "Plataforma encontrada com sucesso!";
                $response->appendArray(array_merge(["message" => $message], [
                    "data" => [
                        "id" => $platformId,
                        "name" => $platformName,
                    ]
                ]))->status(HTTP_STATUS_CODE_200)->sendJSON();
            }
            catch (Exception $e) 
            {
                $message[] = $e->getMessage();
                $response->appendArray(["message" => $message])->status(HTTP_STATUS_CODE_500)->sendJSON();
            }
        }

        public function findAll(HttpRequest $request, HttpResponse $response)
        {
            $message = [];

            try
            {
                $platforms = $this->service->findAll();

                $numberOfPlatforms = count($platforms);
                if ($numberOfPlatforms === 0) 
                {
                    $message[] = "A busca foi concluída e nenhuma plataforma foi encontrada.";
                    $response->appendArray(["message" => $message])->status(HTTP_STATUS_CODE_200)->sendJSON();
                    return;
                }

                $data = [];
                foreach ($platforms as $platform) 
                {
                    $data[] = [
                        "id" => $platform->getId(),
                        "name" => $platform->getName(),
                    ];
                }

                $message[] = "Plataformas buscadas com sucesso!";
                $response->appendArray(array_merge(["message" => $message], ["data" => $data]))->status(HTTP_STATUS_CODE_200)->sendJSON();
            }
            catch (Exception $e) 
            {
                $message[] = $e->getMessage();
                $response->appendArray(["message" => $message])->status(HTTP_STATUS_CODE_500)->sendJSON();
            }
        }
    }
?>