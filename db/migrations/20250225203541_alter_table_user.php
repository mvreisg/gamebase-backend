<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AlterTableUser extends AbstractMigration
{
    public function up()
    {
        $this
            ->table("user")
            ->renameColumn("name", "username")
            ->save();
    }

    public function down()
    {
        $this
            ->table("user")
            ->renameColumn("username", "name")
            ->save();
    }
}
