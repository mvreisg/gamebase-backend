<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddingValueFieldToPermissionTable extends AbstractMigration
{
    public function change(): void
    {
        $this
            ->table("permission")
            ->addColumn("value", "text", [
                "null" => false,
            ])
            ->save();
    }
}
