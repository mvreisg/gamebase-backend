<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Services;

use ArrayIterator;
use Mvreisg\GamebaseBackend\Domain\Entities\GameGenre;
use Mvreisg\GamebaseBackend\Domain\Exceptions\EntityInvalidValueException;
use Mvreisg\GamebaseBackend\Domain\Repositories\GameGenreRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\GameRepositoryInterface;
use Mvreisg\GamebaseBackend\Domain\Repositories\GenreRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\DatabaseUnexistantRegisterException;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock\MockGameGenreRepository;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock\MockGameRepository;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock\MockGenreRepository;
use PHPUnit\Framework\TestCase;

class GameGenreServiceTest extends TestCase
{
    private GameRepositoryInterface $gameRepository;
    private GameService $gameService;
    private GenreRepositoryInterface $genreRepository;
    private GenreService $genreService;
    private GameGenreRepositoryInterface $gameGenreRepository;
    private GameGenreService $gameGenreService;

    protected function setUp(): void
    {
        $this->gameRepository = new MockGameRepository();
        $this->gameService = new GameService($this->gameRepository);
        $this->genreRepository = new MockGenreRepository();
        $this->genreService = new GenreService($this->genreRepository);
        $this->gameGenreRepository = new MockGameGenreRepository(
            $this->gameRepository,
            $this->genreRepository
        );
        $this->gameGenreService = new GameGenreService($this->gameGenreRepository);
    }

    public function testIfInsertSucceds(): void
    {
        $genre = $this->genreService->insert('test', true);
        $genreId = $genre->getId();

        $game = $this->gameService->insert('test', true);
        $gameId = $game->getId();

        $gameGenre = $this->gameGenreService->insert($genreId, $gameId);

        $this->assertNotEmpty($gameGenre);
        $this->assertInstanceOf(GameGenre::class, $gameGenre);
    }

    public function testIfTenInsertionsSucceds(): void
    {
        for ($i = 1; $i <= 10; $i++) {
            $genre = $this->genreService->insert('test'.$i, true);
            $genreId = $genre->getId();

            $game = $this->gameService->insert('test'.$i, true);
            $gameId = $game->getId();

            $gameGenre = $this->gameGenreService->insert($genreId, $gameId);

            $this->assertNotEmpty($gameGenre);
            $this->assertInstanceOf(GameGenre::class, $gameGenre);
        }
    }

    public function testIfInsertWithInvalidGenreIdFails(): void
    {
        $genreId = -1;

        $game = $this->gameService->insert('test', true);
        $gameId = $game->getId();        

        $this->expectException(EntityInvalidValueException::class);

        $this->gameGenreService->insert($genreId, $gameId);
    }

    public function testIfInsertWithInvalidGameIdFails(): void
    {
        $genre = $this->genreService->insert('test', true);
        $genreId = $genre->getId();        

        $gameId = -1;

        $this->expectException(EntityInvalidValueException::class);

        $this->gameGenreService->insert($genreId, $gameId);
    }

    public function testIfUpdateSucceds(): void
    {
        $genres = [];
        $games = [];
        for ($i = 1; $i <= 2; $i++){
            $genres[] = $this->genreService->insert('test'.$i, true);
            $games[] = $this->gameService->insert('test'.$i, true);
        }

        $genresIterator = new ArrayIterator($genres);
        $gamesIterator = new ArrayIterator($games);

        $genre = $genresIterator->current();
        $genresIterator->next();
        $genreId = $genre->getId();

        $game = $gamesIterator->current();
        $gamesIterator->next();
        $gameId = $game->getId();

        $gameGenre = $this->gameGenreService->insert($genreId, $gameId);
        $id = $gameGenre->getId();

        $genre = $genresIterator->current();
        $genreId = $genre->getId();

        $game = $gamesIterator->current();
        $gameId = $game->getId();

        $hasUpdated = $this->gameGenreService->update($id, $genreId, $gameId);

        $this->assertTrue($hasUpdated);
    }

    public function testIfUpdateWithInvalidIdFails(): void
    {
        $genres = [];
        $games = [];
        for ($i = 1; $i <= 2; $i++){
            $genres[] = $this->genreService->insert('test'.$i, true);
            $games[] = $this->gameService->insert('test'.$i, true);
        }

        $genresIterator = new ArrayIterator($genres);
        $gamesIterator = new ArrayIterator($games);

        $genre = $genresIterator->current();
        $genresIterator->next();
        $genreId = $genre->getId();

        $game = $gamesIterator->current();
        $gamesIterator->next();
        $gameId = $game->getId();

        $this->gameGenreService->insert($genreId, $gameId);
        $id = -1;

        $genre = $genresIterator->current();
        $genreId = $genre->getId();

        $game = $gamesIterator->current();
        $gameId = $game->getId();

        $this->expectException(EntityInvalidValueException::class);

        $this->gameGenreService->update($id, $genreId, $gameId);
    }

    public function testIfUpdateWithUnexistantIdFails(): void
    {
        $genres = [];
        $games = [];
        for ($i = 1; $i <= 2; $i++){
            $genres[] = $this->genreService->insert('test'.$i, true);
            $games[] = $this->gameService->insert('test'.$i, true);
        }

        $genresIterator = new ArrayIterator($genres);
        $gamesIterator = new ArrayIterator($games);

        $genre = $genresIterator->current();
        $genresIterator->next();
        $genreId = $genre->getId();

        $game = $gamesIterator->current();
        $gamesIterator->next();
        $gameId = $game->getId();

        $this->gameGenreService->insert($genreId, $gameId);        
        $id = 999;

        $genre = $genresIterator->current();
        $genreId = $genre->getId();

        $game = $gamesIterator->current();
        $gameId = $game->getId();

        $this->expectException(DatabaseUnexistantRegisterException::class);

        $this->gameGenreService->update($id, $genreId, $gameId);
    }    

    public function testIfUpdateWithInvalidGenreIdFails(): void
    {
        $genres = [];
        $games = [];
        for ($i = 1; $i <= 2; $i++){
            $genres[] = $this->genreService->insert('test'.$i, true);
            $games[] = $this->gameService->insert('test'.$i, true);
        }

        $genresIterator = new ArrayIterator($genres);
        $gamesIterator = new ArrayIterator($games);

        $genre = $genresIterator->current();
        $genresIterator->next();
        $genreId = $genre->getId();

        $game = $gamesIterator->current();
        $gamesIterator->next();
        $gameId = $game->getId();

        $gameGenre = $this->gameGenreService->insert($genreId, $gameId);
        $id = $gameGenre->getId();

        $genreId = -1;

        $game = $gamesIterator->current();
        $gameId = $game->getId();

        $this->expectException(EntityInvalidValueException::class);

        $this->gameGenreService->update($id, $genreId, $gameId);
    }

    public function testIfUpdateWithUnexistantGenreIdFails(): void
    {
        $genres = [];
        $games = [];
        for ($i = 1; $i <= 2; $i++){
            $genres[] = $this->genreService->insert('test'.$i, true);
            $games[] = $this->gameService->insert('test'.$i, true);
        }

        $genresIterator = new ArrayIterator($genres);
        $gamesIterator = new ArrayIterator($games);

        $genre = $genresIterator->current();
        $genresIterator->next();
        $genreId = $genre->getId();

        $game = $gamesIterator->current();
        $gamesIterator->next();
        $gameId = $game->getId();

        $gameGenre = $this->gameGenreService->insert($genreId, $gameId);
        $id = $gameGenre->getId();

        $genreId = 999;

        $game = $gamesIterator->current();
        $gameId = $game->getId();

        $this->expectException(DatabaseUnexistantRegisterException::class);

        $this->gameGenreService->update($id, $genreId, $gameId);
    }     

    public function testIfUpdateWithInvalidGameIdFails(): void
    {
        $genres = [];
        $games = [];
        for ($i = 1; $i <= 2; $i++){
            $genres[] = $this->genreService->insert('test'.$i, true);
            $games[] = $this->gameService->insert('test'.$i, true);
        }

        $genresIterator = new ArrayIterator($genres);
        $gamesIterator = new ArrayIterator($games);

        $genre = $genresIterator->current();
        $genresIterator->next();
        $genreId = $genre->getId();

        $game = $gamesIterator->current();
        $gamesIterator->next();
        $gameId = $game->getId();

        $gameGenre = $this->gameGenreService->insert($genreId, $gameId);
        $id = $gameGenre->getId();

        $genre = $genresIterator->current();
        $genreId = $genre->getId();

        $gameId = -1;

        $this->expectException(EntityInvalidValueException::class);

        $this->gameGenreService->update($id, $genreId, $gameId);
    }    

    public function testIfUpdateWithUnexistantGameIdFails(): void
    {
        $genres = [];
        $games = [];
        for ($i = 1; $i <= 2; $i++){
            $genres[] = $this->genreService->insert('test'.$i, true);
            $games[] = $this->gameService->insert('test'.$i, true);
        }

        $genresIterator = new ArrayIterator($genres);
        $gamesIterator = new ArrayIterator($games);

        $genre = $genresIterator->current();
        $genresIterator->next();
        $genreId = $genre->getId();

        $game = $gamesIterator->current();
        $gamesIterator->next();
        $gameId = $game->getId();

        $gameGenre = $this->gameGenreService->insert($genreId, $gameId);
        $id = $gameGenre->getId();

        $genre = $genresIterator->current();
        $genreId = $genre->getId();

        $gameId = 999;

        $this->expectException(DatabaseUnexistantRegisterException::class);

        $this->gameGenreService->update($id, $genreId, $gameId);
    }     

    public function testIfDeleteSucceds(): void
    {
        $genre = $this->genreService->insert('test', true);
        $genreId = $genre->getId();

        $game = $this->gameService->insert('test', true);
        $gameId = $game->getId();

        $gameGenre = $this->gameGenreService->insert($genreId, $gameId);
        $id = $gameGenre->getId();

        $hasDeleted = $this->gameGenreService->delete($id);

        $this->assertTrue($hasDeleted);
    }

    public function testIfDeleteWithInvalidIdFails(): void
    {
        $genre = $this->genreService->insert('test', true);
        $genreId = $genre->getId();

        $game = $this->gameService->insert('test', true);
        $gameId = $game->getId();

        $this->gameGenreService->insert($genreId, $gameId);
        $id = -1;        

        $this->expectException(EntityInvalidValueException::class);

        $this->gameGenreService->delete($id);
    }

    public function testIfDeleteWithUnexistantIdFails(): void
    {
        $genre = $this->genreService->insert('test', true);
        $genreId = $genre->getId();

        $game = $this->gameService->insert('test', true);
        $gameId = $game->getId();

        $this->gameGenreService->insert($genreId, $gameId);
        $id = 999;        

        $hasDeleted = $this->gameGenreService->delete($id);

        $this->assertFalse($hasDeleted);
    }    

    public function testIfFindByIdSucceds(): void
    {
        $genre = $this->genreService->insert('test', true);
        $genreId = $genre->getId();

        $game = $this->gameService->insert('test', true);
        $gameId = $game->getId();

        $gameGenre = $this->gameGenreService->insert($genreId, $gameId);
        $id = $gameGenre->getId();

        $fetchedGameGenre = $this->gameGenreService->findById($id);

        $this->assertNotEmpty($fetchedGameGenre);
        $this->assertInstanceOf(GameGenre::class, $fetchedGameGenre);
        $this->assertEquals($gameGenre, $fetchedGameGenre);
    }

    public function testIfFindByIdWithInvalidIdFails(): void
    {
        $genre = $this->genreService->insert('test', true);
        $genreId = $genre->getId();

        $game = $this->gameService->insert('test', true);
        $gameId = $game->getId();

        $this->gameGenreService->insert($genreId, $gameId);
        $id = -1;

        $this->expectException(EntityInvalidValueException::class);

        $this->gameGenreService->findById($id);
    }

    public function testIfFindByIdWithUnexistantIdFails(): void
    {
        $genre = $this->genreService->insert('test', true);
        $genreId = $genre->getId();

        $game = $this->gameService->insert('test', true);
        $gameId = $game->getId();

        $this->gameGenreService->insert($genreId, $gameId);
        $id = 999;

        $fetchedGameGenre = $this->gameGenreService->findById($id);

        $this->assertEmpty($fetchedGameGenre);
    }    

    public function testIfFindAllSucceds(): void
    {
        $genre = $this->genreService->insert('test', true);
        $genreId = $genre->getId();

        $game = $this->gameService->insert('test', true);
        $gameId = $game->getId();

        $this->gameGenreService->insert($genreId, $gameId);

        $gameGenres = $this->gameGenreService->findAll();

        $this->assertNotEmpty($gameGenres);
    }

    public function testIfFindAllWithNoRegistersSucceds(): void
    {
        $gameGenres = $this->gameGenreService->findAll();

        $this->assertEmpty($gameGenres);
    }
}
