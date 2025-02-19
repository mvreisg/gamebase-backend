<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddingActiveColumns extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
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
