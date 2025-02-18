<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class MyFirstMigration extends AbstractMigration
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
        // User - Usuário
        $userTable = $this->table('user');
        $userTable
            ->addColumn('name', 'text', [
                'null' => false
            ])
            ->addColumn('password', 'text', [
                'null' => false
            ])
            ->addIndex('name', [
                'unique' => true
            ])
            ->create();

        // Game - Jogo
        $gameTable = $this->table('game');
        $gameTable
            ->addColumn('name', 'string', [
                'limit' => 400, 
                'null' => false
            ])
            ->addIndex('name', [
                'unique' => true
            ])
            ->create();

        // Platform - Plataforma
        $platformTable = $this->table('platform');
        $platformTable
            ->addColumn('name', 'string', [
                'limit' => 100, 
                'null' => false
            ])
            ->addIndex('name', [
                'unique' => true
            ])
            ->create();

        // Genre - Gênero
        $genreTable = $this->table('genre');
        $genreTable
            ->addColumn('name', 'string', [
                'limit' => 100, 
                'null' => false
            ])
            ->addIndex('name', [
                'unique' => true
            ])
            ->create();

        $gamePlatformTable = $this->table('game_platform');
        $gamePlatformTable
            ->addColumn('game_id', 'integer', [
                'null' => false, 
                'signed' => false
            ])
            ->addForeignKey('game_id', 'game', 'id', [
                'delete'=> 'RESTRICT', 
                'update'=> 'RESTRICT'
            ])
            ->addColumn('platform_id', 'integer', [
                'null' => false, 
                'signed' => false
            ])            
            ->addForeignKey('platform_id', 'platform', 'id', [
                'delete'=> 'RESTRICT', 
                'update'=> 'RESTRICT'
            ])
            ->create();

        $gameGenreTable = $this->table('game_genre');
        $gameGenreTable
            ->addColumn('game_id', 'integer', [
                'null' => false, 
                'signed' => false
            ])
            ->addForeignKey('game_id', 'game', 'id', [
                'delete'=> 'RESTRICT', 
                'update'=> 'RESTRICT'
            ])
            ->addColumn('genre_id', 'integer', [
                'null' => false, 
                'signed' => false
            ])            
            ->addForeignKey('genre_id', 'genre', 'id', [
                'delete'=> 'RESTRICT', 
                'update'=> 'RESTRICT'
            ])
            ->create();
    }
}
