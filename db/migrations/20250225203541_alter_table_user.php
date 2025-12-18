<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AlterTableUser extends AbstractMigration
{
    public function up()
    {
        $table = $this->table("user");
        $table
            ->renameColumn("name", "username")
            ->save();
    }

    public function down()
    {
        $table = $this->table("user");
        $table
            ->renameColumn("username", "name")
            ->save();
    }
}
