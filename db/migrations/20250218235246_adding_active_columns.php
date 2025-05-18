<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddingActiveColumns extends AbstractMigration
{
    public function change(): void
    {
        $userTable = $this->table('user');
        $userTable
            ->addColumn('is_active', 'boolean', [
                'null' => false
            ])
            ->update();

        $gameTable = $this->table('game');
        $gameTable
            ->addColumn('is_active', 'boolean', [
                'null' => false
            ])
            ->update();

        $platformTable = $this->table('platform');
        $platformTable
            ->addColumn('is_active', 'boolean', [
                'null' => false
            ])
            ->update();

        $genreTable = $this->table('genre');
        $genreTable
            ->addColumn('is_active', 'boolean', [
                'null' => false
            ])
            ->update();
    }
}
