<?php

declare(strict_types=1);

use Mvreisg\GamebaseBackend\Infrastructure\Encryption\DefuseEncryption;
use Phinx\Seed\AbstractSeed;

class AddingFirstUser extends AbstractSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * https://book.cakephp.org/phinx/0/en/seeding.html
     */
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
