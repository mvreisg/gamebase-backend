<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddingSectorTable extends AbstractMigration
{
    public function change(): void
    {
        $this
            ->table("sector")
            ->addColumn("name", "text", [
                "null" => false,
            ])
            ->addIndex("name", [
                "unique" => true
            ])
            ->addColumn("is_active", "boolean", [
                "null" => false
            ])
            ->create();
    }
}
