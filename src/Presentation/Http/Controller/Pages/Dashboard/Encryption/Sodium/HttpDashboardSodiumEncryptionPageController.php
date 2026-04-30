<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Controller\Pages\Dashboard\Encryption\Sodium;

use Mvreisg\GamebaseBackend\Presentation\Http\Model\Components\Encryption\Sodium\HttpSodiumEncryptionComponentModel;
use Mvreisg\GamebaseBackend\Presentation\Http\Option\HttpOptions;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Twig\Environment;

class HttpDashboardSodiumEncryptionPageController
{
    private HttpSodiumEncryptionComponentModel $model;
    private HttpOptions $options;
    private Environment $environment;

    public function __construct(
        HttpSodiumEncryptionComponentModel $model,
        HttpOptions $options,
        Environment $environment
    ) {
        $this->model = $model;
        $this->options = $options;
        $this->environment = $environment;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $html = $this->environment->render("Pages/Dashboard/Encryption/Sodium/SodiumEncryptionDashboardView.twig", [
            "host" => $this->options->getHost(),
            "title" => $this->options->getTitle(),
            "sodium" => [
                "key" => $this->model->getKey()
            ]
        ]);
        $response
            ->getBody()
            ->write(
                $html
            );
        return $response;
    }
}
