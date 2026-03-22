<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddingActiveColumns extends AbstractMigration
{
    public function change(): void
    {
        $this
            ->table("user")
            ->addColumn("is_active", "boolean", [
                "null" => false
            ])
            ->update();

        $this
            ->table("game")
            ->addColumn("is_active", "boolean", [
                "null" => false
            ])
            ->update();

        $this
            ->table("platform")
            ->addColumn("is_active", "boolean", [
                "null" => false
            ])
            ->update();

        $this
            ->table("genre")
            ->addColumn("is_active", "boolean", [
                "null" => false
            ])
            ->update();
    }
}
