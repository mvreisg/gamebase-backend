<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddingUserPermissionTable extends AbstractMigration
{
    public function change(): void
    {
        $this
            ->table("user_permission")
            ->addColumn("user_id", "integer", [
                "null" => false,
                "signed" => false
            ])
            ->addColumn("permission_id", "integer", [
                "null" => false,
                "signed" => false
            ])
            ->addForeignKey("user_id", "user", "id", [
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
