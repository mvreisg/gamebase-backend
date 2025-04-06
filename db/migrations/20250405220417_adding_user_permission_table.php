<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddingUserPermissionTable extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('user_permission');
        $table->addColumn('user_id', 'integer', [
            'null' => false,
            'signed' => false
        ]);
        $table->addColumn('permission_id', 'integer', [
            'null' => false,
            'signed' => false
        ]);
        $table->addForeignKey('user_id', 'user', 'id', [
            'delete' => 'RESTRICT',
            'update' => 'RESTRICT'
        ]);
        $table->addForeignKey('permission_id', 'permission', 'id', [
            'delete' => 'RESTRICT',
            'update' => 'RESTRICT'
        ]);
        $table->create();
    }
}
