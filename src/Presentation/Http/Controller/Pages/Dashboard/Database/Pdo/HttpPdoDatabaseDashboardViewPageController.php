<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Controller\Pages\Dashboard\Database\Pdo;

use Mvreisg\GamebaseBackend\Application\Shared\Service\DatabaseService;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\Option\RepositoryOptions;
use Mvreisg\GamebaseBackend\Presentation\Http\Option\HttpOptions;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Twig\Environment;

class HttpPdoDatabaseDashboardViewPageController
{
    private DatabaseService $databaseService;
    private RepositoryOptions $repositoryOptions;
    private HttpOptions $options;
    private Environment $environment;

    public function __construct(
        DatabaseService $databaseService,
        RepositoryOptions $repositoryOptions,
        HttpOptions $options,
        Environment $environment
    ) {
        $this->databaseService = $databaseService;
        $this->repositoryOptions = $repositoryOptions;
        $this->options = $options;
        $this->environment = $environment;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $exists = $this->databaseService->exists(
            $this->repositoryOptions->getDatabase()
        );
        $params = $request->getQueryParams();
        if (isset($params["action"])) {
            $action = $params["action"];
            switch ($action) {
                case "create":
                    if ($exists === false) {
                        $this->databaseService->create($this->repositoryOptions->getDatabase());
                    }
                    break;
                case "drop":
                    if ($exists) {
                        $this->databaseService->drop($this->repositoryOptions->getDatabase());
                    }
                    break;
            }
        }
        $exists = $this->databaseService->exists(
            $this->repositoryOptions->getDatabase()
        );
        $html = $this->environment->render("Pages/Dashboard/Database/Pdo/PdoDatabaseDashboardPageView.twig", [
            "title" => $this->options->getTitle(),
            "host" => $this->options->getHost(),
            "database" => [
                "name" => $this->repositoryOptions->getDatabase(),
                "exists" => $exists
            ]
        ]);
        $response->getBody()->write($html);
        return $response;
    }
}
