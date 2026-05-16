<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Tests\Application\Shared\Service;

use Mvreisg\GamebaseBackend\Application\Shared\Service\DatabaseService;
use Mvreisg\GamebaseBackend\Domain\Shared\Interface\DatabaseRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DatabaseServiceTest extends TestCase
{
    private function createDatabaseRepository(
        bool $exists,
        bool $wasCreated,
        bool $wasDropped
    ): MockObject&DatabaseRepositoryInterface {
        $repository = $this->createMock(DatabaseRepositoryInterface::class);
        $repository
            ->method("exists")
            ->willReturn(
                $exists
            );
        $repository
            ->method("create")
            ->willReturn(
                $wasCreated
            );
        $repository
            ->method("drop")
            ->willReturn(
                $wasDropped
            );
        return $repository;
    }

    private function createDatabaseService(
        MockObject&DatabaseRepositoryInterface $repository
    ): DatabaseService {
        return new DatabaseService(
            $repository
        );
    }

    public function testIfDatabaseExists(): void
    {
        $repository = $this->createDatabaseRepository(
            true,
            true,
            true
        );
        $service = $this->createDatabaseService(
            $repository
        );
        $exists = $service->exists("test");
        $this->assertTrue($exists);
    }

    public function testIfDatabaseDoesNotExists(): void
    {
        $repository = $this->createDatabaseRepository(
            false,
            true,
            true
        );
        $service = $this->createDatabaseService(
            $repository
        );
        $exists = $service->exists("test");
        $this->assertFalse($exists);
    }

    public function testIfDatabaseIsCreated(): void
    {
        $repository = $this->createDatabaseRepository(
            true,
            true,
            true
        );
        $service = $this->createDatabaseService(
            $repository
        );
        $wasCreated = $service->create("test");
        $this->assertTrue($wasCreated);
    }

    public function testIfDatabaseIsNotCreated(): void
    {
        $repository = $this->createDatabaseRepository(
            true,
            false,
            true
        );
        $service = $this->createDatabaseService(
            $repository
        );
        $wasCreated = $service->create("test");
        $this->assertFalse($wasCreated);
    }

    public function testIfDatabaseIsDropped(): void
    {
        $repository = $this->createDatabaseRepository(
            true,
            true,
            true
        );
        $service = $this->createDatabaseService(
            $repository
        );
        $wasDropped = $service->drop("test");
        $this->assertTrue($wasDropped);
    }

    public function testIfDatabaseIsNotDropped(): void
    {
        $repository = $this->createDatabaseRepository(
            true,
            true,
            false
        );
        $service = $this->createDatabaseService(
            $repository
        );
        $wasDropped = $service->drop("test");
        $this->assertFalse($wasDropped);
    }
}
