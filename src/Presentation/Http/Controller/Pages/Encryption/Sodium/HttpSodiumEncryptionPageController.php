<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Controller\Pages\Encryption\Sodium;

use Mvreisg\GamebaseBackend\Presentation\Http\Model\Components\Encryption\Sodium\HttpSodiumEncryptionComponentModel;
use Mvreisg\GamebaseBackend\Presentation\Http\Option\HttpOptions;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Twig\Environment;

class HttpSodiumEncryptionPageController
{
    private HttpSodiumEncryptionComponentModel $model;
    private HttpOptions $options;
    private Environment $environment;
    private LoggerInterface $logger;

    public function __construct(
        HttpSodiumEncryptionComponentModel $model,
        HttpOptions $options,
        Environment $environment,
        LoggerInterface $logger
    ) {
        $this->model = $model;
        $this->options = $options;
        $this->environment = $environment;
        $this->logger = $logger;
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        try {
            $html = $this->environment->render("Pages/Encryption/Sodium/SodiumEncryptionView.twig", [
                "host" => $this->options->getHost(),
                "title" => $this->options->getTitle(),
                "sodium" => [
                    "key" => $this->model->getKey()
                ]
            ]);
            $response->getBody()->write($html);
            $this->logger->notice("SodiumEncryptionView successfully rendered!");
            return $response;
        } catch (\Throwable $e) {
            $this->logger->error("SodiumEncryptionView rendering failed!", [
                "exception" => $e->getMessage()
            ]);
            throw $e;
        }
    }
}
