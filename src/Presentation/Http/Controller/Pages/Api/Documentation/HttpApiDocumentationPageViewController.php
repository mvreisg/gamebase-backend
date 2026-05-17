<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Controller\Pages\Api\Documentation;

use Mvreisg\GamebaseBackend\Presentation\Http\Option\HttpOptions;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Twig\Environment;

class HttpApiDocumentationPageViewController
{
    private HttpOptions $options;
    private Environment $environment;

    public function __construct(
        HttpOptions $options,
        Environment $environment,
    ) {
        $this->options = $options;
        $this->environment = $environment;
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        $html = $this->environment->render("Pages/Api/Documentation/ApiDocumentationPageView.twig", [
            "host" => $this->options->getHost()
        ]);
        $response->getBody()->write($html);
        return $response;
    }
}
