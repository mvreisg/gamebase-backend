<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Model\Components\Encryption\Jwt;

class HttpJwtEncryptionComponentModel
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
        $command = "openssl rand -base64 32";

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
