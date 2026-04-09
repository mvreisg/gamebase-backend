<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddSectorValueValueColumnAsUnique extends AbstractMigration
{
    public function up(): void
    {
        $this
            ->table("user_sector_permission")
            ->dropForeignKey("sector_id")
            ->save();

        $this
            ->table("sector")
            ->addIndex("value", [
                "unique" => true
            ])
            ->update();

        $this
            ->table("user_sector_permission")
            ->addForeignKey("sector_id", "sector", "id")
            ->save();
    }

    public function down(): void
    {
        $this
            ->table("user_sector_permission")
            ->dropForeignKey("sector_id")
            ->save();

        $this
            ->table("sector")
            ->removeIndexByName("value")
            ->update();

        $this
            ->table("user_sector_permission")
            ->addForeignKey("sector_id", "sector", "id")
            ->save();
    }
}
