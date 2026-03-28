<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddingValueFieldToSectorTable extends AbstractMigration
{
    public function change(): void
    {
        $this
            ->table("sector")
            ->addColumn("value", "text", [
                "null" => false,
            ])
            ->save();
    }
}
