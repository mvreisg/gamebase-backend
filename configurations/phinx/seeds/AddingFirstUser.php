<?php

declare(strict_types=1);

use DI\Container;
use Mvreisg\GamebaseBackend\Infrastructure\Encryption\EncryptionAdapter;
use Phinx\Seed\AbstractSeed;

class AddingFirstUser extends AbstractSeed
{
    public function run(): void
    {
        require_once dirname(__DIR__, 3) . "/constants.php";

        /**
         * @var Container
         */
        $container = require PROJECT_ROOT . "/configurations/php-di/container_bootstrap.php";

        /**
         * @var EncryptionAdapter
         */
        $encrypter = $container->get(EncryptionAdapter::class);
        $data = [
            [
                "username" => $container->get("repository.root.username"),
                "password" => $encrypter->encrypt(
                    $container->get("repository.root.username")
                ),
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
