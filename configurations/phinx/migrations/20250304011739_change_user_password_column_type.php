<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class ChangeUserPasswordColumnType extends AbstractMigration
{
    public function up()
    {
        $this
            ->table("user")
            ->changeColumn("password", "blob", [
                "null" => false
            ])
            ->save();
    }

    public function down()
    {
        $this
            ->table("user")
            ->changeColumn("password", "string", [
                "null" => false,
                "limit" => 255
            ])
            ->save();
    }
}
