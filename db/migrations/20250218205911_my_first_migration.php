<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class MyFirstMigration extends AbstractMigration
{
    public function change(): void
    {
        $this
            ->table("user")
            ->addColumn("name", "text", [
                "null" => false
            ])
            ->addColumn("password", "text", [
                "null" => false
            ])
            ->addIndex("name", [
                "unique" => true
            ])
            ->create();

        $this
            ->table("game")
            ->addColumn("name", "string", [
                "limit" => 400,
                "null" => false
            ])
            ->addIndex("name", [
                "unique" => true
            ])
            ->create();

        $this
            ->table("platform")
            ->addColumn("name", "string", [
                "limit" => 100,
                "null" => false
            ])
            ->addIndex("name", [
                "unique" => true
            ])
            ->create();

        $this
            ->table("genre")
            ->addColumn("name", "string", [
                "limit" => 100,
                "null" => false
            ])
            ->addIndex("name", [
                "unique" => true
            ])
            ->create();

        $this
            ->table("game_platform")
            ->addColumn("game_id", "integer", [
                "null" => false,
                "signed" => false
            ])
            ->addForeignKey("game_id", "game", "id", [
                "delete" => "RESTRICT",
                "update" => "RESTRICT"
            ])
            ->addColumn("platform_id", "integer", [
                "null" => false,
                "signed" => false
            ])
            ->addForeignKey("platform_id", "platform", "id", [
                "delete" => "RESTRICT",
                "update" => "RESTRICT"
            ])
            ->create();

        $this->table("game_genre")
            ->addColumn("game_id", "integer", [
                "null" => false,
                "signed" => false
            ])
            ->addForeignKey("game_id", "game", "id", [
                "delete" => "RESTRICT",
                "update" => "RESTRICT"
            ])
            ->addColumn("genre_id", "integer", [
                "null" => false,
                "signed" => false
            ])
            ->addForeignKey("genre_id", "genre", "id", [
                "delete" => "RESTRICT",
                "update" => "RESTRICT"
            ])
            ->create();
    }
}
