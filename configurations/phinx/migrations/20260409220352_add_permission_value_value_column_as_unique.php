<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddPermissionValueValueColumnAsUnique extends AbstractMigration
{
    public function up(): void
    {
        $this
            ->table("user_sector_permission")
            ->dropForeignKey("permission_id")
            ->save();

        $this
            ->table("permission")
            ->addIndex("value", [
                "unique" => true
            ])
            ->update();

        $this
            ->table("user_sector_permission")
            ->addForeignKey("permission_id", "permission", "id")
            ->save();
    }

    public function down(): void
    {
        $this
            ->table("user_sector_permission")
            ->dropForeignKey("permission_id")
            ->save();

        $this
            ->table("permission")
            ->removeIndexByName("value")
            ->update();

        $this
            ->table("user_sector_permission")
            ->addForeignKey("permission_id", "permission", "id")
            ->save();
    }
}
