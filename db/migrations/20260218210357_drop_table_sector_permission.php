<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class DropTableSectorPermission extends AbstractMigration
{
    public function up(): void
    {
        $this
            ->table("sector_permission")
            ->drop()
            ->save();
    }

    public function down(): void
    {
        $this
            ->table("sector_permission")
            ->addColumn("sector_id", "integer", [
                "null" => false,
                "signed" => false
            ])
            ->addColumn("permission_id", "integer", [
                "null" => false,
                "signed" => false
            ])
            ->addForeignKey("sector_id", "sector", "id", [
                "delete" => "RESTRICT",
                "update" => "RESTRICT"
            ])
            ->addForeignKey("permission_id", "permission", "id", [
                "delete" => "RESTRICT",
                "update" => "RESTRICT"
            ])
            ->create();
    }
}
