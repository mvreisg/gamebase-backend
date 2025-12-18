<?php

declare(strict_types=1);

use Mvreisg\GamebaseBackend\Infrastructure\Encryption\Defuse\DefuseEncryption;
use Mvreisg\GamebaseBackend\Infrastructure\Environments\Dotenv\DotenvEnvironment;
use Phinx\Seed\AbstractSeed;

class AddingFirstUser extends AbstractSeed
{
    public function run(): void
    {
        $data = [
            [
                "username" => DotenvEnvironment::get(
                    "REPOSITORY_ROOT_USERNAME"
                ),
                "password" => (new DefuseEncryption())
                    ->encrypt(
                        DotenvEnvironment::get(
                            "REPOSITORY_ROOT_PASSWORD"
                        )
                    ),
                "is_active" => 1
            ]
        ];

        $user = $this->table("user");
        $user
            ->insert($data)
            ->saveData();
    }
}
