<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Model\Components\Database\Phinx;

class HttpPhinxDatabaseComponentModel
{
    private array $output;
    private int $returnCode;

    public function __construct()
    {
        $this->output = [];
        $this->returnCode = 0;
    }

    public function execute()
    {
        $root = escapeshellarg(PROJECT_ROOT);

        $command = "bash $root/configurations/phinx/startup/phinx_startup.sh 2>&1";

        exec(
            $command,
            $this->output,
            $this->returnCode
        );
    }

    public function getOutput(): array
    {
        return $this->output;
    }

    public function getReturnCode(): int
    {
        return $this->returnCode;
    }
}
