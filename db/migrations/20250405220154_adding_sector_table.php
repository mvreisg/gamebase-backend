<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddingSectorTable extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('sector');
        $table->addColumn('name', 'text', [
            'null' => false,
        ]);
        $table->addIndex('name', [
            'unique' => true
        ]);
        $table->addColumn('is_active', 'boolean', [
            'null' => false
        ]);
        $table->create();
    }
}
