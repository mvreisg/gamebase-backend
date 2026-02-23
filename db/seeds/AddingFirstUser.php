<?php

declare(strict_types=1);

use Mvreisg\GamebaseBackend\Infrastructure\Encryption\EncryptionAdapter;
use Mvreisg\GamebaseBackend\Infrastructure\Environments\Dotenv\DotenvEnvironment;
use Phinx\Seed\AbstractSeed;

class AddingFirstUser extends AbstractSeed
{
    public function run(): void
    {
        $data = [
            [
                "username" => DotenvEnvironment::get("REPOSITORY_ROOT_USERNAME"),
                "password" => EncryptionAdapter::make()->encrypt(DotenvEnvironment::get("REPOSITORY_ROOT_PASSWORD")),
                "is_active" => 1
            ]
        ];

        $result = $this->fetchRow(
            "SELECT COUNT(*) AS count FROM user WHERE username = '{$data[0]["username"]}'",
        );

        if ($result["count"] > 0) {
            return;
        }

        $this
            ->table("user")
            ->insert($data)
            ->saveData();
    }
}
