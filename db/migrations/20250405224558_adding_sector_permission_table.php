<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddingSectorPermissionTable extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table("sector_permission");
        $table->addColumn("sector_id", "integer", [
            "null" => false,
            "signed" => false
        ]);
        $table->addColumn("permission_id", "integer", [
            "null" => false,
            "signed" => false
        ]);
        $table->addForeignKey("sector_id", "sector", "id", [
            "delete" => "RESTRICT",
            "update" => "RESTRICT"
        ]);
        $table->addForeignKey("permission_id", "permission", "id", [
            "delete" => "RESTRICT",
            "update" => "RESTRICT"
        ]);
        $table->create();
    }
}
