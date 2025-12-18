<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddingPermissionTable extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table("permission");
        $table->addColumn("name", "text", [
            "null" => false,
        ]);
        $table->addIndex("name", [
            "unique" => true
        ]);
        $table->addColumn("is_active", "boolean", [
            "null" => false
        ]);
        $table->create();
    }
}
