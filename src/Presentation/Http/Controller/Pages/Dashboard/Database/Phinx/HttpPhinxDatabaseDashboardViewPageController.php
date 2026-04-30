<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Controller\Pages\Dashboard\Database\Phinx;

use Mvreisg\GamebaseBackend\Application\Shared\Service\DatabaseService;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\Option\RepositoryOptions;
use Mvreisg\GamebaseBackend\Presentation\Http\Model\Components\Database\Phinx\HttpPhinxDatabaseComponentModel;
use Mvreisg\GamebaseBackend\Presentation\Http\Option\HttpOptions;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Twig\Environment;

class HttpPhinxDatabaseDashboardViewPageController
{
    private HttpOptions $options;
    private Environment $environment;
    private DatabaseService $databaseService;
    private RepositoryOptions $repositoryOptions;
    private HttpPhinxDatabaseComponentModel $phinxDatabaseComponentModel;

    public function __construct(
        Environment $environment,
        HttpOptions $options,
        HttpPhinxDatabaseComponentModel $phinxDatabaseComponentModel,
        DatabaseService $databaseService,
        RepositoryOptions $repositoryOptions
    ) {
        $this->environment = $environment;
        $this->options = $options;
        $this->phinxDatabaseComponentModel = $phinxDatabaseComponentModel;
        $this->databaseService = $databaseService;
        $this->repositoryOptions = $repositoryOptions;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $doesDatabaseExist = $this->databaseService->exists(
            $this->repositoryOptions->getDatabase()
        );
        if ($doesDatabaseExist === false) {
            $this->databaseService->create(
                $this->repositoryOptions->getDatabase()
            );
            $this->phinxDatabaseComponentModel->execute();
            $doesDatabaseExist = $this->databaseService->exists(
                $this->repositoryOptions->getDatabase()
            );
        }
        $html = $this->environment->render("Pages/Dashboard/Database/Phinx/PhinxDatabaseDashboardPageView.twig", [
            "host" => $this->options->getHost(),
            "title" => $this->options->getTitle(),
            "phinx" => [
                "returnCode" => $this->phinxDatabaseComponentModel->getReturnCode(),
                "output" => $this->phinxDatabaseComponentModel->getOutput()
            ],
            "database" => [
                "exists" => $doesDatabaseExist
            ]
        ]);
        $response->getBody()->write($html);
        return $response;
    }
}
