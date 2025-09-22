<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Application\Services;

use Mvreisg\GamebaseBackend\Application\Exceptions\Repositories\RepositoryException;
use Mvreisg\GamebaseBackend\Domain\Entities\GenreEntity;
use Mvreisg\GamebaseBackend\Domain\Exceptions\Entities\EntityInvalidValueException;
use Mvreisg\GamebaseBackend\Domain\Repositories\GenreEntityRepositoryInterface;
use Mvreisg\GamebaseBackend\Infrastructure\Exceptions\Repositories\Mock\MockDuplicatedEntryException;
use Mvreisg\GamebaseBackend\Infrastructure\Repositories\Mock\MockGenreEntityRepository;
use PHPUnit\Framework\TestCase;

class GenreServiceTest extends TestCase
{
    private GenreEntityRepositoryInterface $genreEntityRepository;
    private GenreService $genreService;

    protected function setUp(): void
    {
        $this->genreEntityRepository = new MockGenreEntityRepository();
        $this->genreService = new GenreService($this->genreEntityRepository);
    }

    public function testIfGenreInsertionSucceds(): void
    {
        $name = 'test';
        $isActive = true;

        $genre = $this->genreService->insert($name, $isActive);

        $this->assertNotEmpty($genre);
        $this->assertInstanceOf(GenreEntity::class, $genre);
    }

    public function testIfTenGenreInsertionSucceds(): void
    {
        $name = 'test';
        $isActive = true;

        for ($i = 1; $i <= 10; $i++) {
            $genre = $this->genreService->insert($name . $i, $isActive);
            $this->assertNotEmpty($genre);
            $this->assertInstanceOf(GenreEntity::class, $genre);
        }
    }

    public function testIfInsertionOfTwoGenresWithTheSameNameFails(): void
    {
        $this->expectException(MockDuplicatedEntryException::class);

        $name = 'test';
        $isActive = true;

        $this->genreService->insert($name, $isActive);
        $this->genreService->insert($name, $isActive);
    }

    public function testIfGenreInsertionFailsWithEmptyName(): void
    {
        $name = '';
        $isActive = true;

        $this->expectException(EntityInvalidValueException::class);

        $this->genreService->insert($name, $isActive);
    }

    public function testIfUpdateSuccedsWithOneGenreInTheRepository(): void
    {
        $name = 'test';
        $isActive = true;

        $genre = $this->genreService->insert($name, $isActive);

        $name = 'test2';
        $isActive = false;
        $id = $genre->getId();

        $hasUpdated = $this->genreService->update($id, $name, $isActive);

        $this->assertTrue($hasUpdated);
    }

    public function testIfUpdateSuccedsWithTenGenresInTheRepository(): void
    {
        $name = 'test';
        $isActive = true;

        $genres = [];
        for ($i = 1; $i <= 10; $i++) {
            $genres[$i] = $this->genreService->insert($name . $i, $isActive);
        }

        for ($i = 1; $i <= 10; $i++) {
            $id = $genres[$i]->getId();
            $hasUpdated = $this->genreService->update($id, $name . $i * 10, $isActive);

            $this->assertTrue($hasUpdated);
        }
    }

    public function testIfUpdatingAGenreWithAExistantNameSucceds(): void
    {
        $name = 'test';
        $isActive = true;

        $genre = $this->genreService->insert($name, $isActive);

        $id = $genre->getId();

        $hasUpdated = $this->genreService->update($id, $name, $isActive);

        $this->assertTrue($hasUpdated);
    }

    public function testIfUpdatingAGenreWithInvalidIdFails(): void
    {
        $name = 'test';
        $isActive = true;

        $this->genreService->insert($name, $isActive);

        $id = -1;

        $this->expectException(EntityInvalidValueException::class);

        $this->genreService->update($id, $name, $isActive);
    }

    public function testIfUpdatingAGenreWithAEmptyNameFails(): void
    {
        $name = 'test';
        $isActive = true;

        $genre = $this->genreService->insert($name, $isActive);

        $id = $genre->getId();
        $name = '';

        $this->expectException(EntityInvalidValueException::class);

        $this->genreService->update($id, $name, $isActive);
    }

    public function testIfSettingAsActiveSucceds(): void
    {
        $name = 'test';
        $isActive = true;

        $genre = $this->genreService->insert($name, $isActive);

        $id = $genre->getId();
        $isActive = false;

        $hasChanged = $this->genreService->setIsActive($id, $isActive);

        $this->assertTrue($hasChanged);
    }

    public function testIfSettingIsActiveWithSameValueFails(): void
    {
        $name = 'test';
        $isActive = true;

        $genre = $this->genreService->insert($name, $isActive);

        $id = $genre->getId();

        $hasChanged = $this->genreService->setIsActive($id, $isActive);

        $this->assertFalse($hasChanged);
    }

    public function testIfSettingIsActiveWitInvalidIdFails(): void
    {
        $name = 'test';
        $isActive = true;

        $this->genreService->insert($name, $isActive);

        $id = -1;

        $this->expectException(EntityInvalidValueException::class);

        $this->genreService->setIsActive($id, $isActive);
    }

    public function testIfFindByIdSucceds(): void
    {
        $name = 'test';
        $isActive = true;

        $genre = $this->genreService->insert($name, $isActive);

        $id = $genre->getId();

        $genre = $this->genreService->findById($id);

        $this->assertNotEmpty($genre);
        $this->assertInstanceOf(GenreEntity::class, $genre);
    }

    public function testIfFindByIdSuccedsWithTenGenres(): void
    {
        $name = 'test';
        $isActive = true;

        $genres = [];
        for ($i = 1; $i <= 10; $i++) {
            $genres[$i] = $this->genreService->insert($name . $i, $isActive);
        }

        for ($i = 1; $i <= 10; $i++) {
            $id = $genres[$i]->getId();

            $genre = $this->genreService->findById($id);

            $this->assertNotEmpty($genre);
            $this->assertInstanceOf(GenreEntity::class, $genre);
        }
    }

    public function testIfFindAllSuccedsEvenWithNoGenresInTheRepository(): void
    {
        $emptyArray = $this->genreService->findAll();

        $this->assertEmpty($emptyArray);
    }

    public function testIfFindAllSuccedsWithOneGenreInTheRepository(): void
    {
        $name = 'test';
        $isActive = true;

        $this->genreService->insert($name, $isActive);

        $genresArray = $this->genreService->findAll();

        $this->assertNotEmpty($genresArray);
    }

    public function testIfFindAllSuccedsWithTenGenresInTheRepository(): void
    {
        $name = 'test';
        $isActive = true;

        for ($i = 1; $i <= 10; $i++) {
            $this->genreService->insert($name . $i, $isActive);
        }

        $genresArray = $this->genreService->findAll();

        $this->assertNotEmpty($genresArray);
    }
}
