<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Controller\Pages\Encryption\Jwt;

use Mvreisg\GamebaseBackend\Presentation\Http\Model\Components\Encryption\Jwt\HttpJwtEncryptionComponentModel;
use Mvreisg\GamebaseBackend\Presentation\Http\Option\HttpOptions;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Twig\Environment;

class HttpJwtEncryptionPageController
{
    private HttpJwtEncryptionComponentModel $model;
    private HttpOptions $options;
    private Environment $environment;
    private LoggerInterface $logger;

    public function __construct(
        HttpJwtEncryptionComponentModel $model,
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
            $this->model->execute();
            $html = $this->environment->render("Pages/Encryption/Jwt/JwtEncryptionView.twig", [
                "host" => $this->options->getHost(),
                "title" => $this->options->getTitle(),
                "jwt" => [
                    "returnCode" => $this->model->getReturnCode(),
                    "output" => $this->model->getOutput()
                ],
            ]);
            $response->getBody()->write($html);
            $this->logger->notice("JwtEncryptionView successfully rendered!");
            return $response;
        } catch (\Throwable $e) {
            $this->logger->error("JwtEncryptionView rendering failed!", [
                "exception" => $e->getMessage()
            ]);
            throw $e;
        }
    }
}
