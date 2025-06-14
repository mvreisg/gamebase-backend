<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Services;

use Mvreisg\GamebaseBackend\Domain\Entities\GameGenre;
use Mvreisg\GamebaseBackend\Domain\Exceptions\EntityInvalidValueException;
use Mvreisg\GamebaseBackend\Domain\Repositories\GameGenreRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock\MockGameGenreRepository;
use PHPUnit\Framework\TestCase;

class GameGenreServiceTest extends TestCase
{
    private GameGenreRepositoryInterface $gameGenreRepository;
    private GameGenreService $gameGenreService;

    protected function setUp(): void
    {
        $this->gameGenreRepository = new MockGameGenreRepository();
        $this->gameGenreService = new GameGenreService($this->gameGenreRepository);
    }

    public function testIfItSuccessfullyInserts(): void
    {
        $genreId = 1;
        $gameId = 1;

        $gameGenre = $this->gameGenreService->insert($genreId, $gameId);

        $this->assertNotEmpty($gameGenre);
        $this->assertInstanceOf(GameGenre::class, $gameGenre);
    }

    public function testIfItSuccessfullyInsertsTenRegisters(): void
    {
        for ($i = 1; $i <= 10; $i++) {
            $genreId = $i;
            $gameId = $i;
            $gameGenre = $this->gameGenreService->insert($genreId, $gameId);

            $this->assertNotEmpty($gameGenre);
            $this->assertInstanceOf(GameGenre::class, $gameGenre);
        }
    }

    public function testIfItFailsToInsertWithInvalidGenreId(): void
    {
        $genreId = -1;
        $gameId = 1;

        $this->expectException(EntityInvalidValueException::class);

        $this->gameGenreService->insert($genreId, $gameId);
    }

    public function testIfItFailsToInsertWithInvalidGameId(): void
    {
        $genreId = 1;
        $gameId = -1;

        $this->expectException(EntityInvalidValueException::class);

        $this->gameGenreService->insert($genreId, $gameId);
    }

    public function testIfItSuccessfullyUpdates(): void
    {
        $genreId = 1;
        $gameId = 1;

        $gameGenre = $this->gameGenreService->insert($genreId, $gameId);

        $id = $gameGenre->getId();

        $hasChanged = $this->gameGenreService->update($id, $genreId, $gameId);

        $this->assertTrue($hasChanged);
    }

    public function testIfItSuccessfullyUpdatesWithTenRegisters(): void
    {
        $genreId = 1;
        $gameId = 1;

        $gameGenres = [];
        for ($i = 1; $i <= 10; $i++) {
            $genreId = $i;
            $gameId = $i;
            $gameGenres[$i] = $this->gameGenreService->insert($genreId, $gameId);
        }

        for ($i = 1; $i <= 10; $i++) {
            $genreId = $i;
            $gameId = $i;
            $id = $gameGenres[$i]->getId();

            $hasChanged = $this->gameGenreService->update($id, $genreId, $gameId);

            $this->assertTrue($hasChanged);
        }
    }

    public function testIfItFailsToUpdateWithInvalidId(): void
    {
        $genreId = 1;
        $gameId = 1;

        $this->gameGenreService->insert($genreId, $gameId);

        $id = -1;

        $this->expectException(EntityInvalidValueException::class);

        $this->gameGenreService->update($id, $genreId, $gameId);
    }

    public function testIfItFailsToUpdateWithInvalidGenreId(): void
    {
        $genreId = 1;
        $gameId = 1;

        $gameGenre = $this->gameGenreService->insert($genreId, $gameId);

        $genreId = -1;
        $id = $gameGenre->getId();

        $this->expectException(EntityInvalidValueException::class);

        $this->gameGenreService->update($id, $genreId, $gameId);
    }

    public function testIfItFailsToUpdateWithInvalidGameId(): void
    {
        $genreId = 1;
        $gameId = 1;

        $gameGenre = $this->gameGenreService->insert($genreId, $gameId);

        $gameId = -1;
        $id = $gameGenre->getId();

        $this->expectException(EntityInvalidValueException::class);

        $this->gameGenreService->update($id, $genreId, $gameId);
    }

    public function testIfItSuccessfullyDeletes(): void
    {
        $genreId = 1;
        $gameId = 1;

        $gameGenre = $this->gameGenreService->insert($genreId, $gameId);

        $id = $gameGenre->getId();

        $hasDeleted = $this->gameGenreService->delete($id);

        $this->assertTrue($hasDeleted);
    }

    public function testIfItSuccessfullyDeletesWithTenRegisters(): void
    {
        $genreId = 1;
        $gameId = 1;

        $gameGenres = [];
        for ($i = 1; $i <= 10; $i++) {
            $genreId = $i;
            $gameId = $i;
            $gameGenres[$i] = $this->gameGenreService->insert($genreId, $gameId);
        }

        for ($i = 1; $i <= 10; $i++) {
            $id = $gameGenres[$i]->getId();

            $hasDeleted = $this->gameGenreService->delete($id);

            $this->assertTrue($hasDeleted);
        }
    }

    public function testIfItFailsToDeleteWithInvalidId(): void
    {
        $genreId = 1;
        $gameId = 1;

        $this->gameGenreService->insert($genreId, $gameId);

        $id = -1;

        $this->expectException(EntityInvalidValueException::class);

        $this->gameGenreService->delete($id);
    }

    public function testIfItSucessfullyFindsById(): void
    {
        $genreId = 1;
        $gameId = 1;

        $gameGenre = $this->gameGenreService->insert($genreId, $gameId);

        $id = $gameGenre->getId();

        $fetchedGameGenre = $this->gameGenreService->findById($id);

        $this->assertNotEmpty($fetchedGameGenre);
        $this->assertInstanceOf(GameGenre::class, $fetchedGameGenre);
        $this->assertEquals($gameGenre, $fetchedGameGenre);
    }

    public function testIfItSucessfullyFindsByIdWithTenRegisters(): void
    {
        $genreId = 1;
        $gameId = 1;

        $gameGenres = [];
        for ($i = 1; $i <= 10; $i++) {
            $genreId = $i;
            $gameId = $i;
            $gameGenres[$i] = $this->gameGenreService->insert($genreId, $gameId);
        }

        for ($i = 1; $i <= 10; $i++) {
            $insertedGameGenre = $gameGenres[$i];
            $id = $insertedGameGenre->getId();

            $fetchedGameGenre = $this->gameGenreService->findById($id);

            $this->assertNotEmpty($fetchedGameGenre);
            $this->assertInstanceOf(GameGenre::class, $fetchedGameGenre);
            $this->assertEquals($insertedGameGenre, $fetchedGameGenre);
        }
    }

    public function testIfItFailsToFindByIdWithInvalidId(): void
    {
        $genreId = 1;
        $gameId = 1;

        $this->gameGenreService->insert($genreId, $gameId);

        $id = -1;

        $this->expectException(EntityInvalidValueException::class);

        $this->gameGenreService->findById($id);
    }

    public function testIfItSuccessfullyFindsAllWithNoRegisters(): void
    {
        $emptyArray = $this->gameGenreService->findAll();

        $this->assertEmpty($emptyArray);
    }

    public function testIfItSuccessfullyFindsAll(): void
    {
        $genreId = 1;
        $gameId = 1;

        $this->gameGenreService->insert($genreId, $gameId);

        $gameGenres = $this->gameGenreService->findAll();

        $this->assertNotEmpty($gameGenres);
    }
}
