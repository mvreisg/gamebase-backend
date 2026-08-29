<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Model\Components\OpenApi\Documentation;

class HttpOpenApiDocumentationComponentModel
{
    private array $output;
    private int $returnCode;
    private bool $isReady;

    public function __construct()
    {
        $this->output = [];
        $this->returnCode = 0;
        $this->isReady = false;
    }

    public function execute()
    {
        $root = escapeshellarg(PROJECT_ROOT);

        $path = PROJECT_ROOT . "/public/docs";

        if (!is_dir($path)) {
            mkdir($path, 0775, true);
        }

        $command = "bash $root/configurations/api/startup/api_startup.sh 2>&1";

        exec(
            $command,
            $this->output,
            $this->returnCode
        );

        $this->isReady = $this->returnCode === 0;
    }

    public function getOutput(): array
    {
        return $this->output;
    }

    public function getReturnCode(): int
    {
        return $this->returnCode;
    }

    public function isReady(): bool
    {
        return $this->isReady;
    }
}
