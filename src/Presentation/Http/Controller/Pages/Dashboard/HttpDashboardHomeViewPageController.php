<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Controller\Pages\Dashboard;

use Mvreisg\GamebaseBackend\Presentation\Http\Option\HttpOptions;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Twig\Environment;

class HttpDashboardHomeViewPageController
{
    private HttpOptions $options;
    private Environment $environment;
    private LoggerInterface $logger;

    public function __construct(
        HttpOptions $options,
        Environment $environment,
        LoggerInterface $logger
    ) {
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
            $html = $this->environment->render("Pages/Dashboard/DashboardHomePageView.twig", [
                "host" => $this->options->getHost(),
                "title" => $this->options->getTitle()
            ]);
            $response->getBody()->write($html);
            $this->logger->notice("DashboardHomePageView successfully rendered!");
            return $response;
        } catch (\Throwable $e) {
            $this->logger->error("DashboardHomePageView rendering failed!", [
                "exception" => $e->getMessage()
            ]);
            throw $e;
        }
    }
}
