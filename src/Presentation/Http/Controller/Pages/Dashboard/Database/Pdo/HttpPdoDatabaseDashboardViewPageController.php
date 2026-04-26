<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Controller\Pages\Dashboard\Database\Pdo;

use Mvreisg\GamebaseBackend\Application\Shared\Service\DatabaseService;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\MariaDb\Option\MariaDbRepositoryOptions;
use Mvreisg\GamebaseBackend\Presentation\Http\Views\Pages\Dashboard\Database\Pdo\PdoDatabaseDashboardView;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class HttpPdoDatabaseDashboardViewPageController
{
    private DatabaseService $databaseService;
    private MariaDbRepositoryOptions $repositoryOptions;

    public function __construct(DatabaseService $databaseService, MariaDbRepositoryOptions $repositoryOptions)
    {
        $this->databaseService = $databaseService;
        $this->repositoryOptions = $repositoryOptions;
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
                PdoDatabaseDashboardView::create(
                    TITLE,
                    $this->repositoryOptions->getDatabase(),
                    $exists
                )->getHtml(BASE_URL)
            );
        return $response;
    }
}
