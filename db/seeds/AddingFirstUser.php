<?php

declare(strict_types=1);

use Mvreisg\GamebaseBackend\Infrastructure\Encryption\DefuseEncryption;
use Phinx\Seed\AbstractSeed;

class AddingFirstUser extends AbstractSeed
{
    public function run(): void
    {
        $data = [
            [
                'username' => $_SERVER['APP_ROOT_USERNAME'],
                'password' => (new DefuseEncryption())->encrypt($_SERVER['APP_ROOT_PASSWORD']),
                'is_active' => 1
            ]
        ];

        $user = $this->table('user');
        $user
            ->insert($data)
            ->saveData();
    }
}
