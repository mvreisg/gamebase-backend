<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Controller\Pages\Dashboard\Database\Pdo;

use Mvreisg\GamebaseBackend\Application\Shared\Service\DatabaseService;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDb\Option\MariaDbRepositoryOptions;
use Mvreisg\GamebaseBackend\Presentation\Http\Option\HttpOptions;
use Mvreisg\GamebaseBackend\Presentation\Http\Views\Pages\Dashboard\Database\Pdo\PdoDatabaseDashboardView;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class HttpPdoDatabaseDashboardViewPageController
{
    private DatabaseService $databaseService;
    private MariaDbRepositoryOptions $repositoryOptions;
    private HttpOptions $options;

    public function __construct(DatabaseService $databaseService, MariaDbRepositoryOptions $repositoryOptions, HttpOptions $options)
    {
        $this->databaseService = $databaseService;
        $this->repositoryOptions = $repositoryOptions;
        $this->options = $options;
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
        $response
            ->getBody()
            ->write(
                PdoDatabaseDashboardView::create()->getHtml(
                    $this->options->getHost(),
                    $this->options->getTitle(),
                    $this->repositoryOptions->getDatabase(),
                    $exists
                )
            );
        return $response;
    }
}
