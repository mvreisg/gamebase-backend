<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class ChangeUserPasswordColumnType extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $users = $this->table('user');
        $users
            ->changeColumn('password', 'blob', [
                'null' => false
            ])
            ->save();
    }
}
