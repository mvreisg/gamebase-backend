<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Controller\Pages\Dashboard\OpenApi\Documentation;

use Mvreisg\GamebaseBackend\Presentation\Http\Model\Components\OpenApi\Documentation\{
    HttpOpenApiDocumentationComponentModel
};
use Mvreisg\GamebaseBackend\Presentation\Http\Option\HttpOptions;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Twig\Environment;

class HttpOpenApiDocumentationDashboardViewPageController
{
    private HttpOptions $options;
    private Environment $environment;
    private HttpOpenApiDocumentationComponentModel $openApiDocumentationComponentModel;
    private LoggerInterface $logger;

    public function __construct(
        Environment $environment,
        HttpOptions $options,
        HttpOpenApiDocumentationComponentModel $openApiDocumentationComponentModel,
        LoggerInterface $logger
    ) {
        $this->environment = $environment;
        $this->options = $options;
        $this->openApiDocumentationComponentModel = $openApiDocumentationComponentModel;
        $this->logger = $logger;
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        try {
            $this->openApiDocumentationComponentModel->execute();
            $html = $this->environment->render(
                "Pages/Dashboard/OpenApi/Documentation/OpenApiDocumentationDashboardPageView.twig",
                [
                    "host" => $this->options->getHost(),
                    "title" => $this->options->getTitle(),
                    "openApi" => [
                        "returnCode" => $this->openApiDocumentationComponentModel->getReturnCode(),
                        "output" => $this->openApiDocumentationComponentModel->getOutput(),
                        "isReady" => $this->openApiDocumentationComponentModel->isReady()
                    ]
                ]
            );
            $response->getBody()->write($html);
            $this->logger->notice("OpenApiDocumentationDashboardPageView successfully rendered!");
            return $response;
        } catch (\Throwable $e) {
            $this->logger->error("OpenApiDocumentationDashboardPageView rendering failed!", [
                "exception" => $e->getMessage()
            ]);
            throw $e;
        }
    }
}
