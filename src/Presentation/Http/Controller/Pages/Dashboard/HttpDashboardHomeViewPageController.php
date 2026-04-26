<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Controller\Pages\Dashboard;

use Mvreisg\GamebaseBackend\Presentation\Http\Option\HttpOptions;
use Mvreisg\GamebaseBackend\Presentation\Http\Views\Pages\Dashboard\DashboardHomeView;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class HttpDashboardHomeViewPageController
{
    private HttpOptions $options;

    public function __construct(HttpOptions $options)
    {
        $this->options = $options;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $response
            ->getBody()
            ->write(
                DashboardHomeView::create()->getHtml(
                    $this->options->getHost(),
                    $this->options->getTitle()
                )
            );
        return $response;
    }
}
